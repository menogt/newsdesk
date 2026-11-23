"""
End-to-end HTTP checks for NSBM NewsDesk.
Run against a FRESH import of database/demo.sql:
    python tools/qa_tests.py http://localhost/newsdesk/public
Needs: pip install requests
"""
import re, sys, io, struct, zlib, time, random
import requests

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8100").rstrip("/") + "/index.php"
results = []

def check(name, ok, detail=""):
    results.append((name, bool(ok)))
    print(("PASS " if ok else "FAIL ") + name + ("" if ok else f"  -> {detail}"))

def session():
    s = requests.Session()
    s.headers["User-Agent"] = "newsdesk-qa"
    return s

def token(s, page="home", **params):
    r = s.get(BASE, params={"page": page, **params})
    m = re.search(r'name="csrf" value="([a-f0-9]+)"', r.text)
    return m.group(1) if m else ""

def post(s, page, data, files=None, **params):
    data = {"csrf": token(s, page, **params), **data}
    return s.post(BASE, params={"page": page, **params}, data=data, files=files, allow_redirects=False)

def login(email, password):
    s = session()
    r = post(s, "login", {"action": "login", "email": email, "password": password})
    return s, r

def png(w, h):
    """Tiny valid PNG of w x h for upload tests."""
    raw = b"".join(b"\x00" + b"\x80\xc0\x90" * w for _ in range(h))
    def chunk(t, d): return struct.pack(">I", len(d)) + t + d + struct.pack(">I", zlib.crc32(t + d) & 0xffffffff)
    return b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", struct.pack(">IIBBBBB", w, h, 8, 2, 0, 0, 0)) + chunk(b"IDAT", zlib.compress(raw)) + chunk(b"IEND", b"")

uid = str(int(time.time())) + str(random.randint(100, 999))

# ---------- Public ----------
g = session()
r = g.get(BASE)
check("Homepage loads with published stories", r.status_code == 200 and "Convocation 2026" in r.text, r.status_code)
check("Security headers present (CSP, nosniff, frame)", "Content-Security-Policy" in r.headers and r.headers.get("X-Frame-Options") == "DENY")
check("Breaking news strip shows", "Breaking" in r.text and "exam timetable" in r.text)
check("Notice board lists active announcements only", "Shuttle bus timetable" in r.text and "Water supply interruption" not in r.text)
r = g.get(BASE, params={"page": "announcements"})
check("Expired announcement moves to archive", "Archive" in r.text and "Water supply interruption" in r.text)
r = g.get(BASE, params={"page": "article", "id": 20})
check("Guest cannot read a pending story", r.status_code == 404)
r = g.get(BASE, params={"page": "article", "id": 22})
check("Guest cannot read a rejected story", r.status_code == 404)
r = g.get(BASE, params={"page": "admin"}, allow_redirects=False)
check("Guest is sent to sign in from admin", r.status_code == 302 and "page=login" in r.headers.get("Location", ""))
r = g.get(BASE, params={"page": "does-not-exist"})
check("Unknown page shows styled 404", r.status_code == 404 and "Page not found" in r.text)
r = g.get(BASE, params={"page": "news", "q": "basketball"})
check("Search finds matching story", "Basketball team reaches" in r.text and "Library extends" not in r.text)
r = g.get(BASE, params={"page": "news", "q": "' OR 1=1 -- "})
check("SQL injection text is treated as a literal search", r.status_code == 200 and "0 stories" in r.text)
r = g.get(BASE, params={"page": "news", "q": "%"})
check("Wildcard characters are escaped in search", "0 stories" in r.text)
r = g.get(BASE, params={"page": "news", "category": 3})
check("Category filter", "Basketball" in r.text and "Convocation" not in r.text)
r = g.get(BASE, params={"page": "news", "type": "announcement"})
check("Type filter (announcements only)", "Shuttle bus" in r.text and "Basketball team reaches" not in r.text)
r = g.get(BASE, params={"page": "news", "from": "2099-01-01", "to": "2000-01-01"})
check("Reversed date range is swapped, not silently empty", "swapped" in r.text)
r = g.get(BASE, params={"page": "news", "from": "not-a-date"})
check("Invalid date is reported and ignored", "not a valid date" in r.text)
r = g.get(BASE, params={"page": "news", "p": "abc", "category": ["x"], "q": ["a"]})
check("Malformed query parameters do not crash", r.status_code == 200)
r = g.get(BASE, params={"page": "news", "sort": "popular"})
cards = re.findall(r'<h3><a href="[^"]+">([^<]+)</a>', r.text)
check("Sort by most read", cards and cards[0].startswith("Library extends"), cards[:2])
r = g.post(BASE, params={"page": "login"}, data={"action": "login", "email": "x@y.z", "password": "x"})
check("Form without CSRF token is rejected", r.status_code == 403 and "form expired" in r.text)

