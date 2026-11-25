# NSBM NewsDesk — Campus News Management System

Project topic 1 for Web and Mobile Application Development (Batch 26.1 IFSL).
Built with **HTML, CSS, JavaScript, PHP 8 and MySQL**. No framework, no Composer, no build step.

## Features

**Students / readers**
- Register, sign in, edit profile, change password
- Homepage with breaking-news strip, featured stories, latest list, notice board and category sections
- Browse all news; search; filter by category, type and date range; sort by newest, oldest, most read, most discussed
- Announcements page (current notices with closing dates, plus archive)
- Read stories with cover photos, reading time and read count; related stories
- Submit stories or announcements with a cover photo; edit, resubmit and delete own submissions; see editor feedback
- Comment on stories (moderated), edit and delete own comments
- Save (bookmark) stories; follow categories; personal feed
- In-app notifications (story published or rejected, comment approved, new story in a followed category)

**Administrators** (separate admin panel)
- Dashboard: review queue, comments waiting, reads chart, most read this week
- Stories: create, edit, delete; approve or reject submissions with feedback; mark featured / breaking; filters and search
- Announcements with an optional "show until" date
- Comment moderation with bulk approve / reject / delete
- Categories: create, edit (name, description, colour), delete when empty
- Users: search, add, edit role, block / unblock, delete
- Reports: period picker (7 / 30 / 90 days or custom), daily reads chart, reads and followers by category, most read and most discussed stories, top contributors, CSV export, print view

**Security and quality**
- PDO prepared statements everywhere; output escaped with `htmlspecialchars`
- `password_hash` / `password_verify`; sign-in rate limiting; session ID regenerated on sign-in
- CSRF token on every form; all changes are POST
- Server-side validation on every form, plus friendly client-side validation messages
- Uploads checked by real file type and size, saved with random names; scripts blocked in the uploads folder
- Content Security Policy and other security headers
- Responsive from 360 px phones to wide desktops; keyboard focus styles; screen-reader labels

## Folder structure

```
app/
  bootstrap.php        config, database, session, security headers, core helpers
  helpers.php          formatting, images, notifications, charts
  actions.php          maps each form "action" to a handler + access level
  actions/             auth.php, articles.php, reader.php, admin.php
  report_period.php    reporting period helper
views/
  layout/              site.php (public + student), admin.php (admin panel)
  partials/            story card, listing with filters, story form fields, empty state
  site/                home, news, article, announcements, login, register, write,
                       my_stories, feed, saved, following, notifications, profile, error
  admin/               dashboard, articles, article_form, comments, categories, users,
                       reports, reports_csv
public/                the only web-visible folder
  index.php            front controller (routes: index.php?page=...)
  assets/css, js, fonts, img
  uploads/articles/    uploaded cover photos
database/
  schema.sql           empty tables
  demo.sql             tables + demo data (dates are relative to the import day)
tools/
  seed_data.php        demo content; build_demo_sql.php regenerates demo.sql
  install.php          CLI installer; create_admin.php creates an admin
  qa_tests.py          80 automated end-to-end checks
```

## Run it locally (XAMPP)

1. Copy the `newsdesk` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, create a database called `nsbm_newsdesk` (collation `utf8mb4_unicode_ci`), select it, choose **Import** and import `database/demo.sql`.
4. Copy `config.example.php` to `config.php`. The XAMPP defaults (127.0.0.1, port 3306, user `root`, no password) already match.
5. Open `http://localhost/newsdesk/public/`.

Demo accounts:

| Role | Email | Password |
|---|---|---|
| Administrator | admin@newsdesk.test | CampusAdmin26! |
| Student | student@newsdesk.test | CampusStudent26! |
| Student | writer@newsdesk.test | CampusWriter26! |

Re-import `demo.sql` before recording the video so the dates and charts look current.

## Deploy to InfinityFree (free PHP + MySQL hosting)

1. Create a hosting account and note the **MySQL host, database name, username and password** from *MySQL Databases*.
2. Open phpMyAdmin from the control panel and import `database/demo.sql` into that database.
3. In `config.php`, enter those four values (port `3306`).
4. Upload the contents of the `newsdesk` folder into `htdocs/` (File Manager → Upload & Unzip, or FTP). The root `index.php` forwards visitors to `public/`, and the other folders are blocked from the web.
5. Open `https://your-site/public/check.php`. Fix anything marked FAIL, then **delete check.php**.
6. Visit `https://your-site/` (it forwards to `/public/`). Put this live link in your report.
7. Before submitting, change the admin password from **Profile**, and block or delete demo accounts you do not need.

## Cover photos

Demo stories hot-link free photos from Unsplash. If a photo cannot load, the app shows a coloured panel with the category name instead. For the best result (and originality marks), replace them with your own campus photos from **Admin → Stories → Edit → Cover photo**.

## Automated tests

With the site running on a fresh import of `demo.sql`:

```
pip install requests
python tools/qa_tests.py http://localhost/newsdesk/public
```
