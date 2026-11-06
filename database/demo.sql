-- NSBM NewsDesk database schema
-- Works on MySQL 8+ and MariaDB 10.4+. Import into an EMPTY database.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications, article_views, login_attempts, subscriptions, bookmarks, comments, articles, categories, users;

SET FOREIGN_KEY_CHECKS = 1;

-- People who can sign in. Registration always creates a 'student'.
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(80)  NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('student','admin') NOT NULL DEFAULT 'student',
  status        ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News categories (Academic, Events, Sports, General ...). 'color' is a design token name.
CREATE TABLE categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(60)  NOT NULL UNIQUE,
  description VARCHAR(200) NOT NULL DEFAULT '',
  color       ENUM('green','blue','orange','purple','teal','rose','slate') NOT NULL DEFAULT 'green',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News stories and announcements. Student submissions start as 'pending'.
CREATE TABLE articles (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id    INT UNSIGNED NOT NULL,
  category_id  INT UNSIGNED NOT NULL,
  title        VARCHAR(180) NOT NULL,
  summary      VARCHAR(300) NOT NULL,
  body         TEXT NOT NULL,
  image        VARCHAR(255) NOT NULL DEFAULT '',
  kind         ENUM('news','announcement') NOT NULL DEFAULT 'news',
  status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  featured     TINYINT(1) NOT NULL DEFAULT 0,
  breaking     TINYINT(1) NOT NULL DEFAULT 0,
  expires_at   DATE NULL,
  review_note  VARCHAR(300) NOT NULL DEFAULT '',
  views        INT UNSIGNED NOT NULL DEFAULT 0,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_at DATETIME NULL,
  CONSTRAINT fk_articles_author   FOREIGN KEY (author_id)   REFERENCES users(id),
  CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_feed (status, published_at),
  INDEX idx_category (category_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reader comments. Student comments wait for moderation.
CREATE TABLE comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  body       VARCHAR(1000) NOT NULL,
  status     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comments_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  INDEX idx_moderation (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saved articles (many-to-many users <-> articles).
CREATE TABLE bookmarks (
  user_id    INT UNSIGNED NOT NULL,
  article_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, article_id),
  CONSTRAINT fk_bookmarks_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_bookmarks_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Category subscriptions (many-to-many users <-> categories).
CREATE TABLE subscriptions (
  user_id     INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, category_id),
  CONSTRAINT fk_subscriptions_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
  CONSTRAINT fk_subscriptions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily readership per article, used by the reports charts.
CREATE TABLE article_views (
  article_id INT UNSIGNED NOT NULL,
  view_date  DATE NOT NULL,
  views      INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (article_id, view_date),
  CONSTRAINT fk_views_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  INDEX idx_view_date (view_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- In-app notifications (new story in a followed category, story approved/rejected, comment approved).
CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  message    VARCHAR(255) NOT NULL,
  link       VARCHAR(255) NOT NULL DEFAULT '',
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed sign-in attempts, used to slow down password guessing.
CREATE TABLE login_attempts (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier   CHAR(64) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_throttle (identifier, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------
-- Demonstration data (fictional). Generated by tools/build_demo_sql.php
-- ---------------------------------------------------------------

INSERT INTO categories (id, name, description, color, created_at) VALUES (1, 'Academic', 'Faculty news, exams, timetables, research and study support.', 'blue', NOW() - INTERVAL 150 DAY);
INSERT INTO categories (id, name, description, color, created_at) VALUES (2, 'Events', 'What is on around campus: talks, fairs, festivals and workshops.', 'purple', NOW() - INTERVAL 150 DAY);
INSERT INTO categories (id, name, description, color, created_at) VALUES (3, 'Sports', 'Fixtures, results and training news from university teams.', 'orange', NOW() - INTERVAL 150 DAY);
INSERT INTO categories (id, name, description, color, created_at) VALUES (4, 'Clubs & Societies', 'Updates and achievements from student clubs and societies.', 'teal', NOW() - INTERVAL 150 DAY);
INSERT INTO categories (id, name, description, color, created_at) VALUES (5, 'General', 'Campus services, facilities and community notices.', 'slate', NOW() - INTERVAL 150 DAY);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (1, 'Dilshani Fernando', 'admin@newsdesk.test', '$2y$10$xoaXhMWPqOW108qX5MYMeOOLF6Z4m4P7ECoevhFn54HCDKCGhdrZG', 'admin', 'active', NOW() - INTERVAL 120 DAY, NOW() - INTERVAL 8 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (2, 'Kavindu Perera', 'student@newsdesk.test', '$2y$10$KfJarh.q0rsMx/6D.LDQvemWXjxmWmXcLWyHSDqwAn9VEadMcwqMq', 'student', 'active', NOW() - INTERVAL 60 DAY, NOW() - INTERVAL 15 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (3, 'Nethmi Jayasinghe', 'writer@newsdesk.test', '$2y$10$Nsjkc63zYWw4Svr9/YrJe.DQcnthh4T0lR35v39tZVqoexOOtAog.', 'student', 'active', NOW() - INTERVAL 75 DAY, NOW() - INTERVAL 22 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (4, 'Ishara Wickramasinghe', 'ishara@newsdesk.test', '$2y$10$Nv13VVn6yWRp61OvzVlS4uVRnulkdG63QSvfK0oYsgoXN/coB.Lze', 'student', 'active', NOW() - INTERVAL 40 DAY, NOW() - INTERVAL 29 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (5, 'Dilan Rathnayake', 'dilan@newsdesk.test', '$2y$10$TdlnOjEMgQ8g1GVSpwRqaeyMR7tAjC/l.wWaK3b7wmjeFUKNY2LlG', 'student', 'active', NOW() - INTERVAL 33 DAY, NOW() - INTERVAL 36 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (6, 'Sachini Silva', 'sachini@newsdesk.test', '$2y$10$kRoPIQO9ve/ePiRgYkvt/.LCiGekDyLaRlQ2NEEdoLz8qm7VZlB2G', 'student', 'active', NOW() - INTERVAL 21 DAY, NOW() - INTERVAL 43 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (7, 'Tharindu Bandara', 'tharindu@newsdesk.test', '$2y$10$hOtEHinl70ucB0nTJSAA1OE.0jhczSVjCarDJNjKEIg9CwuRJ42rK', 'student', 'active', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 50 HOUR);
INSERT INTO users (id, name, email, password_hash, role, status, created_at, last_login_at) VALUES (8, 'Amaya de Alwis', 'amaya@newsdesk.test', '$2y$10$69y7tiGF6oiKgaDXkeI8JO8XlqTfZvwq1a/.Bwfjb4/pmwIGOJBTa', 'student', 'active', NOW() - INTERVAL 2 DAY, NULL);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (1, 1, 1, 'Convocation 2026: graduates to cross the stage in three ceremonies', 'More than two thousand graduates from the faculties of Computing, Business and Engineering will receive their degrees across three ceremonies next month.', 'The university has confirmed the schedule for this year''s convocation, which will be held in three ceremonies so that every graduate can invite two guests.

The Faculty of Computing ceremony opens the programme, followed by Business and then Engineering. Graduates should collect their gowns from the student services counter during the week before their ceremony and bring their student ID.

Rehearsals take place the day before each ceremony. Attendance is compulsory for graduates who want to walk the stage, and late arrivals will be seated at the end of their faculty''s row.

Photography packages will be available on the day, and the full ceremony will be streamed for family members who cannot attend in person.', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop', 'news', 'approved', 1, 0, NULL, '', 96, NOW() - INTERVAL 7 HOUR, NOW() - INTERVAL 5 HOUR, NOW() - INTERVAL 5 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (1, CURDATE() - INTERVAL 0 DAY, 96);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (2, 1, 5, 'Green campus week plants 500 native saplings around the Pitipana grounds', 'Volunteers from twelve clubs spent the week planting, labelling and mapping native trees as part of the campus sustainability plan.', 'Green campus week ended on Friday with 500 native saplings planted along the walking paths, car park edges and the slope behind the engineering block.

Each sapling now carries a small tag with its species name and the club that planted it. The environmental society has also mapped every tree so that volunteers can track survival rates over the coming year.

Organisers said the week was about more than numbers. Workshops on composting, reducing single-use plastics and saving energy in the hostels drew a full room every evening.

Students who want to adopt a sapling and water it during the dry months can sign up with the environmental society.', 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop', 'news', 'approved', 1, 0, NULL, '', 150, NOW() - INTERVAL 28 HOUR, NOW() - INTERVAL 26 HOUR, NOW() - INTERVAL 26 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (2, CURDATE() - INTERVAL 1 DAY, 84), (2, CURDATE() - INTERVAL 0 DAY, 66);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (3, 3, 3, 'Basketball team reaches the inter-university final after a one-point thriller', 'A free throw with four seconds left sent the NSBM men’s basketball team into Saturday’s final.', 'It came down to a single free throw. With the scores level and four seconds on the clock, the team''s captain stepped up and made the shot that sent NSBM into the inter-university final.

The team trailed by eleven points at half time but changed to a full-court press after the break. The defence forced turnovers on four consecutive possessions and the lead changed hands six times in the final quarter.

The coach praised the bench players, who kept the pressure on while the starters rested. The final is on Saturday afternoon at the indoor stadium, and supporters'' buses will leave from the main gate.

Students who want a seat on the bus should register at the sports office before Thursday.', 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop', 'news', 'approved', 1, 0, NULL, '', 190, NOW() - INTERVAL 32 HOUR, NOW() - INTERVAL 30 HOUR, NOW() - INTERVAL 30 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (3, CURDATE() - INTERVAL 1 DAY, 107), (3, CURDATE() - INTERVAL 0 DAY, 83);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (4, 2, 4, 'Computing society hackathon: 36 hours, 28 teams and one very tired panel of judges', 'Teams built apps for local farmers, a bus tracker for the campus shuttle and a study planner that won the people’s choice award.', 'Twenty-eight teams signed up for this year''s computing society hackathon, and twenty-five of them made it to the final demo after 36 hours of building.

The winning team built a simple SMS service that lets small farmers check wholesale vegetable prices before they travel to market. The judges said it solved a real problem with technology that works on any phone.

Second place went to a live tracker for the campus shuttle bus, and the people''s choice award went to a study planner that splits assignments into daily tasks.

The society says the best projects will get mentoring over the next term, and it plans to run a smaller beginner-friendly event for first-year students.', 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 189, NOW() - INTERVAL 54 HOUR, NOW() - INTERVAL 52 HOUR, NOW() - INTERVAL 52 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (4, CURDATE() - INTERVAL 2 DAY, 79), (4, CURDATE() - INTERVAL 1 DAY, 62), (4, CURDATE() - INTERVAL 0 DAY, 48);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (5, 1, 1, 'Library extends opening hours for the exam period', 'The main library will stay open until 10 pm on weekdays and open on Sundays for the four weeks before end-of-semester exams.', 'From next Monday the main library will stay open until 10 pm on weekdays and from 9 am to 5 pm on Sundays until the end of the examination period.

Group study rooms can be booked for up to two hours at a time through the library counter. Rooms that are not claimed within fifteen minutes of the booking time will be released to students who are waiting.

Library staff have also asked students not to reserve desks by leaving bags unattended for long periods during busy hours.

The quiet reading floor will remain a silent zone at all times. Phones should be switched to silent before entering.', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 301, NOW() - INTERVAL 76 HOUR, NOW() - INTERVAL 74 HOUR, NOW() - INTERVAL 74 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (5, CURDATE() - INTERVAL 3 DAY, 104), (5, CURDATE() - INTERVAL 2 DAY, 82), (5, CURDATE() - INTERVAL 1 DAY, 64), (5, CURDATE() - INTERVAL 0 DAY, 51);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (6, 4, 2, 'Careers fair brings 40 employers to campus, with on-the-spot interviews', 'Software, banking, logistics and design companies set up stands in the main hall, and several ran first-round interviews on the day.', 'Forty employers took part in this semester''s careers fair, the largest number since the event moved to the main hall.

Several software and banking companies ran first-round interviews on the day, and students who brought printed CVs said it made a real difference.

The careers unit ran a CV clinic in the morning and a short talk on preparing for technical interviews in the afternoon. Both sessions were full within an hour.

Students who missed the fair can book a one-to-one appointment with the careers unit to get feedback on their CV and LinkedIn profile.', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 176, NOW() - INTERVAL 100 HOUR, NOW() - INTERVAL 98 HOUR, NOW() - INTERVAL 98 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (6, CURDATE() - INTERVAL 4 DAY, 54), (6, CURDATE() - INTERVAL 3 DAY, 42), (6, CURDATE() - INTERVAL 2 DAY, 33), (6, CURDATE() - INTERVAL 1 DAY, 26), (6, CURDATE() - INTERVAL 0 DAY, 21);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (7, 5, 3, 'Athletics squad brings home six medals from the national university games', 'Two gold, one silver and three bronze medals for NSBM athletes, including a new personal best in the women’s 400 metres.', 'The NSBM athletics squad returned from the national university games with six medals: two gold, one silver and three bronze.

The highlight was a gold medal and personal best in the women''s 400 metres, run in the final heat of the day. The men''s 4 × 100 metres relay team also took gold after a clean final baton change.

The squad trains three mornings a week at the campus track. New members are welcome at any level, and the coaches run a separate session for beginners on Saturday mornings.

The team thanked the students who travelled to support them over the weekend.', 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 143, NOW() - INTERVAL 122 HOUR, NOW() - INTERVAL 120 HOUR, NOW() - INTERVAL 120 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (7, CURDATE() - INTERVAL 5 DAY, 40), (7, CURDATE() - INTERVAL 4 DAY, 32), (7, CURDATE() - INTERVAL 3 DAY, 25), (7, CURDATE() - INTERVAL 2 DAY, 19), (7, CURDATE() - INTERVAL 1 DAY, 15), (7, CURDATE() - INTERVAL 0 DAY, 12);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (8, 3, 4, 'Music society’s night under the stars draws a record crowd', 'Eight student bands, a choir and a surprise acoustic set made the open-air concert the society’s biggest night yet.', 'The music society''s open-air concert drew its biggest crowd yet, with students and families filling the lawn in front of the main building.

Eight student bands performed, from acoustic duos to a full rock line-up, and the university choir closed the evening with a set of Sinhala and Tamil classics.

The society used the event to raise funds for new instruments for its practice room. Members said they were grateful to the student union for help with lighting and sound.

Auditions for next semester''s line-up open after the exam period.', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 98, NOW() - INTERVAL 152 HOUR, NOW() - INTERVAL 150 HOUR, NOW() - INTERVAL 150 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (8, CURDATE() - INTERVAL 6 DAY, 26), (8, CURDATE() - INTERVAL 5 DAY, 20), (8, CURDATE() - INTERVAL 4 DAY, 16), (8, CURDATE() - INTERVAL 3 DAY, 12), (8, CURDATE() - INTERVAL 2 DAY, 10), (8, CURDATE() - INTERVAL 1 DAY, 8), (8, CURDATE() - INTERVAL 0 DAY, 6);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (9, 1, 5, 'New student support centre opens beside the cafeteria', 'Registration, ID cards, transport passes and wellbeing appointments are now handled in one place.', 'Students no longer need to visit three different offices to sort out administrative tasks. The new student support centre beside the cafeteria now handles registration changes, ID card replacements, transport passes and appointments with student counsellors.

A queue ticket system shows waiting times on a screen outside the centre, and most requests can also be started online through the student portal.

The centre is open from 8.30 am to 4.30 pm on weekdays. Counselling appointments are confidential and free for all registered students.

Staff ask students to bring their student ID to every visit.', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 220, NOW() - INTERVAL 202 HOUR, NOW() - INTERVAL 200 HOUR, NOW() - INTERVAL 200 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (9, CURDATE() - INTERVAL 8 DAY, 52), (9, CURDATE() - INTERVAL 7 DAY, 41), (9, CURDATE() - INTERVAL 6 DAY, 33), (9, CURDATE() - INTERVAL 5 DAY, 26), (9, CURDATE() - INTERVAL 4 DAY, 20), (9, CURDATE() - INTERVAL 3 DAY, 16), (9, CURDATE() - INTERVAL 2 DAY, 13), (9, CURDATE() - INTERVAL 1 DAY, 10), (9, CURDATE() - INTERVAL 0 DAY, 9);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (10, 6, 1, 'Peer tutoring scheme pairs 120 first-year students with senior mentors', 'The faculty of Computing’s peer tutoring scheme has doubled in size, with weekly sessions for programming, maths and databases.', 'The peer tutoring scheme run by the faculty of Computing now pairs 120 first-year students with trained senior mentors, twice as many as last year.

Mentors run weekly small-group sessions on programming fundamentals, discrete mathematics and databases. Each mentor completes a short training course on how to explain ideas without simply giving away answers.

First-year students said the sessions helped them feel less nervous about asking questions they thought were too basic for lectures.

Senior students interested in becoming mentors next semester can apply through the faculty office.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 134, NOW() - INTERVAL 262 HOUR, NOW() - INTERVAL 260 HOUR, NOW() - INTERVAL 260 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (10, CURDATE() - INTERVAL 10 DAY, 30), (10, CURDATE() - INTERVAL 9 DAY, 24), (10, CURDATE() - INTERVAL 8 DAY, 19), (10, CURDATE() - INTERVAL 7 DAY, 15), (10, CURDATE() - INTERVAL 6 DAY, 12), (10, CURDATE() - INTERVAL 5 DAY, 9), (10, CURDATE() - INTERVAL 4 DAY, 7), (10, CURDATE() - INTERVAL 3 DAY, 6), (10, CURDATE() - INTERVAL 2 DAY, 5), (10, CURDATE() - INTERVAL 1 DAY, 4), (10, CURDATE() - INTERVAL 0 DAY, 3);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (11, 4, 2, 'Guest lecture: what building for a billion users teaches about simple design', 'An industry engineer told a packed auditorium why the best products do less, not more.', 'The auditorium was full for this month''s guest lecture, where an industry software engineer spoke about lessons from building apps used by very large audiences.

The main message was that simple products scale better. Features that look impressive in a demo often confuse people who use the app on a cheap phone with a slow connection.

The speaker encouraged students to test their projects with real users early, and to measure what people actually do rather than what they say they want.

The lecture recording is available to students through the learning management system.', 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 87, NOW() - INTERVAL 322 HOUR, NOW() - INTERVAL 320 HOUR, NOW() - INTERVAL 320 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (11, CURDATE() - INTERVAL 13 DAY, 18), (11, CURDATE() - INTERVAL 12 DAY, 15), (11, CURDATE() - INTERVAL 11 DAY, 12), (11, CURDATE() - INTERVAL 10 DAY, 9), (11, CURDATE() - INTERVAL 9 DAY, 8), (11, CURDATE() - INTERVAL 8 DAY, 6), (11, CURDATE() - INTERVAL 7 DAY, 5), (11, CURDATE() - INTERVAL 6 DAY, 4), (11, CURDATE() - INTERVAL 5 DAY, 3), (11, CURDATE() - INTERVAL 4 DAY, 2), (11, CURDATE() - INTERVAL 3 DAY, 2), (11, CURDATE() - INTERVAL 2 DAY, 1), (11, CURDATE() - INTERVAL 1 DAY, 1), (11, CURDATE() - INTERVAL 0 DAY, 1);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (12, 3, 3, 'Women’s football team starts the season with a 3–1 win', 'Two second-half goals turned a tight match into a confident opening victory.', 'The women''s football team opened the season with a 3–1 win in a match that stayed level until the hour mark.

After conceding an early goal, the team equalised before half time with a header from a corner. Two goals in the second half, one from a long-range shot and one from a quick counter-attack, sealed the result.

The captain said the team''s fitness work over the break made the difference late in the game.

The next home match is on Wednesday afternoon. Supporters are welcome at the campus ground.', 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 66, NOW() - INTERVAL 402 HOUR, NOW() - INTERVAL 400 HOUR, NOW() - INTERVAL 400 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (12, CURDATE() - INTERVAL 16 DAY, 14), (12, CURDATE() - INTERVAL 15 DAY, 11), (12, CURDATE() - INTERVAL 14 DAY, 9), (12, CURDATE() - INTERVAL 13 DAY, 7), (12, CURDATE() - INTERVAL 12 DAY, 6), (12, CURDATE() - INTERVAL 11 DAY, 5), (12, CURDATE() - INTERVAL 10 DAY, 4), (12, CURDATE() - INTERVAL 9 DAY, 3), (12, CURDATE() - INTERVAL 8 DAY, 2), (12, CURDATE() - INTERVAL 7 DAY, 1), (12, CURDATE() - INTERVAL 6 DAY, 1), (12, CURDATE() - INTERVAL 5 DAY, 1), (12, CURDATE() - INTERVAL 4 DAY, 1), (12, CURDATE() - INTERVAL 3 DAY, 1);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (13, 5, 4, 'Leo Club volunteers paint and repair a village school library', 'Thirty volunteers spent the weekend repainting, fixing shelves and cataloguing donated books.', 'Thirty members of the university Leo Club spent the weekend at a village school, repainting its library, repairing shelves and cataloguing more than 600 donated books.

The club collected the books during a month-long drive on campus. Students also donated stationery, which was packed into kits for every child in the school.

Teachers at the school said the library had been closed for repairs for over a year.

The club is planning a follow-up visit to run reading sessions with the children next semester.', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 54, NOW() - INTERVAL 482 HOUR, NOW() - INTERVAL 480 HOUR, NOW() - INTERVAL 480 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (13, CURDATE() - INTERVAL 20 DAY, 11), (13, CURDATE() - INTERVAL 19 DAY, 9), (13, CURDATE() - INTERVAL 18 DAY, 7), (13, CURDATE() - INTERVAL 17 DAY, 6), (13, CURDATE() - INTERVAL 16 DAY, 5), (13, CURDATE() - INTERVAL 15 DAY, 4), (13, CURDATE() - INTERVAL 14 DAY, 3), (13, CURDATE() - INTERVAL 13 DAY, 3), (13, CURDATE() - INTERVAL 12 DAY, 2), (13, CURDATE() - INTERVAL 11 DAY, 2), (13, CURDATE() - INTERVAL 10 DAY, 1), (13, CURDATE() - INTERVAL 9 DAY, 1);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (14, 1, 1, 'Final-year project showcase: applications open for exhibition spaces', 'Final-year students can apply to present their projects to industry visitors at the end-of-year showcase.', 'Final-year students from all faculties can now apply for a space at the end-of-year project showcase, where industry visitors, alumni and staff will see the best work of the year.

Applications need a short project summary, a photo or screenshot and the name of the supervisor. A panel will select projects that show clear problem-solving and a working result.

Selected students will get a display table, a power point and a poster printed by the university.

The faculty offices can answer questions about the application process.', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop', 'news', 'approved', 0, 0, NULL, '', 190, NOW() - INTERVAL 562 HOUR, NOW() - INTERVAL 560 HOUR, NOW() - INTERVAL 560 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (14, CURDATE() - INTERVAL 23 DAY, 35), (14, CURDATE() - INTERVAL 22 DAY, 28), (14, CURDATE() - INTERVAL 21 DAY, 22), (14, CURDATE() - INTERVAL 20 DAY, 18), (14, CURDATE() - INTERVAL 19 DAY, 14), (14, CURDATE() - INTERVAL 18 DAY, 12), (14, CURDATE() - INTERVAL 17 DAY, 10), (14, CURDATE() - INTERVAL 16 DAY, 8), (14, CURDATE() - INTERVAL 15 DAY, 7), (14, CURDATE() - INTERVAL 14 DAY, 6), (14, CURDATE() - INTERVAL 13 DAY, 5), (14, CURDATE() - INTERVAL 12 DAY, 4), (14, CURDATE() - INTERVAL 11 DAY, 4), (14, CURDATE() - INTERVAL 10 DAY, 2), (14, CURDATE() - INTERVAL 9 DAY, 2), (14, CURDATE() - INTERVAL 8 DAY, 2), (14, CURDATE() - INTERVAL 7 DAY, 2), (14, CURDATE() - INTERVAL 6 DAY, 2), (14, CURDATE() - INTERVAL 5 DAY, 2), (14, CURDATE() - INTERVAL 4 DAY, 1), (14, CURDATE() - INTERVAL 3 DAY, 1), (14, CURDATE() - INTERVAL 2 DAY, 1), (14, CURDATE() - INTERVAL 1 DAY, 1), (14, CURDATE() - INTERVAL 0 DAY, 1);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (15, 1, 1, 'End-of-semester exam timetable is now published', 'Check your personal timetable on the student portal and report any clashes to your faculty office by Friday.', 'The final timetable for end-of-semester examinations is now available on the student portal.

Please check your personal timetable carefully. If two of your exams are scheduled at the same time, report the clash to your faculty office by Friday so that an alternative sitting can be arranged.

Remember to bring your student ID card to every exam. Students without an ID will need to obtain a temporary pass from the student support centre before the exam starts.', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop', 'announcement', 'approved', 0, 1, CURDATE() + INTERVAL 10 DAY, '', 74, NOW() - INTERVAL 5 HOUR, NOW() - INTERVAL 3 HOUR, NOW() - INTERVAL 3 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (15, CURDATE() - INTERVAL 0 DAY, 74);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (16, 1, 5, 'Shuttle bus timetable changes from Monday', 'Morning shuttles from Kottawa and Homagama will run every 15 minutes between 7 and 9 am.', 'To reduce waiting times during the busy morning period, shuttle buses from Kottawa and Homagama will run every 15 minutes between 7 am and 9 am starting Monday.

Afternoon services remain unchanged. The last bus from campus still leaves at 6.30 pm.

Please have your transport pass ready when boarding. Passes can be renewed at the student support centre.', 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop', 'announcement', 'approved', 0, 0, CURDATE() + INTERVAL 7 DAY, '', 58, NOW() - INTERVAL 22 HOUR, NOW() - INTERVAL 20 HOUR, NOW() - INTERVAL 20 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (16, CURDATE() - INTERVAL 0 DAY, 58);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (17, 4, 2, 'Call for volunteers: Freshers’ welcome day', 'The student union needs 60 volunteers to guide new students, run information desks and help with the campus tour.', 'The student union is looking for 60 volunteers for the Freshers'' welcome day.

Volunteers will guide new students around campus, staff information desks and help run the afternoon campus tour. Lunch and a volunteer T-shirt are provided.

You can sign up at the student union office. Volunteers must attend a one-hour briefing the day before the event.', 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop', 'announcement', 'approved', 0, 0, CURDATE() + INTERVAL 14 DAY, '', 70, NOW() - INTERVAL 46 HOUR, NOW() - INTERVAL 44 HOUR, NOW() - INTERVAL 44 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (17, CURDATE() - INTERVAL 1 DAY, 40), (17, CURDATE() - INTERVAL 0 DAY, 30);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (18, 1, 3, 'Sports trials for the new season', 'Trials for cricket, football, basketball, netball and athletics run over the next two weeks. All levels are welcome.', 'Trials for university teams in cricket, football, basketball, netball and athletics will take place over the next two weeks.

Check the notice board at the sports complex for the time and venue of each trial. Wear suitable sports clothing and bring water.

Students who are new to a sport are encouraged to attend. Coaches are looking for commitment as well as experience.', 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop', 'announcement', 'approved', 0, 0, CURDATE() + INTERVAL 21 DAY, '', 61, NOW() - INTERVAL 72 HOUR, NOW() - INTERVAL 70 HOUR, NOW() - INTERVAL 70 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (18, CURDATE() - INTERVAL 2 DAY, 26), (18, CURDATE() - INTERVAL 1 DAY, 20), (18, CURDATE() - INTERVAL 0 DAY, 15);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (19, 1, 5, 'Water supply interruption in hostel block B', 'Water supply to hostel block B was interrupted for scheduled tank cleaning.', 'Water supply to hostel block B was interrupted for scheduled tank cleaning. Students were asked to store water in advance.

The work has been completed and supply has returned to normal.', '', 'announcement', 'approved', 0, 0, CURDATE() + INTERVAL -3 DAY, '', 140, NOW() - INTERVAL 902 HOUR, NOW() - INTERVAL 900 HOUR, NOW() - INTERVAL 900 HOUR);
INSERT INTO article_views (article_id, view_date, views) VALUES (19, CURDATE() - INTERVAL 37 DAY, 23), (19, CURDATE() - INTERVAL 36 DAY, 19), (19, CURDATE() - INTERVAL 35 DAY, 15), (19, CURDATE() - INTERVAL 34 DAY, 12), (19, CURDATE() - INTERVAL 33 DAY, 10), (19, CURDATE() - INTERVAL 32 DAY, 8), (19, CURDATE() - INTERVAL 31 DAY, 6), (19, CURDATE() - INTERVAL 30 DAY, 5), (19, CURDATE() - INTERVAL 29 DAY, 5), (19, CURDATE() - INTERVAL 28 DAY, 4), (19, CURDATE() - INTERVAL 27 DAY, 3), (19, CURDATE() - INTERVAL 26 DAY, 3), (19, CURDATE() - INTERVAL 25 DAY, 2), (19, CURDATE() - INTERVAL 24 DAY, 1), (19, CURDATE() - INTERVAL 23 DAY, 1), (19, CURDATE() - INTERVAL 22 DAY, 1), (19, CURDATE() - INTERVAL 21 DAY, 1), (19, CURDATE() - INTERVAL 20 DAY, 1), (19, CURDATE() - INTERVAL 19 DAY, 1), (19, CURDATE() - INTERVAL 18 DAY, 1), (19, CURDATE() - INTERVAL 17 DAY, 1), (19, CURDATE() - INTERVAL 16 DAY, 1), (19, CURDATE() - INTERVAL 15 DAY, 1), (19, CURDATE() - INTERVAL 14 DAY, 1), (19, CURDATE() - INTERVAL 13 DAY, 1), (19, CURDATE() - INTERVAL 12 DAY, 1), (19, CURDATE() - INTERVAL 11 DAY, 1), (19, CURDATE() - INTERVAL 10 DAY, 1), (19, CURDATE() - INTERVAL 9 DAY, 1), (19, CURDATE() - INTERVAL 8 DAY, 1), (19, CURDATE() - INTERVAL 7 DAY, 1), (19, CURDATE() - INTERVAL 6 DAY, 1), (19, CURDATE() - INTERVAL 5 DAY, 1), (19, CURDATE() - INTERVAL 4 DAY, 1), (19, CURDATE() - INTERVAL 3 DAY, 1), (19, CURDATE() - INTERVAL 2 DAY, 1), (19, CURDATE() - INTERVAL 1 DAY, 1), (19, CURDATE() - INTERVAL 0 DAY, 1);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (20, 2, 4, 'Debate society qualifies for the national championship semi-finals', 'After four rounds of debates on technology, education and the environment, the team is through to the last four.', 'The debate society''s senior team won four of its five rounds at the regional qualifiers to secure a place in the national championship semi-finals.

The motions covered artificial intelligence in schools, plastic bans and public transport funding. Judges said the team''s rebuttals were among the strongest of the competition.

The society is looking for students to help the team practise before the semi-finals.', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop', 'news', 'pending', 0, 0, NULL, '', 0, NOW() - INTERVAL 8 HOUR, NOW() - INTERVAL 6 HOUR, NULL);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (21, 7, 2, 'Photography walk around campus this Saturday', 'Bring a phone or camera for a relaxed photo walk led by the photography club. Beginners welcome.', 'The photography club is running a relaxed photo walk around campus on Saturday morning.

We will cover composition basics, shooting in bright sunlight and simple editing on your phone. You do not need a camera; a phone is enough.

Meet at the main gate. The walk takes about two hours.', '', 'announcement', 'pending', 0, 0, CURDATE() + INTERVAL 12 DAY, '', 0, NOW() - INTERVAL 4 HOUR, NOW() - INTERVAL 2 HOUR, NULL);
INSERT INTO articles (id, author_id, category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note, views, created_at, updated_at, published_at) VALUES (22, 3, 3, 'Friendly cricket match idea', 'A suggestion for a friendly cricket match between faculties at some point this semester.', 'I think it would be fun to have a friendly cricket match between the faculties some time this semester. Maybe on a weekend.

Let me know if anyone is interested.', '', 'news', 'rejected', 0, 0, NULL, 'Please add a proposed date, the teams involved and who is organising it.', 0, NOW() - INTERVAL 92 HOUR, NOW() - INTERVAL 90 HOUR, NULL);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (1, 2, 'Will there be a live stream link shared beforehand? My grandparents can’t travel.', 'approved', NOW() - INTERVAL 3 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (1, 1, 'Yes, the link will be posted here and on the portal one week before the ceremony.', 'approved', NOW() - INTERVAL 2 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (3, 4, 'What a finish! Booking my seat on the supporters’ bus.', 'approved', NOW() - INTERVAL 25 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (3, 6, 'The defence in the third quarter was unreal.', 'approved', NOW() - INTERVAL 22 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (4, 5, 'The farmers’ price SMS idea is brilliant. Hope they keep building it.', 'approved', NOW() - INTERVAL 48 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (5, 3, 'Sunday opening is a lifesaver, thank you!', 'approved', NOW() - INTERVAL 70 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (5, 7, 'Can we also get more charging points on the second floor?', 'pending', NOW() - INTERVAL 4 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (2, 8, 'How do we sign up to adopt a sapling?', 'pending', NOW() - INTERVAL 1 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (6, 2, 'Got a second interview from the fair. Bring printed CVs, people!', 'approved', NOW() - INTERVAL 90 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (10, 6, 'My mentor explained recursion better than any video I watched.', 'approved', NOW() - INTERVAL 250 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (15, 5, 'Is the timetable final or could dates still change?', 'pending', NOW() - INTERVAL 2 HOUR);
INSERT INTO comments (article_id, user_id, body, status, created_at) VALUES (8, 8, 'first!!!', 'rejected', NOW() - INTERVAL 140 HOUR);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (2, 1, NOW() - INTERVAL 2 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (2, 3, NOW() - INTERVAL 7 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (2, 5, NOW() - INTERVAL 12 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (2, 15, NOW() - INTERVAL 17 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (3, 2, NOW() - INTERVAL 2 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (3, 4, NOW() - INTERVAL 7 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (4, 6, NOW() - INTERVAL 2 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (4, 1, NOW() - INTERVAL 7 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (6, 10, NOW() - INTERVAL 2 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (6, 5, NOW() - INTERVAL 7 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (6, 15, NOW() - INTERVAL 12 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (5, 3, NOW() - INTERVAL 2 DAY);
INSERT INTO bookmarks (user_id, article_id, created_at) VALUES (5, 7, NOW() - INTERVAL 7 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (2, 1, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (2, 3, NOW() - INTERVAL 9 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (2, 4, NOW() - INTERVAL 15 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (3, 3, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (3, 2, NOW() - INTERVAL 9 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (4, 2, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (4, 1, NOW() - INTERVAL 9 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (6, 1, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (5, 3, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (5, 4, NOW() - INTERVAL 9 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (7, 2, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (8, 5, NOW() - INTERVAL 3 DAY);
INSERT INTO subscriptions (user_id, category_id, created_at) VALUES (8, 1, NOW() - INTERVAL 9 DAY);
INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES (2, 'New in Sports: Basketball team reaches the inter-university final after a one-point thriller', 'index.php?page=article&id=3', 0, NOW() - INTERVAL 30 HOUR);
INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES (2, 'New in Academic: Convocation 2026: graduates to cross the stage in three ceremonies', 'index.php?page=article&id=1', 0, NOW() - INTERVAL 5 HOUR);
INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES (2, 'Your comment on “Convocation 2026: graduates to cross the stage in three ceremonies” is now visible.', 'index.php?page=article&id=1', 1, NOW() - INTERVAL 3 HOUR);
INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES (3, 'Your story “Friendly cricket match idea” needs changes: Please add a proposed date, the teams involved and who is organising it.', 'index.php?page=my_stories&status=rejected', 0, NOW() - INTERVAL 88 HOUR);