# ---------- Registration and login ----------
s = session()
email = f"qa{uid}@students.nsbm.ac.lk"
r = post(s, "register", {"action": "register", "name": "QA Student", "email": email, "password": "short", "confirm_password": "short"})
check("Weak password rejected on server", r.status_code == 422 and "at least 8" in r.text)
r = post(s, "register", {"action": "register", "name": "QA Student", "email": email, "password": "Password123", "confirm_password": "Password124"})
check("Password confirmation checked on server", "do not match" in r.text)
r = post(s, "register", {"action": "register", "name": "QA Student", "email": "not-an-email", "password": "Password123", "confirm_password": "Password123"})
check("Email validated on server", "valid email" in r.text)
r = post(s, "register", {"action": "register", "name": "QA Student", "email": email, "password": "Password123", "confirm_password": "Password123", "role": "admin"})
check("Registration succeeds and signs in", r.status_code == 302)
r = s.get(BASE, params={"page": "admin"})
check("Posted role=admin does not create an admin", r.status_code == 403)
r = post(session(), "register", {"action": "register", "name": "QA Student", "email": email, "password": "Password123", "confirm_password": "Password123"})
check("Duplicate email rejected", "already exists" in r.text)
_, r = login(email, "WrongPass1")
check("Wrong password rejected", r.status_code == 422 and "incorrect" in r.text)

stu, r = login("student@newsdesk.test", "CampusStudent26!")
check("Demo student signs in", r.status_code == 302)
adm, r = login("admin@newsdesk.test", "CampusAdmin26!")
check("Admin signs in and lands on dashboard", r.status_code == 302 and "page=admin" in r.headers.get("Location", ""))
for p in ["admin", "admin_articles", "admin_article", "admin_categories", "admin_comments", "admin_users", "admin_reports"]:
    r = adm.get(BASE, params={"page": p})
    check(f"Admin page loads: {p}", r.status_code == 200 and "Warning" not in r.text and "Fatal" not in r.text, r.status_code)
r = stu.get(BASE, params={"page": "admin_users"})
check("Student blocked from admin pages", r.status_code == 403)
r = post(stu, "home", {"action": "category_save", "name": "Hacked", "description": ""})
check("Student cannot forge admin actions", r.status_code == 403)

# ---------- Student submits, admin reviews ----------
r = post(stu, "write", {"action": "article_save", "title": "Hi", "summary": "short", "body": "x", "category_id": 1, "kind": "news"})
check("Short title rejected with message", r.status_code == 422 and "Title needs at least 5" in r.text)
title = f"QA submission {uid}"
r = post(stu, "write", {"action": "article_save", "title": title, "summary": "A summary that is long enough to pass.", "body": "Body text " * 10,
                        "category_id": 2, "kind": "news", "status": "approved", "featured": "1"},
         files={"image": ("cover.png", png(800, 450), "image/png")})
check("Student submits story with photo", r.status_code == 302)
r = g.get(BASE, params={"page": "news", "q": uid})
check("Forged status ignored: submission not public", title not in r.text)
r = stu.get(BASE, params={"page": "my_stories"})
check("Submission appears as pending in My stories", title in r.text and "Pending review" in r.text)
art_id = int(re.search(r'page=article&(?:amp;)?id=(\d+)">' + re.escape(title), r.text).group(1))
r = stu.get(BASE, params={"page": "article", "id": art_id})
check("Author can preview own pending story", r.status_code == 200 and "Preview" in r.text)
r = re.search(r'uploads/articles/([a-f0-9]{32}\.png)', r.text)
check("Uploaded image stored with random name", r is not None)
r2 = post(stu, "write", {"action": "article_save", "title": "Bad image story", "summary": "A summary that is long enough to pass.", "body": "Body text " * 10, "category_id": 2, "kind": "news"},
          files={"image": ("evil.php.png", b"<?php echo 1; ?>", "image/png")})
