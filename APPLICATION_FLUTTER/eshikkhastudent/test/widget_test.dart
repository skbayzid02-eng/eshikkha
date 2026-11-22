import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:eshikkhastudent/localization/app_localizations.dart';
import 'package:eshikkhastudent/main.dart';
import 'package:eshikkhastudent/screens/academic_details_screen.dart';
import 'package:eshikkhastudent/screens/attendance_detail_screen.dart';
import 'package:eshikkhastudent/screens/guardian_home_screen.dart';
import 'package:eshikkhastudent/screens/student_dashboard_screen.dart';
import 'package:eshikkhastudent/screens/student_login_screen.dart';
import 'package:eshikkhastudent/screens/student_register_screen.dart';
import 'package:eshikkhastudent/services/api_service.dart';

void main() {
  testWidgets('Student App launches with Login Screen and enters Student Dashboard smoke test', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(800, 1200);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    await tester.pumpWidget(const EShikkhaStudentApp());
    await tester.pumpAndSettle();
    expect(find.text('Student Portal Login'), findsOneWidget);
    expect(find.text('Login to Portal'), findsOneWidget);

    final loginBtn = find.text('Login to Portal');
    await tester.ensureVisible(loginBtn);
    await tester.pumpAndSettle();
    await tester.tap(loginBtn);
    await tester.pumpAndSettle();

    expect(find.text('Hello Student'), findsOneWidget);
    expect(find.byIcon(Icons.home_filled), findsOneWidget);
    expect(find.byIcon(Icons.auto_stories_rounded), findsWidgets);
    expect(find.byIcon(Icons.how_to_reg_rounded), findsOneWidget);
    expect(find.byIcon(Icons.assessment_outlined), findsOneWidget);
    expect(find.byIcon(Icons.person_outline_rounded), findsOneWidget);
  });

  testWidgets('Guardian multi-child dashboard screen test', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(home: GuardianHomeScreen()));
    await tester.pump();
    expect(find.text('Guardian Dashboard'), findsOneWidget);
    expect(find.text('My Children'), findsOneWidget);
  });

  testWidgets('Student Dashboard renders 5 tabs and core features', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentDashboardScreen(studentId: 2),
      ),
    );
    await tester.pumpAndSettle();

    // Verify Home tab items
    expect(find.text('Hello Student'), findsOneWidget);
    expect(find.text('Assignment Status'), findsOneWidget);
    expect(find.text('Pickup where you left!'), findsOneWidget);

    // Verify bottom nav 5 items: Home, Study, Attendance, Results, Profile
    expect(find.byIcon(Icons.home_filled), findsOneWidget);
    expect(find.byIcon(Icons.auto_stories_rounded), findsWidgets);
    expect(find.byIcon(Icons.how_to_reg_rounded), findsOneWidget);
    expect(find.byIcon(Icons.assessment_outlined), findsOneWidget);
    expect(find.byIcon(Icons.person_outline_rounded), findsOneWidget);

    // Tap on Study tab
    await tester.tap(find.text('Study'));
    await tester.pumpAndSettle();
    expect(find.text('All Subjects'), findsOneWidget);
    expect(find.text('Homework & Tasks'), findsOneWidget);
    expect(find.text('Practicals & Labs'), findsOneWidget);
    expect(find.text('Class Timetable'), findsOneWidget);

    // Tap on Attendance tab
    await tester.tap(find.text('Attendance'));
    await tester.pumpAndSettle();
    expect(find.text("TODAY'S ATTENDANCE STATUS"), findsOneWidget);
    expect(find.text('Present Today'), findsOneWidget);

    // Tap on Results tab
    await tester.tap(find.text('Results'));
    await tester.pumpAndSettle();
    expect(find.text('My Results'), findsOneWidget);
    expect(find.text('Exams Results'), findsOneWidget);

    // Tap on Profile tab
    await tester.tap(find.text('Profile'));
    await tester.pumpAndSettle();
    expect(find.text('Student Profile'), findsOneWidget);
  });

  testWidgets('AttendanceDetailScreen renders all 6 attendance requirements', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: AttendanceDetailScreen(
          studentId: 2,
          studentName: 'Tanvir Ahmed',
        ),
      ),
    );
    await tester.pumpAndSettle();

    // 1. আজ উপস্থিত কি না (Today's status)
    expect(find.text('TODAY\'S ATTENDANCE STATUS'), findsOneWidget);
    expect(find.text('Present Today'), findsOneWidget);

    // 2. মোট উপস্থিতির শতাংশ (Overall Percentage)
    expect(find.text('Overall Attendance Rate'), findsOneWidget);
    expect(find.text('92.5%'), findsOneWidget);

    // 3. কোন দিন অনুপস্থিত ছিল (Absent Days)
    expect(find.textContaining('Absent Days'), findsOneWidget);
    expect(find.textContaining('02 Nov, 2026'), findsOneWidget);

    // 4. কোন দিন দেরিতে এসেছে (Late Arrival Days)
    await tester.tap(find.textContaining('Late Arrival Days'));
    await tester.pumpAndSettle();
    expect(find.textContaining('15 Nov, 2026'), findsOneWidget);

    // 5. মাসিক উপস্থিতি (Monthly Attendance)
    await tester.tap(find.text('Monthly Attendance'));
    await tester.pumpAndSettle();
    expect(find.text('November 2026'), findsOneWidget);
    expect(find.text('October 2026'), findsOneWidget);

    // 6. ছুটির ইতিহাস (Leave / Holiday History)
    await tester.tap(find.textContaining('Leave & Holiday History'));
    await tester.pumpAndSettle();
    expect(find.textContaining('Medical Leave'), findsOneWidget);
  });

  testWidgets('Language toggle changes localization from English to Bengali', (WidgetTester tester) async {
    AppLanguage.changeLanguage('en');
    expect(AppStrings.tr('assignment_status'), 'Assignment Status');
    expect(AppStrings.tr('overall_percentage_title'), 'Overall Attendance Rate');

    AppLanguage.changeLanguage('bn');
    expect(AppStrings.tr('assignment_status'), 'হোমওয়ার্ক ও অ্যাসাইনমেন্ট');
    expect(AppStrings.tr('overall_percentage_title'), 'মোট উপস্থিতির শতাংশ');

    // Reset back to English
    AppLanguage.changeLanguage('en');
  });

  testWidgets('AcademicDetailsScreen renders image-matched UI and all 10 exam requirements', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: AcademicDetailsScreen(
          studentId: 2,
          studentName: 'Tanvir Ahmed',
        ),
      ),
    );
    await tester.pumpAndSettle();

    // Verify Title & Dual-Tab Switcher
    expect(find.text('Academic Details'), findsOneWidget);
    expect(find.text('Internal Marks'), findsOneWidget);
    expect(find.text('Grade Sheet'), findsOneWidget);

    // Tab 1: Internal Marks (Vertical Bar Chart & Breakdown)
    expect(find.text('MATHS'), findsOneWidget);
    expect(find.text('MOS'), findsOneWidget);
    expect(find.text('BME'), findsOneWidget);
    expect(find.text('BE'), findsOneWidget);
    expect(find.text('PHYSICS'), findsOneWidget);
    expect(find.text('ENGLISH'), findsOneWidget);

    // Selected Subject Breakdown (Mathematics III 38/45)
    expect(find.text('Mathematics III'), findsOneWidget);
    expect(find.text('38/45'), findsOneWidget);
    expect(find.textContaining('Assignment 1'), findsOneWidget);
    expect(find.textContaining('Sessional 1'), findsOneWidget);
    expect(find.text('Attendance'), findsOneWidget);
    expect(find.text('85%'), findsOneWidget);

    // 10 Requirements in Tab 1 Overview
    expect(find.text('1st Midterm Examination 2026'), findsOneWidget); // 1. পরীক্ষার নাম
    expect(find.textContaining('15 Sep to 18 Sep'), findsOneWidget); // 2. পরীক্ষার তারিখ
    expect(find.textContaining('#1 (Sec Rank)'), findsOneWidget); // 9. মেধাক্রম

    // Switch to Tab 2: Grade Sheet
    await tester.tap(find.text('Grade Sheet'));
    await tester.pumpAndSettle();

    // Verify CGPA Header & Semester Slider
    expect(find.text('CGPA : 8.6'), findsOneWidget); // 7. CGPA
    expect(find.text('SEM 1'), findsOneWidget);
    expect(find.text('SEM 2'), findsOneWidget);
    expect(find.text('SEM 3'), findsOneWidget);
    expect(find.text('GPA | '), findsOneWidget);
    expect(find.text('8.84'), findsOneWidget);

    // Verify Subjects Table headers and rows
    expect(find.text('Subject'), findsOneWidget);
    expect(find.text('Credits'), findsOneWidget);
    expect(find.text('Grade'), findsOneWidget); // 6. গ্রেড
    expect(find.text('MOS'), findsOneWidget);
    expect(find.text('A+'), findsWidgets);
    expect(find.text('Workshop'), findsOneWidget);

    // Verify Merit Position, Full Marks, Obtained Marks
    expect(find.text('Merit Position'), findsOneWidget); // 9. মেধাক্রম
    expect(find.textContaining('Full Marks: 800'), findsOneWidget); // 4. পূর্ণমান
    expect(find.textContaining('Obtained Marks: 658'), findsOneWidget); // 5. প্রাপ্ত নম্বর
    expect(find.text('82.25%'), findsOneWidget); // 8. মোট নম্বর শতাংশ

    // Verify 10. PDF Marksheet Button & Dialog
    final pdfButton = find.text('Download / Print PDF Marksheet');
    expect(pdfButton, findsOneWidget);
    await tester.ensureVisible(pdfButton);
    await tester.pumpAndSettle();
    await tester.tap(pdfButton);
    await tester.pumpAndSettle();

    expect(find.text('Official Academic Marksheet'), findsOneWidget);
    expect(find.byIcon(Icons.picture_as_pdf_rounded), findsWidgets);
  });

  testWidgets('Student Profile renders all 10 Profile Hub components and dialogs', (WidgetTester tester) async {
    AppLanguage.changeLanguage('en');
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentDashboardScreen(studentId: 2),
      ),
    );
    await tester.pumpAndSettle();

    // Tap on Profile bottom nav item
    await tester.tap(find.text('Profile'));
    await tester.pumpAndSettle();

    // Verify Title & Verified badge
    expect(find.text('Student Profile'), findsOneWidget);
    expect(find.text('Verified'), findsOneWidget);

    // 1. Personal Information (শিক্ষার্থীর ব্যক্তিগত তথ্য)
    expect(find.text('Personal Information'), findsOneWidget);
    expect(find.text('Full Name'), findsOneWidget);
    expect(find.text("Father's Name"), findsWidgets);
    expect(find.text("Mother's Name"), findsWidgets);
    expect(find.text('Date of Birth'), findsOneWidget);
    expect(find.text('Emergency Contact'), findsOneWidget);

    // 2. Student ID (ডিজিটাল স্টুডেন্ট আইডি কার্ড)
    expect(find.text('Digital Student ID'), findsOneWidget);
    expect(find.text('SMART PVC ID'), findsOneWidget);
    expect(find.byIcon(Icons.qr_code_2_rounded), findsWidgets);

    // 3. Institution Details (প্রতিষ্ঠানের তথ্য)
    expect(find.text('Institution Details'), findsOneWidget);
    expect(find.text('EIIN Number'), findsOneWidget);
    expect(find.text('Institute Code'), findsOneWidget);

    // 4. Guardian Information (অভিভাবকের তথ্য)
    expect(find.text('Guardian Information'), findsOneWidget);
    expect(find.text('Guardian Mobile'), findsOneWidget);
    expect(find.text('Call Guardian Emergency'), findsOneWidget);

    // 5. Fees & Online Payments (ফি ও পেমেন্ট)
    expect(find.text('Fees & Online Payments'), findsOneWidget);
    expect(find.text('Total Fees'), findsOneWidget);
    expect(find.text('Paid'), findsOneWidget);
    expect(find.text('Due Balance'), findsOneWidget);

    // 6. Notifications & Alerts (নোটিফিকেশন)
    expect(find.text('Notifications & Alerts'), findsOneWidget);
    expect(find.text('Push Notifications'), findsOneWidget);
    expect(find.text('SMS Alerts'), findsOneWidget);

    // 7. Teacher Messages (মেসেজ)
    expect(find.text('Teacher Messages'), findsOneWidget);

    // 8. Settings & Language (সেটিংস ও ভাষা পরিবর্তন)
    expect(find.text('Settings & Language'), findsOneWidget);
    expect(find.text('Select Language'), findsOneWidget);
    expect(find.text('Biometric Auth Login'), findsOneWidget);
    expect(find.text('Change Password'), findsOneWidget);

    // 9. Help & Support (Help & Support 16300)
    expect(find.text('Help & Support (16300)'), findsOneWidget);
    expect(find.text('National Education Helpline'), findsOneWidget);
    expect(find.textContaining('16300'), findsWidgets);

    // 10. Logout (লগআউট)
    expect(find.text('Logout Account'), findsOneWidget);

    // Test Digital ID Card Full Modal
    final fullCardButton = find.text('Full Card');
    expect(fullCardButton, findsOneWidget);
    await tester.ensureVisible(fullCardButton);
    await tester.pumpAndSettle();
    await tester.tap(fullCardButton);
    await tester.pumpAndSettle();
    expect(find.text('Digital Smart Student ID'), findsOneWidget);
    await tester.tap(find.text('Done'));
    await tester.pumpAndSettle();

    // Test Online Fee Payment Modal
    final payFeeButton = find.text('Pay Due Fees Online (bKash / Nagad)');
    expect(payFeeButton, findsOneWidget);
    await tester.ensureVisible(payFeeButton);
    await tester.pumpAndSettle();
    await tester.tap(payFeeButton);
    await tester.pumpAndSettle();
    expect(find.text('Online Tuition Fee Payment'), findsOneWidget);
    expect(find.text('bKash'), findsWidgets);
    expect(find.text('Nagad'), findsOneWidget);
    expect(find.text('Rocket'), findsOneWidget);
    // Tap Confirm & Pay
    await tester.tap(find.textContaining('Confirm & Pay'));
    await tester.pumpAndSettle();

    // Test Logout Confirmation Dialog
    final logoutButton = find.text('Logout Account');
    await tester.ensureVisible(logoutButton);
    await tester.pumpAndSettle();
    await tester.tap(logoutButton);
    await tester.pumpAndSettle();
    expect(find.text('Confirm Logout'), findsOneWidget);
    expect(find.text('Are you sure you want to log out from the student account?'), findsOneWidget);
    await tester.tap(find.text('Cancel'));
    await tester.pumpAndSettle();
  });

  testWidgets('StudentLoginScreen renders login fields and switches to guardian portal', (WidgetTester tester) async {
    AppLanguage.changeLanguage('en');
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentLoginScreen(),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Student Portal Login'), findsOneWidget);
    expect(find.byType(TextField), findsNWidgets(2));
    expect(find.text('Login to Portal'), findsOneWidget);
    expect(find.text('Guardian Portal'), findsOneWidget);
  });

  testWidgets('Student Registration for Class 1 and redirect to Login with email prefilled', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(800, 1200);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    AppLanguage.changeLanguage('en');
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentRegisterScreen(),
      ),
    );
    await tester.pumpAndSettle();

    // Verify Title and fields
    expect(find.text('Student Registration'), findsOneWidget);
    expect(find.text('Student Full Name'), findsOneWidget);
    expect(find.text('Email Address'), findsOneWidget);
    expect(find.text('Roll Number'), findsOneWidget);
    expect(find.text('Select Class'), findsWidgets);

    // Select Class 1
    final classDropdown = find.byType(DropdownButtonFormField<String>).last;
    await tester.ensureVisible(classDropdown);
    await tester.tap(classDropdown);
    await tester.pumpAndSettle();
    await tester.tap(find.text('Class 1 (প্রথম শ্রেণি)').last);
    await tester.pumpAndSettle();
    
    final regButton = find.text('Complete Registration');
    expect(regButton, findsOneWidget);
    await tester.ensureVisible(regButton);
    await tester.pumpAndSettle();

    // Tap Complete Registration
    await tester.tap(regButton);
    await tester.pumpAndSettle();

    // Verify redirected to Login Screen with the registered email prefilled
    expect(find.text('Student Portal Login'), findsOneWidget);
    expect(find.text('aaryan.class1@eshikkha.edu.bd'), findsOneWidget);
  });

  testWidgets('Student Registration renders 9 numbered fields, 13 Institution Types and 20 Class options', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    AppLanguage.changeLanguage('en');
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentRegisterScreen(),
      ),
    );
    await tester.pumpAndSettle();

    // Verify numbered fields including Father & Mother Name
    expect(find.text('Student Full Name'), findsOneWidget);
    expect(find.text('Father\'s Name'), findsOneWidget);
    expect(find.text('Mother\'s Name'), findsOneWidget);
    expect(find.text('Email Address'), findsOneWidget);
    expect(find.text('Institution Type'), findsOneWidget);
    expect(find.text('Institution Name'), findsOneWidget);
    expect(find.text('Roll Number'), findsOneWidget);
    expect(find.text('Select Class'), findsWidgets);
    expect(find.text('Password'), findsWidgets);
    expect(find.text('Confirm Password'), findsWidgets);
    expect(find.text('Complete Registration'), findsOneWidget);

    // Verify Default selected institution type from the 13 types
    expect(find.text('5. Primary School'), findsWidgets);
  });

  testWidgets('Class 1 Student Login enters Class 1 Home Page and views personalized curriculum', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(800, 1200);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    AppLanguage.changeLanguage('en');
    // Simulate Class 1 registration state
    ApiService.registeredStudentData = {
      'id': 101,
      'name': 'আরিয়ান হাসান (Aaryan Hasan)',
      'email': 'aaryan.class1@eshikkha.edu.bd',
      'roll_number': 1,
      'admission_number': 'ADM-2026-C01-001',
      'class_name': 'Class 1 (প্রথম শ্রেণি)',
      'father_name': 'মাহমুদুল হাসান (Mahmudul Hasan)',
      'mother_name': 'আমিনা বেগম (Amina Begum)',
      'academic_class': {'name': 'Class 1 (প্রথম শ্রেণি)', 'numeric_level': 1},
      'class': {'name': 'Class 1 (প্রথম শ্রেণি)', 'medium': 'bangla'},
    };
    ApiService.registeredEmail = 'aaryan.class1@eshikkha.edu.bd';
    ApiService.currentStudentData = ApiService.registeredStudentData;

    await tester.pumpWidget(
      const MaterialApp(
        home: StudentLoginScreen(prefilledEmail: 'aaryan.class1@eshikkha.edu.bd'),
      ),
    );
    await tester.pumpAndSettle();

    // Enter password
    await tester.enterText(find.byType(TextField).at(1), 'secret123');
    await tester.pumpAndSettle();

    final loginButton = find.text('Login to Portal');
    expect(loginButton, findsOneWidget);
    await tester.ensureVisible(loginButton);
    await tester.pumpAndSettle();

    // Tap Login to Portal
    await tester.tap(loginButton);
    await tester.pumpAndSettle();

    // Verify landed on Class 1 personalized Home Page
    expect(find.text('Welcome to Class 1 Portal!'), findsOneWidget);
    expect(find.textContaining('Class 1'), findsWidgets);
  });

  testWidgets('Homepage Textbooks section renders class books, cover, read, PDF, and chapters', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    AppLanguage.changeLanguage('bn');
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentDashboardScreen(studentId: 2),
      ),
    );
    await tester.pumpAndSettle();

    // Verify Homepage Textbooks section is displayed
    expect(find.text('আমার পাঠ্যবই'), findsWidgets);

    // Verify books for the student
    expect(find.text('আমার বাংলা বই'), findsWidgets);

    // Verify card buttons: 'বই পড়ুন', 'PDF পড়ুন', 'অধ্যায়সমূহ'
    expect(find.text('বই পড়ুন'), findsWidgets);
    expect(find.text('PDF পড়ুন'), findsWidgets);
    expect(find.text('অধ্যায়সমূহ'), findsWidgets);

    // Tap 'PDF পড়ুন' on first book
    final pdfButton = find.text('PDF পড়ুন').first;
    await tester.ensureVisible(pdfButton);
    await tester.tap(pdfButton);
    await tester.pumpAndSettle();

    // Verify PDF viewer opened
    expect(find.textContaining('(PDF)'), findsWidgets);
    expect(find.text('সূচিপত্র'), findsWidgets);

    // Close PDF viewer
    final closeButton = find.byIcon(Icons.close_rounded).first;
    await tester.tap(closeButton);
    await tester.pumpAndSettle();

    // Tap 'অধ্যায়সমূহ' on first book
    final chaptersButton = find.text('অধ্যায়সমূহ').first;
    await tester.ensureVisible(chaptersButton);
    await tester.tap(chaptersButton);
    await tester.pumpAndSettle();

    // Verify Chapter-wise reading opened
    expect(find.textContaining('১'), findsWidgets);
    expect(find.textContaining('পাঠ্য'), findsWidgets);

    // Close chapter reader
    final closeChapterBtn = find.byIcon(Icons.close_rounded).first;
    await tester.tap(closeChapterBtn);
    await tester.pumpAndSettle();
  });

  testWidgets('Textbook Live Class feature opens Zoom and in-app live classroom', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: StudentDashboardScreen(studentId: 2),
      ),
    );
    await tester.pumpAndSettle();

    // Verify Live Class join buttons are present on textbook cards
    expect(find.text('যুক্ত হোন'), findsWidgets);

    // Tap 'যুক্ত হোন' on first textbook's live class strip
    final joinBtn = find.text('যুক্ত হোন').first;
    await tester.ensureVisible(joinBtn);
    await tester.tap(joinBtn);
    await tester.pumpAndSettle();

    // Verify Live Class modal opened with teacher and Zoom credentials
    expect(find.text('শিক্ষকদের লাইভ ক্লাস'), findsOneWidget);
    expect(find.text('জুম ক্লাসে যুক্ত হোন'), findsOneWidget);
    expect(find.text('মিটিং আইডি:'), findsOneWidget);
    expect(find.text('পাসকোড:'), findsOneWidget);
    expect(find.text('LIVE NOW'), findsOneWidget);

    // Tap In-App Classroom
    final inAppBtn = find.textContaining('ইন-অ্যাপ ক্লাসরুমে প্রবেশ করুন');
    await tester.tap(inAppBtn);
    await tester.pumpAndSettle();

    // Verify in-app live classroom canvas & chat
    expect(find.textContaining('লাইভ প্রশ্নোত্তর ও চ্যাট'), findsOneWidget);
    expect(find.byIcon(Icons.mic_rounded), findsOneWidget);
    expect(find.byIcon(Icons.videocam_rounded), findsOneWidget);
  });

  testWidgets('Verify zero RenderFlex layout overflows on phone viewport (360x780)', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(360, 780);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    await tester.pumpWidget(
      const MaterialApp(
        home: StudentDashboardScreen(studentId: 2),
      ),
    );
    await tester.pumpAndSettle();

    // 1. Home Tab: check zero overflow exceptions
    expect(tester.takeException(), isNull);
    expect(find.text(AppStrings.tr('assignment_status')), findsOneWidget);
    expect(find.text(AppStrings.tr('pickup_where_you_left')), findsOneWidget);

    // 2. Open Teacher Messages Dialog: check zero overflow exceptions
    final notifBtn = find.byIcon(Icons.notifications_none_rounded);
    await tester.tap(notifBtn);
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
    expect(find.text(AppStrings.tr('teacher_messages_title')), findsOneWidget);

    // Close dialog
    await tester.tap(find.text(AppStrings.tr('close')));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);

    // 3. Open Drawer / Modal Bottom Sheet: check zero overflow exceptions
    final menuBtn = find.byIcon(Icons.menu_rounded);
    await tester.tap(menuBtn);
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
    expect(find.text(AppStrings.tr('language')), findsOneWidget);

    // Close sheet
    Navigator.of(tester.element(find.text(AppStrings.tr('language')))).pop();
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);

    // 4. Study Tab: check zero overflow exceptions
    await tester.tap(find.byIcon(Icons.auto_stories_rounded).first);
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);

    // 5. Attendance Tab: check zero overflow exceptions
    await tester.tap(find.byIcon(Icons.how_to_reg_rounded));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);

    // 6. Results Tab: check zero overflow exceptions
    await tester.tap(find.byIcon(Icons.assessment_outlined));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);

    // 7. Profile Tab: check zero overflow exceptions
    await tester.tap(find.byIcon(Icons.person_outline_rounded));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
  });
}
