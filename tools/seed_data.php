<?php
/**
 * Demonstration content used by tools/build_demo_sql.php.
 * All people, events and quotes are fictional sample content.
 * Cover photos are hot-linked from Unsplash (free licence); if one fails to
 * load the app shows a coloured category panel instead. Replace them with your
 * own campus photos through the admin editor before presenting.
 */
return [
    'categories' => [
        // key => [name, description, colour]
        'academic' => ['Academic', 'Faculty news, exams, timetables, research and study support.', 'blue'],
        'events'   => ['Events', 'What is on around campus: talks, fairs, festivals and workshops.', 'purple'],
        'sports'   => ['Sports', 'Fixtures, results and training news from university teams.', 'orange'],
        'clubs'    => ['Clubs & Societies', 'Updates and achievements from student clubs and societies.', 'teal'],
        'general'  => ['General', 'Campus services, facilities and community notices.', 'slate'],
    ],

    'users' => [
        // key => [name, email, password, role, days since joined]
        'admin'   => ['Dilshani Fernando', 'admin@newsdesk.test', 'CampusAdmin26!', 'admin', 120],
        'student' => ['Kavindu Perera', 'student@newsdesk.test', 'CampusStudent26!', 'student', 60],
        'writer'  => ['Nethmi Jayasinghe', 'writer@newsdesk.test', 'CampusWriter26!', 'student', 75],
        'ishara'  => ['Ishara Wickramasinghe', 'ishara@newsdesk.test', 'CampusDemo26!', 'student', 40],
        'dilan'   => ['Dilan Rathnayake', 'dilan@newsdesk.test', 'CampusDemo26!', 'student', 33],
        'sachini' => ['Sachini Silva', 'sachini@newsdesk.test', 'CampusDemo26!', 'student', 21],
        'tharindu'=> ['Tharindu Bandara', 'tharindu@newsdesk.test', 'CampusDemo26!', 'student', 5],
        'amaya'   => ['Amaya de Alwis', 'amaya@newsdesk.test', 'CampusDemo26!', 'student', 2],
    ],

    // [author, category, kind, status, featured, breaking, hours ago, total views, image, expires in days|null, title, summary, body]
    'articles' => [
        ['admin', 'academic', 'news', 'approved', 1, 0, 5, 96, 'photo-1523050854058-8df90110c9f1',
         null, 'Convocation 2026: graduates to cross the stage in three ceremonies',
         'More than two thousand graduates from the faculties of Computing, Business and Engineering will receive their degrees across three ceremonies next month.',
         "The university has confirmed the schedule for this year's convocation, which will be held in three ceremonies so that every graduate can invite two guests.\n\nThe Faculty of Computing ceremony opens the programme, followed by Business and then Engineering. Graduates should collect their gowns from the student services counter during the week before their ceremony and bring their student ID.\n\nRehearsals take place the day before each ceremony. Attendance is compulsory for graduates who want to walk the stage, and late arrivals will be seated at the end of their faculty's row.\n\nPhotography packages will be available on the day, and the full ceremony will be streamed for family members who cannot attend in person."],

        ['admin', 'general', 'news', 'approved', 1, 0, 26, 150, 'photo-1542601906990-b4d3fb778b09',
         null, 'Green campus week plants 500 native saplings around the Pitipana grounds',
         'Volunteers from twelve clubs spent the week planting, labelling and mapping native trees as part of the campus sustainability plan.',
         "Green campus week ended on Friday with 500 native saplings planted along the walking paths, car park edges and the slope behind the engineering block.\n\nEach sapling now carries a small tag with its species name and the club that planted it. The environmental society has also mapped every tree so that volunteers can track survival rates over the coming year.\n\nOrganisers said the week was about more than numbers. Workshops on composting, reducing single-use plastics and saving energy in the hostels drew a full room every evening.\n\nStudents who want to adopt a sapling and water it during the dry months can sign up with the environmental society."],

        ['writer', 'sports', 'news', 'approved', 1, 0, 30, 190, 'photo-1546519638-68e109498ffc',
         null, 'Basketball team reaches the inter-university final after a one-point thriller',
         'A free throw with four seconds left sent the NSBM men’s basketball team into Saturday’s final.',
         "It came down to a single free throw. With the scores level and four seconds on the clock, the team's captain stepped up and made the shot that sent NSBM into the inter-university final.\n\nThe team trailed by eleven points at half time but changed to a full-court press after the break. The defence forced turnovers on four consecutive possessions and the lead changed hands six times in the final quarter.\n\nThe coach praised the bench players, who kept the pressure on while the starters rested. The final is on Saturday afternoon at the indoor stadium, and supporters' buses will leave from the main gate.\n\nStudents who want a seat on the bus should register at the sports office before Thursday."],

        ['student', 'clubs', 'news', 'approved', 0, 0, 52, 189, 'photo-1531482615713-2afd69097998',
         null, 'Computing society hackathon: 36 hours, 28 teams and one very tired panel of judges',
         'Teams built apps for local farmers, a bus tracker for the campus shuttle and a study planner that won the people’s choice award.',
         "Twenty-eight teams signed up for this year's computing society hackathon, and twenty-five of them made it to the final demo after 36 hours of building.\n\nThe winning team built a simple SMS service that lets small farmers check wholesale vegetable prices before they travel to market. The judges said it solved a real problem with technology that works on any phone.\n\nSecond place went to a live tracker for the campus shuttle bus, and the people's choice award went to a study planner that splits assignments into daily tasks.\n\nThe society says the best projects will get mentoring over the next term, and it plans to run a smaller beginner-friendly event for first-year students."],

        ['admin', 'academic', 'news', 'approved', 0, 0, 74, 301, 'photo-1481627834876-b7833e8f5570',
         null, 'Library extends opening hours for the exam period',
         'The main library will stay open until 10 pm on weekdays and open on Sundays for the four weeks before end-of-semester exams.',
         "From next Monday the main library will stay open until 10 pm on weekdays and from 9 am to 5 pm on Sundays until the end of the examination period.\n\nGroup study rooms can be booked for up to two hours at a time through the library counter. Rooms that are not claimed within fifteen minutes of the booking time will be released to students who are waiting.\n\nLibrary staff have also asked students not to reserve desks by leaving bags unattended for long periods during busy hours.\n\nThe quiet reading floor will remain a silent zone at all times. Phones should be switched to silent before entering."],

        ['ishara', 'events', 'news', 'approved', 0, 0, 98, 176, 'photo-1540575467063-178a50c2df87',
         null, 'Careers fair brings 40 employers to campus, with on-the-spot interviews',
         'Software, banking, logistics and design companies set up stands in the main hall, and several ran first-round interviews on the day.',
         "Forty employers took part in this semester's careers fair, the largest number since the event moved to the main hall.\n\nSeveral software and banking companies ran first-round interviews on the day, and students who brought printed CVs said it made a real difference.\n\nThe careers unit ran a CV clinic in the morning and a short talk on preparing for technical interviews in the afternoon. Both sessions were full within an hour.\n\nStudents who missed the fair can book a one-to-one appointment with the careers unit to get feedback on their CV and LinkedIn profile."],

        ['dilan', 'sports', 'news', 'approved', 0, 0, 120, 143, 'photo-1461896836934-ffe607ba8211',
         null, 'Athletics squad brings home six medals from the national university games',
         'Two gold, one silver and three bronze medals for NSBM athletes, including a new personal best in the women’s 400 metres.',
         "The NSBM athletics squad returned from the national university games with six medals: two gold, one silver and three bronze.\n\nThe highlight was a gold medal and personal best in the women's 400 metres, run in the final heat of the day. The men's 4 × 100 metres relay team also took gold after a clean final baton change.\n\nThe squad trains three mornings a week at the campus track. New members are welcome at any level, and the coaches run a separate session for beginners on Saturday mornings.\n\nThe team thanked the students who travelled to support them over the weekend."],

        ['writer', 'clubs', 'news', 'approved', 0, 0, 150, 98, 'photo-1492684223066-81342ee5ff30',
         null, 'Music society’s night under the stars draws a record crowd',
         'Eight student bands, a choir and a surprise acoustic set made the open-air concert the society’s biggest night yet.',
         "The music society's open-air concert drew its biggest crowd yet, with students and families filling the lawn in front of the main building.\n\nEight student bands performed, from acoustic duos to a full rock line-up, and the university choir closed the evening with a set of Sinhala and Tamil classics.\n\nThe society used the event to raise funds for new instruments for its practice room. Members said they were grateful to the student union for help with lighting and sound.\n\nAuditions for next semester's line-up open after the exam period."],

        ['admin', 'general', 'news', 'approved', 0, 0, 200, 220, 'photo-1497633762265-9d179a990aa6',
         null, 'New student support centre opens beside the cafeteria',
         'Registration, ID cards, transport passes and wellbeing appointments are now handled in one place.',
         "Students no longer need to visit three different offices to sort out administrative tasks. The new student support centre beside the cafeteria now handles registration changes, ID card replacements, transport passes and appointments with student counsellors.\n\nA queue ticket system shows waiting times on a screen outside the centre, and most requests can also be started online through the student portal.\n\nThe centre is open from 8.30 am to 4.30 pm on weekdays. Counselling appointments are confidential and free for all registered students.\n\nStaff ask students to bring their student ID to every visit."],

        ['sachini', 'academic', 'news', 'approved', 0, 0, 260, 134, 'photo-1522202176988-66273c2fd55f',
         null, 'Peer tutoring scheme pairs 120 first-year students with senior mentors',
         'The faculty of Computing’s peer tutoring scheme has doubled in size, with weekly sessions for programming, maths and databases.',
         "The peer tutoring scheme run by the faculty of Computing now pairs 120 first-year students with trained senior mentors, twice as many as last year.\n\nMentors run weekly small-group sessions on programming fundamentals, discrete mathematics and databases. Each mentor completes a short training course on how to explain ideas without simply giving away answers.\n\nFirst-year students said the sessions helped them feel less nervous about asking questions they thought were too basic for lectures.\n\nSenior students interested in becoming mentors next semester can apply through the faculty office."],

        ['ishara', 'events', 'news', 'approved', 0, 0, 320, 87, 'photo-1517486808906-6ca8b3f04846',
         null, 'Guest lecture: what building for a billion users teaches about simple design',
         'An industry engineer told a packed auditorium why the best products do less, not more.',
         "The auditorium was full for this month's guest lecture, where an industry software engineer spoke about lessons from building apps used by very large audiences.\n\nThe main message was that simple products scale better. Features that look impressive in a demo often confuse people who use the app on a cheap phone with a slow connection.\n\nThe speaker encouraged students to test their projects with real users early, and to measure what people actually do rather than what they say they want.\n\nThe lecture recording is available to students through the learning management system."],

        ['writer', 'sports', 'news', 'approved', 0, 0, 400, 66, 'photo-1574629810360-7efbbe195018',
         null, 'Women’s football team starts the season with a 3–1 win',
         'Two second-half goals turned a tight match into a confident opening victory.',
         "The women's football team opened the season with a 3–1 win in a match that stayed level until the hour mark.\n\nAfter conceding an early goal, the team equalised before half time with a header from a corner. Two goals in the second half, one from a long-range shot and one from a quick counter-attack, sealed the result.\n\nThe captain said the team's fitness work over the break made the difference late in the game.\n\nThe next home match is on Wednesday afternoon. Supporters are welcome at the campus ground."],

        ['dilan', 'clubs', 'news', 'approved', 0, 0, 480, 54, 'photo-1529156069898-49953e39b3ac',
         null, 'Leo Club volunteers paint and repair a village school library',
         'Thirty volunteers spent the weekend repainting, fixing shelves and cataloguing donated books.',
         "Thirty members of the university Leo Club spent the weekend at a village school, repainting its library, repairing shelves and cataloguing more than 600 donated books.\n\nThe club collected the books during a month-long drive on campus. Students also donated stationery, which was packed into kits for every child in the school.\n\nTeachers at the school said the library had been closed for repairs for over a year.\n\nThe club is planning a follow-up visit to run reading sessions with the children next semester."],

        ['admin', 'academic', 'news', 'approved', 0, 0, 560, 190, 'photo-1524178232363-1fb2b075b655',
         null, 'Final-year project showcase: applications open for exhibition spaces',
         'Final-year students can apply to present their projects to industry visitors at the end-of-year showcase.',
         "Final-year students from all faculties can now apply for a space at the end-of-year project showcase, where industry visitors, alumni and staff will see the best work of the year.\n\nApplications need a short project summary, a photo or screenshot and the name of the supervisor. A panel will select projects that show clear problem-solving and a working result.\n\nSelected students will get a display table, a power point and a poster printed by the university.\n\nThe faculty offices can answer questions about the application process."],

        // Announcements (shown on the notice board)
        ['admin', 'academic', 'announcement', 'approved', 0, 1, 3, 74, 'photo-1434030216411-0b793f4b4173',
         10, 'End-of-semester exam timetable is now published',
         'Check your personal timetable on the student portal and report any clashes to your faculty office by Friday.',
         "The final timetable for end-of-semester examinations is now available on the student portal.\n\nPlease check your personal timetable carefully. If two of your exams are scheduled at the same time, report the clash to your faculty office by Friday so that an alternative sitting can be arranged.\n\nRemember to bring your student ID card to every exam. Students without an ID will need to obtain a temporary pass from the student support centre before the exam starts."],

        ['admin', 'general', 'announcement', 'approved', 0, 0, 20, 58, 'photo-1544620347-c4fd4a3d5957',
         7, 'Shuttle bus timetable changes from Monday',
         'Morning shuttles from Kottawa and Homagama will run every 15 minutes between 7 and 9 am.',
         "To reduce waiting times during the busy morning period, shuttle buses from Kottawa and Homagama will run every 15 minutes between 7 am and 9 am starting Monday.\n\nAfternoon services remain unchanged. The last bus from campus still leaves at 6.30 pm.\n\nPlease have your transport pass ready when boarding. Passes can be renewed at the student support centre."],

        ['ishara', 'events', 'announcement', 'approved', 0, 0, 44, 70, 'photo-1511578314322-379afb476865',
         14, 'Call for volunteers: Freshers’ welcome day',
         'The student union needs 60 volunteers to guide new students, run information desks and help with the campus tour.',
         "The student union is looking for 60 volunteers for the Freshers' welcome day.\n\nVolunteers will guide new students around campus, staff information desks and help run the afternoon campus tour. Lunch and a volunteer T-shirt are provided.\n\nYou can sign up at the student union office. Volunteers must attend a one-hour briefing the day before the event."],

        ['admin', 'sports', 'announcement', 'approved', 0, 0, 70, 61, 'photo-1517649763962-0c623066013b',
         21, 'Sports trials for the new season',
         'Trials for cricket, football, basketball, netball and athletics run over the next two weeks. All levels are welcome.',
         "Trials for university teams in cricket, football, basketball, netball and athletics will take place over the next two weeks.\n\nCheck the notice board at the sports complex for the time and venue of each trial. Wear suitable sports clothing and bring water.\n\nStudents who are new to a sport are encouraged to attend. Coaches are looking for commitment as well as experience."],

        ['admin', 'general', 'announcement', 'approved', 0, 0, 900, 140, '',
         -3, 'Water supply interruption in hostel block B',
         'Water supply to hostel block B was interrupted for scheduled tank cleaning.',
         "Water supply to hostel block B was interrupted for scheduled tank cleaning. Students were asked to store water in advance.\n\nThe work has been completed and supply has returned to normal."],

        // Waiting for review (for the approval demo)
        ['student', 'clubs', 'news', 'pending', 0, 0, 6, 0, 'photo-1559136555-9303baea8ebd',
         null, 'Debate society qualifies for the national championship semi-finals',
         'After four rounds of debates on technology, education and the environment, the team is through to the last four.',
         "The debate society's senior team won four of its five rounds at the regional qualifiers to secure a place in the national championship semi-finals.\n\nThe motions covered artificial intelligence in schools, plastic bans and public transport funding. Judges said the team's rebuttals were among the strongest of the competition.\n\nThe society is looking for students to help the team practise before the semi-finals."],

        ['tharindu', 'events', 'announcement', 'pending', 0, 0, 2, 0, '',
         12, 'Photography walk around campus this Saturday',
         'Bring a phone or camera for a relaxed photo walk led by the photography club. Beginners welcome.',
         "The photography club is running a relaxed photo walk around campus on Saturday morning.\n\nWe will cover composition basics, shooting in bright sunlight and simple editing on your phone. You do not need a camera; a phone is enough.\n\nMeet at the main gate. The walk takes about two hours."],

        // Rejected (for the feedback demo)
        ['writer', 'sports', 'news', 'rejected', 0, 0, 90, 0, '',
         null, 'Friendly cricket match idea',
         'A suggestion for a friendly cricket match between faculties at some point this semester.',
         "I think it would be fun to have a friendly cricket match between the faculties some time this semester. Maybe on a weekend.\n\nLet me know if anyone is interested."],
    ],

    // [user, article index, status, hours ago, text]
    'comments' => [
        ['student', 0, 'approved', 3, 'Will there be a live stream link shared beforehand? My grandparents can’t travel.'],
        ['admin',   0, 'approved', 2, 'Yes, the link will be posted here and on the portal one week before the ceremony.'],
        ['ishara',  2, 'approved', 25, 'What a finish! Booking my seat on the supporters’ bus.'],
        ['sachini', 2, 'approved', 22, 'The defence in the third quarter was unreal.'],
        ['dilan',   3, 'approved', 48, 'The farmers’ price SMS idea is brilliant. Hope they keep building it.'],
        ['writer',  4, 'approved', 70, 'Sunday opening is a lifesaver, thank you!'],
        ['tharindu',4, 'pending', 4, 'Can we also get more charging points on the second floor?'],
        ['amaya',   1, 'pending', 1, 'How do we sign up to adopt a sapling?'],
        ['student', 5, 'approved', 90, 'Got a second interview from the fair. Bring printed CVs, people!'],
        ['sachini', 9, 'approved', 250, 'My mentor explained recursion better than any video I watched.'],
        ['dilan',   14, 'pending', 2, 'Is the timetable final or could dates still change?'],
        ['amaya',   7, 'rejected', 140, 'first!!!'],
    ],

    'bookmarks' => [
        ['student', [0, 2, 4, 14]], ['writer', [1, 3]], ['ishara', [5, 0]], ['sachini', [9, 4, 14]], ['dilan', [2, 6]],
    ],
    'subscriptions' => [
        ['student', ['academic', 'sports', 'clubs']], ['writer', ['sports', 'events']], ['ishara', ['events', 'academic']],
        ['sachini', ['academic']], ['dilan', ['sports', 'clubs']], ['tharindu', ['events']], ['amaya', ['general', 'academic']],
    ],
    'notifications' => [
        ['student', 'New in Sports: Basketball team reaches the inter-university final after a one-point thriller', 2, 0, 30],
        ['student', 'New in Academic: Convocation 2026: graduates to cross the stage in three ceremonies', 0, 0, 5],
        ['student', 'Your comment on “Convocation 2026: graduates to cross the stage in three ceremonies” is now visible.', 0, 1, 3],
        ['writer',  'Your story “Friendly cricket match idea” needs changes: Please add a proposed date, the teams involved and who is organising it.', -1, 0, 88],
    ],
];