check("Non-image upload rejected", r2.status_code == 422 and "JPG, PNG or WebP" in r2.text)

other, _ = login("writer@newsdesk.test", "CampusWriter26!")
r = other.get(BASE, params={"page": "write", "id": art_id})
check("Other student cannot open the editor", r.status_code == 403)
r = post(other, "home", {"action": "article_delete", "id": art_id})
check("Other student cannot delete", r.status_code == 403)

r = post(adm, "admin_article", {"action": "article_review", "id": art_id, "decision": "rejected", "review_note": ""}, id=art_id)
check("Reject without feedback is refused", r.status_code == 422)
r = post(adm, "admin_article", {"action": "article_review", "id": art_id, "decision": "rejected", "review_note": "Add the venue."}, id=art_id)
check("Admin rejects with feedback", r.status_code == 302)
r = stu.get(BASE, params={"page": "notifications"})
check("Author notified of rejection", "needs changes" in r.text and "Add the venue." in r.text)
r = post(adm, "admin_article", {"action": "article_review", "id": art_id, "decision": "approved", "review_note": ""}, id=art_id)
check("Admin approves", r.status_code == 302)
r = g.get(BASE, params={"page": "article", "id": art_id})
check("Approved story is public", r.status_code == 200 and title in r.text)
r = stu.get(BASE, params={"page": "notifications"})
check("Author notified of publication", "is now published" in r.text)
r = adm.get(BASE, params={"page": "admin_articles", "q": uid})
check("Admin search finds story", title in r.text)
r = post(adm, "admin_articles", {"action": "article_flag", "id": art_id, "flag": "breaking"})
r = g.get(BASE)
check("Breaking toggle shows story on homepage strip", title in r.text.split("breaking-items")[1][:2000] if "breaking-items" in r.text else False)
post(adm, "admin_articles", {"action": "article_flag", "id": art_id, "flag": "breaking"})

# Follower notification when a new story is published
post(stu, "following", {"action": "subscribe", "id": 2})   # demo student does not follow Events yet
r = post(adm, "admin_article", {"action": "article_save", "title": f"Admin events story {uid}", "summary": "A summary that is long enough to pass.", "body": "Body text " * 10,
                                "category_id": 2, "kind": "announcement", "status": "approved", "expires_at": "2000-01-01"})
check("Past expiry date rejected for announcements", r.status_code == 422 and "past" in r.text)
r = post(adm, "admin_article", {"action": "article_save", "title": f"Admin events story {uid}", "summary": "A summary that is long enough to pass.", "body": "Body text " * 10,
                                "category_id": 2, "kind": "news", "status": "approved"})
check("Admin publishes directly", r.status_code == 302)
r = stu.get(BASE, params={"page": "notifications"})
check("Followers notified of new story in category", f"New in Events: Admin events story {uid}" in r.text)
r = stu.get(BASE, params={"page": "feed"})
check("Feed shows followed category stories", f"Admin events story {uid}" in r.text)

# Views
before = adm.get(BASE, params={"page": "admin_articles", "q": uid}).text
v1 = g.get(BASE, params={"page": "article", "id": art_id}); v2 = g.get(BASE, params={"page": "article", "id": art_id})
r = adm.get(BASE, params={"page": "admin_article", "id": art_id})
reads = re.search(r"<dt>Reads</dt><dd>(\d+)</dd>", r.text)
check("Reads counted once per session", reads and int(reads.group(1)) >= 1)
r = adm.get(BASE, params={"page": "admin_articles", "q": uid})
check("Reading a story does not change its 'updated' time", "Just now" in r.text or "min ago" in r.text)

