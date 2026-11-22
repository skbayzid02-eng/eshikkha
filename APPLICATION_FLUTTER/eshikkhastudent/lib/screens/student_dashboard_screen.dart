import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/student_model.dart';
import '../models/textbook_model.dart';
import '../services/api_service.dart';
import 'guardian_home_screen.dart';
import 'academic_details_screen.dart';
import 'student_home_tab.dart';
import 'student_study_tab.dart';
import 'student_attendance_tab.dart';
import 'student_results_tab.dart';
import 'student_profile_tab.dart';

class StudentDashboardScreen extends StatefulWidget {
  final int studentId;

  const StudentDashboardScreen({super.key, this.studentId = 2});

  @override
  State<StudentDashboardScreen> createState() => _StudentDashboardScreenState();
}

class _StudentDashboardScreenState extends State<StudentDashboardScreen> {
  int _currentIndex = 0;
  bool _isLoading = true;
  StudentProfile? _profile;
  List<TextbookModel> _textbooks = [];
  List<HomeworkItem> _homeworks = [];
  List<RoutineItem> _routines = [];
  List<ExamScheduleItem> _examSchedules = [];
  List<ExamTerm> _examTerms = [];
  List<Map<String, dynamic>> _messages = [];
  List<Map<String, dynamic>> _notices = [];
  List<StudySubjectItem> _studySubjects = [];
  List<PracticalLabItem> _practicals = [];
  Map<String, dynamic>? _profileHub;
  double _dueFeesAmount = 3500.0;
  List<Map<String, dynamic>> _profileRecentFees = [];
  String _todayAttendanceStatus = 'present';
  String _todayAttendanceRemarks = 'RFID Gate Entry: 07:54 AM';
  double _attendancePercentage = 96.4;
  double _overallPercentage = 58.0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final data = await ApiService.getStudentPortalData(studentId: widget.studentId);
    final profileHubData = await ApiService.getStudentProfileHub(studentId: widget.studentId);

