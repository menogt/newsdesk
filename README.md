# NSBM NewsDesk — Campus News Management System

A full-stack campus news and official announcements management platform built for **NSBM Green University** (Faculty of Computing — *Web and Mobile Application Development*, Batch 26.1 IFSL, **Project Topic 1**).

Developed with **HTML5, CSS3, JavaScript, PHP 8, and MySQL** — with zero third-party PHP/JS frameworks, no Composer dependencies, and no build step.

**Live Website:** [https://newsdeskkk.free.nf/public/](https://newsdeskkk.free.nf/public/)  
**Repository:** [https://github.com/menogt/newsdesk](https://github.com/menogt/newsdesk)  
**Video Demonstration:** [https://youtu.be/1FdIOR-AAiA](https://youtu.be/1FdIOR-AAiA)

---

## 1. Project Overview

NSBM NewsDesk provides a unified digital newsroom where NSBM students and campus clubs can read verified campus updates and submit stories, while university editorial administrators review submissions, moderate reader discussions, organize news categories, and track readership analytics.

The system separates public/student pages from the editorial administration console under a single front controller (`public/index.php`), enforces server-side validation and role-based access control across all state-changing actions, and stores all content and engagement data in a normalized 9-table MySQL InnoDB database.

| Project Attribute | Details |
|---|---|
| **University & Faculty** | NSBM Green University — Faculty of Computing |
| **Module & Batch** | Web and Mobile Application Development \| Batch 26.1 IFSL |
| **Selected Topic** | Project Topic 1: NSBM NewsDesk — Campus News Management System |
| **Team Size** | 8 Members (Team Leader: OGMS Perera — `@menogt`) |
| **Architecture** | Custom MVC-inspired Front Controller (PHP 8 + PDO + MySQL/MariaDB) |

---

## 2. Live Website

The production deployment is hosted on InfinityFree and publicly accessible for testing:

- **Live Application:** [https://newsdeskkk.free.nf/public/](https://newsdeskkk.free.nf/public/)
- **Root Redirection:** Visiting [https://newsdeskkk.free.nf/](https://newsdeskkk.free.nf/) forwards automatically to `public/` via root `index.php`.

> **Security Notice:** Never publish private database credentials or production secrets. Demonstration credentials for evaluation are documented strictly in [Section 7 (Demonstration Accounts)](#demonstration-accounts).

---

## 3. Screenshots

All screenshots below were captured directly from the live hosted website ([https://newsdeskkk.free.nf/public/](https://newsdeskkk.free.nf/public/)) at a consistent `1440×900` desktop viewport.

### Homepage

![NSBM NewsDesk Homepage](docs/screenshots/student/homepage.png)
*Public homepage featuring the breaking-news banner, lead featured story, secondary stories, and chronological Latest sidebar.*

### Student / Reader Interface

| Screen | Preview |
|---|---|
| **All News & Filtering** (`?page=news`) — Keyword search, category, content type, date range, and sort controls | ![News and Articles Listing](docs/screenshots/student/articles.png) |
| **Article Reader** (`?page=article&id=4`) — Cover photo, reading time, read count, bookmarking, comments, and related stories | ![Article Reading Page](docs/screenshots/student/article-details.png) |
| **Announcements & Archive** (`?page=announcements`) — Active official notices separated from expired archive items | ![Announcements Page](docs/screenshots/student/announcements.png) |
| **Student Submissions Overview (My Stories)** (`?page=my_stories`) — Status tabs (`All`, `Pending review`, `Published`, `Needs changes`) and edit/delete controls | ![Student My Stories](docs/screenshots/student/dashboard.png) |
| **Article Submission Form** (`?page=write`) — Headline, category, type toggle, summary, story body, cover upload, and editorial checklist | ![Article Submission Form](docs/screenshots/student/submission.png) |
| **Notifications** (`?page=notifications`) — Unread alerts for editorial decisions, approved comments, and followed categories | ![Notifications Interface](docs/screenshots/student/notifications.png) |
| **Bookmarked Articles** (`?page=saved`) — Personal saved stories list with search and filter controls | ![Bookmarked Articles](docs/screenshots/student/bookmarks.png) |
| **User Profile & Password** (`?page=profile`) — Personal activity statistics, profile update form, and password change form | ![User Profile](docs/screenshots/student/profile.png) |

> **Implementation Note (Student Workspace):** NewsDesk does not define a separate `?page=student_dashboard` route in `public/index.php`. Instead, signed-in student controls are integrated into the masthead (`Write`, `Notifications`, `Account menu`, `My feed`, `Saved`) alongside **My stories** (`?page=my_stories`, shown above as the student overview) and **My feed** (`?page=feed`).

### Administrator Interface

| Screen | Preview |
|---|---|
| **Administrator Dashboard** (`?page=admin`) — Real-time KPI cards, pending review queue, 14-day readership chart, waiting comments, and top stories | ![Administrator Dashboard](docs/screenshots/admin/dashboard.png) |
| **Article Management** (`?page=admin_articles`) — Full stories table with status tabs, search/type/category filters, and Featured/Breaking toggles | ![Article Management](docs/screenshots/admin/articles.png) |
| **Pending Submissions** (`?page=admin_articles&status=pending`) — Filtered editorial queue of student stories awaiting review | ![Pending Submissions](docs/screenshots/admin/pending-submissions.png) |
| **Article Review & Editor** (`?page=admin_article&id=20`) — Approve/Reject panel with required rejection feedback alongside full story editor | ![Article Editor](docs/screenshots/admin/article-editor.png) |
| **Category Management** (`?page=admin_categories`) — Create, edit, colour-code, and safely delete campus news categories | ![Category Management](docs/screenshots/admin/categories.png) |
| **User Management** (`?page=admin_users&q=newsdesk.test`) — Search, filter by role, create accounts, edit roles, block/unblock, and delete users *(filtered to demo accounts)* | ![User Management](docs/screenshots/admin/users.png) |
| **Comment Moderation** (`?page=admin_comments`) — Status tabs (`Waiting`, `Approved`, `Rejected`, `All`) with single and bulk moderation controls | ![Comment Moderation](docs/screenshots/admin/comments.png) |
| **Announcement Management** (`?page=admin_articles&type=announcement`) — Announcements filtered inside Admin Stories with expiry dates and homepage flags | ![Announcement Management](docs/screenshots/admin/announcements.png) |
| **Reports & Analytics** (`?page=admin_reports&range=90`) — Period KPIs, daily reads SVG chart, category breakdowns, top contributors, print view, and CSV export | ![Reports and Analytics](docs/screenshots/admin/reports.png) |

> **Implementation Note (Admin Announcements):** NewsDesk manages both news stories and official announcements through a unified `articles` table (`kind = 'news' | 'announcement'`). Rather than a separate `admin_announcements` route, administrators filter **Stories** by `Type: Announcements` (`?page=admin_articles&type=announcement`) and configure optional closing dates (`expires_at`) in the Story Editor.

---

## 4. Features

### Student / Reader Features

- **Account Management (`?page=register`, `?page=login`, `?page=profile`):** Self-registration (always assigns the `student` role), sign-in/sign-out, profile editing, and password change requiring current password verification.
- **Public Newsroom (`?page=home`):** Displays a breaking-news strip (up to 3 flagged items), lead featured story, secondary featured cards, Latest stories sidebar, active Notice Board announcements, Most Read This Month leaderboard, and per-category sections.
- **Multi-Faceted Search & Filtering (`?page=news`):** Combines keyword search (escaped `LIKE` matching across title, summary, and body), category filter, content-type filter (`news` vs. `announcement`), date range (`From` / `To`, automatically swapping reversed ranges), four sort modes (`Newest first`, `Oldest first`, `Most read`, `Most discussed`), and 9-per-page pagination.
- **Announcements & Expiry Archive (`?page=announcements`):** Separates active notices (`expires_at IS NULL OR expires_at >= CURDATE()`) from expired notices automatically moved to the **Archive** section.
- **Article Reader (`?page=article&id=...`):** Displays cover image (with category-coloured fallback), reading-time estimate, session-deduplicated read counter (`record_view()`), copy-link button, bookmarking, moderated comments, and up to 3 related stories.
- **Story Authoring & Tracking (`?page=write`, `?page=my_stories`):** Submit news stories or announcements with optional cover photo upload (`JPG`, `PNG`, `WebP` up to `2 MB`, minimum `400×225` px), track status (`Pending review`, `Published`, `Needs changes`), view editor feedback notes, and edit, resubmit, or delete own submissions.
- **Personalization & Notifications (`?page=following`, `?page=feed`, `?page=saved`, `?page=notifications`):** Follow/unfollow campus categories to build a custom **My feed**, bookmark stories under **Saved**, post/edit/delete moderated comments, and receive in-app notifications when submissions are reviewed, comments are approved, or followed categories publish new stories.

### Administrator Features

- **Editorial Dashboard (`?page=admin`):** Summarizes pending stories, unmoderated comments, 7-day read count with week-over-week delta, student account totals, oldest-first Review Queue, 14-day daily reads SVG bar chart, inline comment moderation, and Most Read This Week.
- **Story & Announcement Lifecycle (`?page=admin_articles`, `?page=admin_article`):** Search by title or author, filter by status/type/category, publish directly or review student submissions (`Approve and publish` or `Reject` with mandatory feedback), toggle `Featured` and `Breaking` flags, set announcement `expires_at` dates, and delete stories (cascading comments, bookmarks, and view history).
- **Comment Moderation (`?page=admin_comments`):** Review reader comments across `Waiting`, `Approved`, `Rejected`, and `All` tabs with both individual actions and bulk Approve/Reject/Delete (`comments_bulk`).
- **Category CRUD (`?page=admin_categories`):** Create and edit categories (name, optional description, and 7 colour design tokens: `green`, `blue`, `orange`, `purple`, `teal`, `rose`, `slate`) with referential protection preventing deletion of categories that still contain stories.
- **User Administration (`?page=admin_users`):** Search users by name or email, filter by role, create accounts with temporary passwords, update names/emails/roles, block/unblock accounts, and delete users without authored stories (with self-lockout protection preventing an admin from blocking, deleting, or demoting themselves).
- **Readership Analytics & Export (`?page=admin_reports`):** Preset periods (`Last 7 days`, `Last 30 days`, `Last 90 days`) or custom `From`/`To` dates; computes 6 KPIs, daily reads SVG chart, reads and followers by category, top 10 most read stories, top 5 most discussed stories, top 5 student contributors, print stylesheet, and downloadable CSV report (`?page=admin_reports&export=csv`).

### Security and Quality

- **SQL Injection Protection:** All database queries use PDO prepared statements (`query()`, `one()`, `rows()`, `scalar()`) with `PDO::ATTR_EMULATE_PREPARES => false` and escaped `LIKE` wildcards.
- **XSS Prevention:** All dynamic output is escaped with `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` via `e()`.
- **Authentication & Session Hardening:** Passwords hashed with `password_hash(..., PASSWORD_DEFAULT)` and verified with `password_verify()`; strict session mode enabled; `HttpOnly` and `SameSite=Lax` cookies (`Secure` when HTTPS is active); session IDs regenerated on sign-in, registration, sign-out, and password changes.
- **Brute-Force Rate Limiting:** Failed sign-in attempts are hashed and logged in `login_attempts`, throttling after 10 failures per email or 50 failures per IP address within 15 minutes.
- **CSRF Protection:** Every form includes a 32-byte `random_bytes()` token verified with `hash_equals()` in `app/actions.php`; all state changes require `POST`.
- **Upload Validation & Execution Blocking:** Uploaded images are validated via `finfo(FILEINFO_MIME_TYPE)` and `getimagesize()`, renamed to random 32-character hex filenames, and isolated inside `public/uploads/articles/` where `public/uploads/.htaccess` disables PHP/script execution.
- **HTTP Security Headers & Directory Protection:** `app/bootstrap.php` emits `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, and `Referrer-Policy: strict-origin-when-cross-origin`. `.htaccess` files disable directory indexing (`Options -Indexes`) and block direct web access to `app/`, `database/`, `docs/`, `tools/`, `views/`, `config.php`, `config.example.php`, `README.md`, and `.gitignore`.
- **Responsive & Accessible UI:** Adapts cleanly from `360 px` mobile viewports to desktop screens, with skip links, semantic landmarks, keyboard focus styles, and hidden data tables for SVG charts.

---

## 5. Technology Stack

| Layer | Technology / Specification |
|---|---|
| **Frontend** | HTML5, custom CSS3 (`site.css`, `admin.css`), vanilla ES6+ JavaScript (`app.js`), self-hosted WOFF2 fonts (`Archivo`, `Source Serif 4`), inline SVG icons & charts |
| **Backend** | PHP **8.0+** (`declare(strict_types=1)`, tested with PHP 8.2 / 8.3), PDO (`pdo_mysql`), `mbstring`, `fileinfo`, `session` |
| **Database** | MySQL 8.0+ or MariaDB 10.4+ (`InnoDB`, `utf8mb4_unicode_ci`, 9 relational tables) |
| **Web Server / Hosting** | Apache HTTP Server (`mod_headers`, `.htaccess`), XAMPP / WAMP (local), InfinityFree (live hosting), optional Docker (`php:8.3-apache`) |
| **Automated QA** | Python 3 with `requests` (`tools/qa_tests.py` — 80 end-to-end HTTP checks) |

---

## 6. Project Folder Structure

```text
newsdesk/
├── app/
│   ├── actions/
│   │   ├── admin.php          # Story review, flags, categories, comments, users
│   │   ├── articles.php       # Story/announcement CRUD & upload handling
│   │   ├── auth.php           # Registration, login/logout, rate limiting, profile
│   │   └── reader.php         # Bookmarks, category follows, comments, notifications
│   ├── actions.php            # POST dispatcher, CSRF verification, role guards
│   ├── auth.php               # Standalone auth actions helper copy
│   ├── bootstrap.php          # Config loader, PDO connection, session, CSP headers
│   ├── helpers.php            # Formatting, images, notifications, SVG chart helpers
│   └── report_period.php      # Preset & custom reporting period calculator
├── database/
│   ├── demo.sql               # Schema + relative-dated demonstration dataset
│   └── schema.sql             # Clean 9-table InnoDB schema
├── docs/
│   └── screenshots/
│       ├── admin/             # 9 administrator interface captures
│       └── student/           # 9 public & student interface captures
├── public/                    # Web-accessible document root
│   ├── assets/
│   │   ├── css/               # site.css, admin.css
│   │   ├── fonts/             # archivo.woff2, source-serif-4*.woff2
│   │   ├── img/               # favicon.svg
│   │   └── js/                # app.js (validation, confirmations, mobile nav)
│   ├── uploads/articles/      # Uploaded story cover images (.htaccess protected)
│   ├── check.php              # Pre-deployment environment checker (delete in prod)
│   └── index.php              # Front controller routing (?page=...)
├── tools/
│   ├── build_demo_sql.php     # Regenerates database/demo.sql
│   ├── create_admin.php       # CLI script to create an administrator account
│   ├── install.php            # CLI database installer (--empty or demo data)
│   ├── qa_tests.py            # 80 automated end-to-end HTTP checks
│   └── seed_data.php          # Demonstration dataset generator
├── views/
│   ├── admin/                 # dashboard, articles, article_form, categories,
│   │                          # comments, users, reports, reports_csv
│   ├── layout/                # site.php (public/student), admin.php (admin sidebar)
│   ├── partials/              # story_card, listing, article_fields, empty
│   └── site/                  # home, news, article, announcements, login, register,
│                              # write, my_stories, feed, saved, following,
│                              # notifications, profile, error
├── .htaccess                  # Root security rules & DirectoryIndex index.php
├── config.example.php         # Database configuration template (copy to config.php)
├── Dockerfile                 # Optional PHP 8.3 + Apache container build
├── docker-start.sh            # Container Apache port entrypoint
└── index.php                  # Forwards root requests to public/
```

---

## 7. Local Installation and Setup

### Prerequisites

- **PHP 8.0 or newer** with extensions: `pdo_mysql`, `mbstring`, `fileinfo`, and `session`.
- **MySQL 8.0+** or **MariaDB 10.4+**.
- **Apache HTTP Server** (via **XAMPP** or **WAMP** on Windows, or Apache/Docker on Linux/macOS) with `mod_headers` and `AllowOverride All` enabled.
- **Git** (to clone the repository) and **Python 3 + `requests`** (optional, for running automated QA tests).

### Clone Repository

```bash
git clone https://github.com/menogt/newsdesk.git
```

### Configure Local Server

Place the cloned `newsdesk` directory inside your local web server's document root:

- **XAMPP (Windows):** `C:\xampp\htdocs\newsdesk\`
- **WAMP (Windows):** `C:\wamp64\www\newsdesk\`
- **Linux / macOS:** Move into your web root or point Apache `DocumentRoot` directly to `/path/to/newsdesk/public`.

Ensure the upload directory `public/uploads/articles/` is writable by the web server (on Linux/macOS: `chmod -R 755 public/uploads/articles`).

Start **Apache** and **MySQL** in the XAMPP Control Panel or WAMP Manager.

### Database Setup

**Option A — Using phpMyAdmin (Recommended for XAMPP/WAMP):**

1. Open `http://localhost/phpmyadmin`.
2. Click **New**, enter database name `nsbm_newsdesk`, choose collation `utf8mb4_unicode_ci`, and click **Create**.
3. Select `nsbm_newsdesk`, open the **Import** tab, choose `database/demo.sql` (for full demo content with dates relative to the import day) or `database/schema.sql` (for an empty database), and click **Import**.
4. Verify all 9 tables are created: `articles`, `article_views`, `bookmarks`, `categories`, `comments`, `login_attempts`, `notifications`, `subscriptions`, and `users`.

**Option B — Using the CLI Installer:**

After creating `config.php` (below) and an empty `nsbm_newsdesk` database:

```bash
# Install schema + demonstration data
php tools/install.php

# Or install empty tables only, then create an administrator account
php tools/install.php --empty
NEWSDESK_ADMIN_PASSWORD="YourStrongPassword1" php tools/create_admin.php "Admin Name" admin@example.com
```

### Database Configuration

Copy `config.example.php` to `config.php` in the project root (`newsdesk/config.php`):

```php
<?php
return [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'nsbm_newsdesk',
    'username' => 'root',
    'password' => '',
];
```

- The defaults above match a standard **XAMPP** or **WAMP** MySQL installation (`root` with an empty password on `127.0.0.1:3306`).
- Update `username` / `password` if your local MySQL server uses custom credentials.
- `config.php` is listed in `.gitignore` — **never commit `config.php` to version control.**

### Running the Application

1. Verify your local environment by visiting:
   `http://localhost/newsdesk/public/check.php`
   Confirm every line reports `[OK]` (PHP version `8.0+`, extensions, `config.php`, writable `uploads/articles`, and all 9 database tables).
2. Launch the application at:
   `http://localhost/newsdesk/public/`

### Demonstration Accounts

The `database/demo.sql` dataset includes the following **demonstration-only** accounts for local and grading evaluation:

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@newsdesk.test` | `CampusAdmin26!` |
| **Student** | `student@newsdesk.test` | `CampusStudent26!` |
| **Student** | `writer@newsdesk.test` | `CampusWriter26!` |

> **Important Security Warning:** These credentials are strictly for demonstration with `demo.sql`. Before exposing any deployment publicly or submitting for evaluation, **immediately change the administrator password** via **Profile → Change password** and block or delete unneeded demo accounts from **Admin → Users**.

---

## 8. Application Behaviour

### Student Workflow

1. **Discover & Filter:** Visitors and students browse breaking news, featured stories, active announcements, and category sections on `?page=home`, or search and filter by keyword, category, type, and date on `?page=news`.
2. **Authenticate & Personalize:** Students register (`?page=register`) or sign in (`?page=login`), subscribe to campus categories (`?page=following`), view curated updates in **My feed** (`?page=feed`), and bookmark stories (`?page=saved`).
3. **Submit & Track Stories:** Via **Write a story** (`?page=write`), students submit news stories or announcements with an optional cover photo. Submissions enter the database with `status = 'pending'` (students cannot self-publish). Authors monitor review progress and editor notes under **My stories** (`?page=my_stories`) and can edit or delete their submissions.
4. **Comment & Receive Alerts:** Comments submitted by students start as `pending` until approved by an administrator; editing an existing comment returns it to moderation. In-app notifications alert students when their story is published or rejected, when their comment is approved, or when a followed category publishes a new story.

### Administrator Workflow

1. **Triage Queue (`?page=admin`):** Administrators sign in and land on the Admin Dashboard, highlighting pending stories and waiting comments alongside 7-day and 14-day readership metrics.
2. **Editorial Review (`?page=admin_articles`, `?page=admin_article`):** Editors open pending submissions, edit content if necessary, preview the formatted article, and choose **Approve and publish** (which sets `published_at` and notifies both the author and category followers) or **Reject** (requiring an explanatory `review_note` sent to the author). Published stories can be toggled as **Featured** or **Breaking**.
3. **Moderation, Categories & Users:** Editors bulk-moderate comments (`?page=admin_comments`), manage categories (`?page=admin_categories`), and administer student/editor accounts (`?page=admin_users`).
4. **Analytics & Reporting (`?page=admin_reports`):** Each non-admin session opening a published article increments `articles.views` and daily `article_views` once per session. Administrators analyze custom or preset date ranges and export metrics via **Download CSV**.

---

## 9. Deployment Guide

### InfinityFree Deployment

1. Create an InfinityFree hosting account and note the **MySQL hostname, database name, username, and password** from **MySQL Databases** in the control panel.
2. Open **phpMyAdmin** from the control panel and import `database/demo.sql` (or `database/schema.sql`) into that database.
3. Locally configure `config.php` with the hosting MySQL credentials (port `3306`) — do not commit this file to GitHub.
4. Upload the project contents (excluding `.git/`) into `htdocs/` via File Manager or FTP. The root `index.php` forwards visitors to `public/`, and `.htaccess` rules block direct access to `app/`, `database/`, `docs/`, `tools/`, `views/`, and `config.php`.
5. Visit `https://your-site/public/check.php` to verify PHP 8.0+, extensions, writable `public/uploads/articles/`, and database connectivity.
6. **Delete `public/check.php` immediately** after all checks pass so server configuration details are not publicly exposed.

### Production Configuration

- **Distinguish Local vs. Production Config:** Local development uses `127.0.0.1` (`root` / empty password); production uses the hosting provider's remote MySQL host, database name, dedicated database user, and strong password inside `config.php` on the server only.
- **Credential Hygiene:** Change the default administrator password immediately after deploying, and block or remove unused demo student accounts (`@newsdesk.test`).
- **Container Deployment (Optional):** A `Dockerfile` (`php:8.3-apache` with `pdo_mysql`, `mbstring`, `headers`, `DocumentRoot /var/www/html/public`, and automatic removal of `public/check.php`) plus `docker-start.sh` are included for container-based PHP hosting platforms.

---

## 10. Cover Photos and Media

- Seeded demonstration stories in `database/demo.sql` reference stock photography URLs from Unsplash (`https://images.unsplash.com`).
- On some networks or browsers, certain third-party stock URLs (such as `photo-1523050854058-8df90110c9f1`) may be blocked by Cross-Origin Read Blocking (`ERR_BLOCKED_BY_ORB`). NewsDesk's `public/assets/js/app.js` and `cover()` helper handle failed or missing images gracefully by displaying a colour-coded category panel instead of a broken image icon.
- Uploaded cover photos (`JPG`, `PNG`, `WebP`, max `2 MB`, min `400×225` px) are stored locally in `public/uploads/articles/` with randomized filenames and served directly from your host.

---

## 11. Automated Testing

The repository includes an 80-assertion Python end-to-end test suite (`tools/qa_tests.py`) covering public routes, security headers, SQL injection/wildcard safety, CSRF enforcement, registration/login validation, story submission and image upload security, editorial approval/rejection, notifications, comment moderation, bookmarks, category/user CRUD safeguards, and CSV report export.

Run the suite against a **fresh import of `database/demo.sql`**:

```bash
pip install requests
python tools/qa_tests.py http://localhost/newsdesk/public
```

> **Note:** Running `qa_tests.py` creates and deletes test records during execution; always run it against a fresh local test database rather than a live production site.

---

## 12. Project Checklist

- [x] Core 9-table MySQL schema (`database/schema.sql`) and relative-dated demo dataset (`database/demo.sql`)
- [x] Responsive public, student, and editorial admin interfaces (`views/site/`, `views/admin/`, `public/assets/`)
- [x] Complete CRUD workflows with server-side validation, CSRF protection, and role-based access control
- [x] Automated 80-check end-to-end HTTP test suite (`tools/qa_tests.py`)
- [x] Live deployment on InfinityFree ([https://newsdeskkk.free.nf/public/](https://newsdeskkk.free.nf/public/)) with `public/check.php` removed
- [x] Project demonstration video published ([https://youtu.be/1FdIOR-AAiA](https://youtu.be/1FdIOR-AAiA))
- [x] Genuine live screenshots collected and integrated into `README.md` (`docs/screenshots/`)

---

## 13. Project Contributors

**Batch 26.1 IFSL — Project Topic 1 Team (8 Members)**

| # | Assigned Module | Member Name | Student ID | GitHub ID | Key Responsibilities |
|---|---|---|---|---|---|
| 01 | `01-Core-Database` | DMSVN Disanayaka | `42133` | [@IzumiTenshi09](https://github.com/IzumiTenshi09) | 9-table InnoDB schema, demo seed SQL, PDO bootstrap, helpers, front controller routing, CLI install scripts |
| 02 | `02-Shared-UI` | YATC Gunawardana | `42053` | [@chathrika005](https://github.com/chathrika005) | Shared site & admin layouts, responsive CSS design system, typography, navigation, card partials, client JS |
| 03 | `03-Accounts` | WMPP Bandara | `42027` | [@paramithb2005](https://github.com/paramithb2005) | Registration, bcrypt login/logout, brute-force throttling, profile & password management, admin user CRUD |
| 04 | `04-Public-News` | JMIK Bandara | `41715` | [@ireshakalana](https://github.com/ireshakalana) | Public homepage, breaking strip, multi-filter news catalog, article reader, announcements archive, error views |
| 05 | `05-Story-Authoring` *(Team Leader)* | OGMS Perera | `42181` | [@menogt](https://github.com/menogt) | Student story/announcement form, validated cover photo upload, My Stories status tracking, repository integration |
| 06 | `06-Editorial-Admin` | S.N.O Senarathna | `41903` | [@Oma1228](https://github.com/Oma1228) | Editorial admin dashboard, pending review queue, approval/rejection with notes, Featured/Breaking flags, category CRUD |
| 07 | `07-Reader-Engagement` | H.S.H Perera | `41485` | [@Sandunihansangana](https://github.com/Sandunihansangana) | Bookmarks, category subscriptions, personalized My Feed, in-app notifications, single & bulk comment moderation |
| 08 | `08-Reports-Release` | L.G.M.D Gunawardhana | `41719` | [@Manodya18](https://github.com/Manodya18) | Readership & engagement reports, SVG charts, CSV export, `qa_tests.py` verification, deployment & release docs |

---

## 14. Project Deliverables

| Deliverable | Link / Location |
|---|---|
| **GitHub Source Repository** | [https://github.com/menogt/newsdesk](https://github.com/menogt/newsdesk) |
| **Live Hosted Web Application** | [https://newsdeskkk.free.nf/public/](https://newsdeskkk.free.nf/public/) |
| **Video Demonstration (YouTube)** | [https://youtu.be/1FdIOR-AAiA](https://youtu.be/1FdIOR-AAiA) |
| **Automated QA Test Suite** | [`tools/qa_tests.py`](tools/qa_tests.py) |
| **Database Schema & Demo Data** | [`database/schema.sql`](database/schema.sql) / [`database/demo.sql`](database/demo.sql) |

---

## 15. License

This project was developed for academic coursework under the **Web and Mobile Application Development** module at **NSBM Green University (Faculty of Computing, Batch 26.1 IFSL)**. No separate open-source license file (`LICENSE`) is currently included in the repository; all rights remain with the project team and NSBM Green University coursework guidelines.

---

## 16. Support

- **Issues & Bug Reports:** Open an issue on the [GitHub Repository](https://github.com/menogt/newsdesk/issues).
- **Local Setup Verification:** Run `public/check.php` during local setup (delete before production deployment) or inspect server logs if a `503` database connection error appears.
- **Team Leader Contact:** OGMS Perera (`@menogt`) — NSBM Green University, Faculty of Computing.