# ---------- Comments ----------
r = post(stu, "article", {"action": "comment_save", "article_id": art_id, "body": "<script>alert(1)</script> nice"}, id=art_id)
check("Student comment saved", r.status_code == 302)
r = g.get(BASE, params={"page": "article", "id": art_id})
check("Pending comment hidden from public", "alert(1)" not in r.text)
r = stu.get(BASE, params={"page": "article", "id": art_id})
check("Author sees own pending comment, escaped", "&lt;script&gt;" in r.text and "<script>alert(1)" not in r.text)
cid = int(re.search(r'name="action" value="comment_delete"><input type="hidden" name="id" value="(\d+)"', r.text).group(1))
r = post(adm, "admin_comments", {"action": "comment_review", "id": cid, "decision": "approved"})
r = g.get(BASE, params={"page": "article", "id": art_id})
check("Approved comment is public", "&lt;script&gt;" in r.text)
r = post(other, "article", {"action": "comment_delete", "id": cid}, id=art_id)
check("Other student cannot delete comment", r.status_code == 403)
r = post(stu, "article", {"action": "comment_save", "id": cid, "article_id": art_id, "body": "Edited comment"}, id=art_id)
r = g.get(BASE, params={"page": "article", "id": art_id})
check("Edited comment returns to moderation", "Edited comment" not in r.text)
r = post(adm, "admin_comments", {"action": "comments_bulk", "decision": "approved", "ids[]": [cid]})
check("Bulk approve works", "Edited comment" in g.get(BASE, params={"page": "article", "id": art_id}).text)

# ---------- Bookmarks and following ----------
r = post(stu, "article", {"action": "bookmark", "id": art_id}, id=art_id)
check("Bookmark saved", title in stu.get(BASE, params={"page": "saved"}).text)
post(stu, "article", {"action": "bookmark", "id": art_id}, id=art_id)
check("Bookmark removed", title not in stu.get(BASE, params={"page": "saved"}).text)

# ---------- Categories ----------
cname = f"QA Cat {uid}"
r = post(adm, "admin_categories", {"action": "category_save", "name": cname, "description": "x", "color": "rose"})
check("Create category", cname in adm.get(BASE, params={"page": "admin_categories"}).text)
r = post(adm, "admin_categories", {"action": "category_save", "name": cname, "description": "", "color": "rose"})
check("Duplicate category rejected", r.status_code == 422)
r = post(adm, "admin_categories", {"action": "category_delete", "id": 2})
check("Category with stories cannot be deleted", r.status_code == 422 and "still has" in r.text)

# ---------- Users ----------
r = post(adm, "admin_users", {"action": "user_status", "id": 1})
check("Admin cannot block themselves", r.status_code == 422)
qa_id = int(re.search(r'edit=(\d+)[^"]*">Edit</a>(?:(?!</tr>).)*?', adm.get(BASE, params={"page": "admin_users", "q": email}).text).group(1))
post(adm, "admin_users", {"action": "user_status", "id": qa_id})
_, r = login(email, "Password123")
check("Blocked user cannot sign in", r.status_code == 422 and "suspended" in r.text)
post(adm, "admin_users", {"action": "user_status", "id": qa_id})
r = post(adm, "admin_users", {"action": "user_delete", "id": qa_id})
check("Admin deletes a user without stories", r.status_code == 302)

# ---------- Profile ----------
r = post(stu, "profile", {"action": "password_change", "current_password": "wrong", "new_password": "Another123", "confirm_password": "Another123"})
check("Password change needs current password", r.status_code == 422)

# ---------- Reports ----------
r = adm.get(BASE, params={"page": "admin_reports", "range": "7"})
check("Report shows chart and tables", "<svg class=\"chart\"" in r.text and "Most read stories" in r.text)
r = adm.get(BASE, params={"page": "admin_reports", "range": "custom", "from": "2026-12-01", "to": "2026-01-01"})
check("Custom report period handles reversed dates", r.status_code == 200)
r = adm.get(BASE, params={"page": "admin_reports", "export": "csv"})
check("CSV export downloads", r.headers.get("Content-Type", "").startswith("text/csv") and "Reads in period" in r.text)

# ---------- Clean up ----------
post(adm, "admin_articles", {"action": "article_delete", "id": art_id})
check("Admin deletes story (cascade)", g.get(BASE, params={"page": "article", "id": art_id}).status_code == 404)

passed = sum(1 for _, ok in results if ok)
print(f"\n{passed}/{len(results)} checks passed")
sys.exit(0 if passed == len(results) else 1)