    if (mounted) {
      final profJson = (data['profile'] ?? data['student']) as Map<String, dynamic>? ?? {};
      final attJson = (data['dashboard']?['attendance'] ?? data['attendance']) as Map<String, dynamic>? ?? {};
      final hwData = data['homework'];
      final hwList = (hwData is Map ? (hwData['homeworks'] ?? hwData['items']) : hwData) as List? ?? [];
      final rtData = data['routine'];
      final rtList = (rtData is Map ? (rtData['all_routines'] ?? rtData['items']) : rtData) as List? ?? [];
      final exData = data['exam_routine'] ?? data['exam_schedule'];
      final exList = (exData is Map ? (exData['exam_schedules'] ?? exData['items']) : exData) as List? ?? [];
      final resJson = (data['results'] ?? data['dashboard']?['results_summary']) as Map<String, dynamic>? ?? {};
      final termList = (resJson['terms'] ?? data['terms']) as List? ?? [];
      final msgData = data['messages'];
      final msgList = (msgData is Map ? (msgData['messages'] ?? msgData['items']) : msgData) as List? ?? [];
      final notData = data['notices'];
      final notList = (notData is Map ? (notData['notices'] ?? notData['items']) : notData) as List? ?? [];
      final studyJson = data['study'] as Map<String, dynamic>? ?? {};
      final subList = studyJson['subjects'] as List? ?? [];
      final pracList = studyJson['practicals'] as List? ?? [];

      final profileObj = StudentProfile.fromJson(profJson);
      final initialLevel = ApiService.resolveClassLevel(profileObj.className);
      final textbooks = await ApiService.getTextbooks(
        className: profileObj.className,
        classLevel: initialLevel,
        group: profileObj.group,
        studentId: widget.studentId,
      );

      setState(() {
        _profileHub = profileHubData;
        final feesMap = profileHubData['fees_and_payments'] as Map<String, dynamic>? ?? {};
        _dueFeesAmount = (feesMap['due_amount'] as num?)?.toDouble() ?? 3500.0;
        final historyList = feesMap['recent_history'] as List? ?? [];
        _profileRecentFees = historyList.map((e) => Map<String, dynamic>.from(e)).toList();

        _profile = profileObj;
        _textbooks = textbooks;
        _todayAttendanceStatus = attJson['today_status'] ?? 'present';
        _todayAttendanceRemarks = attJson['today_remarks'] ?? 'RFID Gate Entry: 07:54 AM';
        _attendancePercentage = (attJson['percentage'] as num?)?.toDouble() ?? 96.4;
        _overallPercentage = (resJson['overall_percentage'] as num?)?.toDouble() ?? 58.0;

        _homeworks = hwList.map((e) => HomeworkItem.fromJson(e)).toList();
        _routines = rtList.map((e) => RoutineItem.fromJson(e)).toList();
        _examSchedules = exList.map((e) => ExamScheduleItem.fromJson(e)).toList();
        _examTerms = termList.map((e) => ExamTerm.fromJson(e)).toList();
        _messages = msgList.map((e) => Map<String, dynamic>.from(e)).toList();
        _notices = notList.map((e) => Map<String, dynamic>.from(e)).toList();
        _studySubjects = subList.isNotEmpty
            ? subList.map((e) => StudySubjectItem.fromJson(e)).toList()
            : _buildDefaultStudySubjects();
        _practicals = pracList.isNotEmpty
            ? pracList.map((e) => PracticalLabItem.fromJson(e)).toList()
            : _buildDefaultPracticals();
        _isLoading = false;
      });
    }
  }

  bool get _isClass1 {
    final name = (_profile?.className ?? '').trim().toLowerCase();
    if (name.isEmpty) return false;
    if (name.contains('class 10') || name.contains('class 11') || name.contains('class 12')) return false;
    if (name.contains('দশম') || name.contains('একাদশ') || name.contains('দ্বাদশ')) return false;
    if (name.contains('১০') || name.contains('১১') || name.contains('১২')) return false;
    return name == 'class 1' ||
        name.startsWith('class 1 ') ||
        name.startsWith('class 1(') ||
        name.contains('(class 1)') ||
        name.contains('class 1 (') ||
        name.contains('প্রথম') ||
        name.contains('১ম');
  }

  void _showMessagesDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.mail_rounded, color: Color(0xFF2563EB)),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                AppStrings.tr('teacher_messages_title'),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        content: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 380, maxHeight: 420),
          child: _messages.isEmpty
              ? const Padding(
                  padding: EdgeInsets.all(16),
                  child: Text('No messages from teachers yet.'),
                )
              : ListView.separated(
                  shrinkWrap: true,
                  itemCount: _messages.length,
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (ctx, i) {
                    final m = _messages[i];
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const CircleAvatar(
                        backgroundColor: Color(0xFFEFF6FF),
                        child: Icon(Icons.person, color: Color(0xFF2563EB)),
                      ),
                      title: Text(
                        m['sender_name'] ?? 'Teacher',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(m['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                          Text(m['message'] ?? '', style: TextStyle(color: Colors.grey.shade700, fontSize: 12)),
                        ],
                      ),
                    );
                  },
                ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(AppStrings.tr('close')),
          ),
        ],
      ),
    );
  }

  List<StudySubjectItem> _buildDefaultStudySubjects() {
    return [
      StudySubjectItem(
        id: 1,
        name: 'Higher Mathematics',
        banglaName: 'উচ্চতর গণিত',
        code: 'HM-265',
        chaptersCount: 10,
        completedChapters: 7,
        progress: 70,
        teacherName: 'মুহাম্মদ রফিকুল ইসলাম',
        teacherDesignation: 'সিনিয়র সহকারী শিক্ষক',
        room: 'Room 301',
        colorTheme: 'blue',
        icon: 'calculate_rounded',
        materialsCount: 14,
        topics: ['Matrix & Determinants', 'Vectors', 'Differentiation', 'Integration', 'Coordinate Geometry'],
      ),
      StudySubjectItem(
        id: 2,
        name: 'Physics',
        banglaName: 'পদার্থবিজ্ঞান',
        code: 'PHY-174',
        chaptersCount: 10,
        completedChapters: 6,
        progress: 60,
        teacherName: 'ড. আহসান হাবীব',
        teacherDesignation: 'সহযোগী অধ্যাপক',
        room: 'Lab 2',
        colorTheme: 'orange',
        icon: 'bolt_rounded',
        materialsCount: 12,
        topics: ['Vector Mechanics', 'Work, Power & Energy', 'Gravitation & Gravity'],
      ),
      StudySubjectItem(
        id: 3,
        name: 'Chemistry',
        banglaName: 'রসায়ন',
        code: 'CHEM-176',
        chaptersCount: 8,
        completedChapters: 5,
        progress: 62,
        teacherName: 'মোঃ মোখলেসুর রহমান',
        teacherDesignation: 'সহকারী অধ্যাপক',
        room: 'Lab 1',
        colorTheme: 'emerald',
        icon: 'science_rounded',
        materialsCount: 10,
        topics: ['Qualitative Chemistry', 'Periodic Table', 'Chemical Changes'],
      ),
      StudySubjectItem(
        id: 4,
        name: 'English',
        banglaName: 'ইংরেজি',
        code: 'ENG-107',
        chaptersCount: 12,
        completedChapters: 9,
        progress: 75,
        teacherName: 'মিসেস ফারহানা আক্তার',
        teacherDesignation: 'প্রভাষক',
        room: 'Room 301',
        colorTheme: 'purple',
        icon: 'menu_book_rounded',
        materialsCount: 16,
        topics: ['Reading Skills', 'Grammar in Context', 'Writing Formal Letters & Essays'],
      ),
      StudySubjectItem(
        id: 5,
        name: 'ICT',
        banglaName: 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)',
        code: 'ICT-275',
        chaptersCount: 6,
        completedChapters: 5,
        progress: 83,
        teacherName: 'প্রকৌশলী তানভীর আহমেদ',
        teacherDesignation: 'আইসিটি ইনস্ট্রাক্টর',
        room: 'Computer Lab',
        colorTheme: 'teal',
        icon: 'computer_rounded',
        materialsCount: 18,
        topics: ['Database Management', 'Web Design & HTML', 'C Programming', 'Digital Bangladesh & AI'],
      ),
      StudySubjectItem(
        id: 6,
        name: 'Biology',
        banglaName: 'জীববিজ্ঞান',
        code: 'BIO-178',
        chaptersCount: 12,
        completedChapters: 7,
        progress: 58,
        teacherName: 'ড. নাসরিন জাহান',
        teacherDesignation: 'সহযোগী অধ্যাপক',
        room: 'Biology Lab',
        colorTheme: 'blue',
        icon: 'biotech_rounded',
        materialsCount: 11,
        topics: ['Cell Structure', 'Plant Physiology', 'Genetics & Evolution'],
      ),
    ];
  }

  List<PracticalLabItem> _buildDefaultPracticals() {
    return [
      PracticalLabItem(
        id: 1,
        title: 'Organic Compounds Synthesis',
        banglaTitle: 'জৈব যৌগ সংশ্লেষণ',
        subject: 'Chemistry',
        experimentsCount: 6,
        labRoom: 'Lab 1',
        icon: 'science_rounded',
        colorTheme: 'teal',
      ),
      PracticalLabItem(
        id: 2,
        title: 'Youngs Modulus Experiment',
        banglaTitle: 'ইয়ং-এর গুণাঙ্ক নির্ণয়',
        subject: 'Physics',
        experimentsCount: 8,
        labRoom: 'Lab 2',
        icon: 'bolt_rounded',
        colorTheme: 'orange',
      ),
      PracticalLabItem(
        id: 3,
        title: 'Plant Tissue Cell Division',
        banglaTitle: 'কোষ বিভাজন পরীক্ষণ',
        subject: 'Biology',
        experimentsCount: 5,
        labRoom: 'Bio Lab',
        icon: 'biotech_rounded',
        colorTheme: 'blue',
      ),
      PracticalLabItem(
        id: 4,
        title: 'SQL Database Connectivity',
        banglaTitle: 'এসকিউএল ডাটাবেজ প্র্যাক্টিক্যাল',
        subject: 'ICT',
        experimentsCount: 10,
        labRoom: 'PC Lab 3',
        icon: 'computer_rounded',
        colorTheme: 'purple',
      ),
    ];
  }

  void _openDrawerMenu() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SafeArea(
            top: false,
            child: SingleChildScrollView(
              padding: EdgeInsets.fromLTRB(20, 14, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(width: 44, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
                  const SizedBox(height: 18),
                  ListTile(
                    leading: const Icon(Icons.school, color: Color(0xFF2563EB)),
                    title: Text(AppStrings.tr('student_view'), style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Text(_profile?.name ?? 'Tanvir Ahmed'),
                    trailing: const Icon(Icons.check_circle, color: Color(0xFF2563EB)),
                  ),
                  const Divider(),
                  ListTile(
                    leading: const Icon(Icons.analytics_outlined, color: Color(0xFFF59E0B)),
                    title: Text(AppStrings.tr('academic_details_title'), style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: const Text('Internal Marks, Grade Sheet & Official Marksheet'),
                    trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 16),
                    onTap: () {
                      Navigator.pop(ctx);
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => AcademicDetailsScreen(
                            studentId: _profile?.id ?? widget.studentId,
                            studentName: _profile?.name ?? 'Tanvir Ahmed',
                          ),
                        ),
                      );
                    },
                  ),
                  const Divider(),
                  ListTile(
                    leading: const Icon(Icons.family_restroom, color: Color(0xFF10B981)),
                    title: Text(AppStrings.tr('guardian_view'), style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: const Text('Md. Rafiqul Islam Chowdhury (4 Children)'),
                    onTap: () {
                      Navigator.pop(ctx);
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(builder: (_) => const GuardianHomeScreen()),
                      );
                    },
                  ),
                  const Divider(),
                  ListTile(
                    leading: const Icon(Icons.language, color: Color(0xFF6366F1)),
                    title: Text(AppStrings.tr('language')),
                    subtitle: Text(AppLanguage.isEnglish ? 'English (International)' : 'বাংলা (Bengali)'),
                    trailing: Switch(
                      value: AppLanguage.isBangla,
                      onChanged: (val) {
                        AppLanguage.changeLanguage(val ? 'bn' : 'en');
                        setState(() {});
                        Navigator.pop(ctx);
                      },
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildBottomNav() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Colors.grey.shade200)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: const Color(0xFF5B4DF6),
        unselectedItemColor: const Color(0xFF94A3B8),
        selectedFontSize: 12,
        unselectedFontSize: 12,
        elevation: 0,
        items: [
          BottomNavigationBarItem(
            icon: const Icon(Icons.home_filled),
            label: AppStrings.tr('home_tab'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.auto_stories_rounded),
            label: AppStrings.tr('study_tab'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.how_to_reg_rounded),
            label: AppStrings.tr('attendance_tab'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.assessment_outlined),
            label: AppStrings.tr('results_tab'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.person_outline_rounded),
            label: AppStrings.tr('profile_tab'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, lang, _) {
        return Scaffold(
          backgroundColor: const Color(0xFFF8FAFC),
          body: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : SafeArea(
                  child: IndexedStack(
                    index: _currentIndex,
                    children: [
                      StudentHomeTab(
                        profile: _profile,
                        isClass1: _isClass1,
                        textbooks: _textbooks,
                        homeworks: _homeworks,
                        messages: _messages,
                        todayAttendanceStatus: _todayAttendanceStatus,
                        todayAttendanceRemarks: _todayAttendanceRemarks,
                        attendancePercentage: _attendancePercentage,
                        onRefresh: _loadData,
                        onOpenDrawer: _openDrawerMenu,
                        onOpenMessages: _showMessagesDialog,
                        onNavigateToTab: (idx) => setState(() => _currentIndex = idx),
                      ),
                      StudentStudyTab(
                        profile: _profile,
                        studySubjects: _studySubjects,
                        homeworks: _homeworks,
                        practicals: _practicals,
                        routines: _routines,
                        examSchedules: _examSchedules,
                        textbooks: _textbooks,
                      ),
                      StudentAttendanceTab(
                        studentId: _profile?.id ?? widget.studentId,
                        studentName: _profile?.name ?? 'Tanvir Ahmed',
                      ),
                      StudentResultsTab(
                        profile: _profile,
                        studentId: _profile?.id ?? widget.studentId,
                        overallPercentage: _overallPercentage,
                        examTerms: _examTerms,
                        examSchedules: _examSchedules,
                      ),
                      StudentProfileTab(
                        profile: _profile,
                        profileHub: _profileHub,
                        studentId: _profile?.id ?? widget.studentId,
                        isClass1: _isClass1,
                        notices: _notices,
                        messages: _messages,
                        dueFeesAmount: _dueFeesAmount,
                        recentFees: _profileRecentFees,
                        onFeePaid: (due, history) {
                          setState(() {
                            _dueFeesAmount = due;
                            _profileRecentFees.insert(0, history);
                          });
                        },
                      ),
                    ],
                  ),
                ),
          bottomNavigationBar: _buildBottomNav(),
        );
      },
    );
  }
}
