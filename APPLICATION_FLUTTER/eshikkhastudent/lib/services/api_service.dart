import 'dart:convert';
import 'dart:io';
import '../models/academic_model.dart';
import '../models/attendance_model.dart';
import '../models/child_model.dart';
import '../models/textbook_model.dart';

class ApiService {
  static const String guardianBaseUrl = 'http://localhost:8000/api/guardian';
  static const String studentBaseUrl = 'http://localhost:8000/api/student';

  static String? currentAuthToken;
  static int currentStudentId = 2;
  static String? currentStudentGroup; // Default to Tanvir Ahmed (DRMC)
  static Map<String, dynamic>? currentStudentData;
  static String? registeredEmail;
  static String? registeredPassword;
  static Map<String, dynamic>? registeredStudentData;

  // Seeded Initial 4 Children Data for Guardian Dashboard:
  static final List<ChildSummary> _localChildren = [
    ChildSummary(
      studentId: 1,
      admissionNumber: 'RCS-ADM-2024-008',
      rollNumber: 5,
      relationship: 'father',
      photo: 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=150',
      name: 'ফারহান আহমেদ (Farhan Ahmed)',
      institutionName: 'রাজশাহী কলেজিয়েট স্কুল (RCS)',
      institutionType: 'school',
      institutionCode: 'RCS-303',
      classOrSemester: 'অষ্টম শ্রেণি (Class 8)',
      section: 'পদ্মা শাখা',
      todayAttendanceStatus: 'present',
      todayAttendanceTime: 'RFID Gate Punch: 07:50 AM',
      latestGpa: 4.85,
      latestGrade: 'A',
      examTitle: 'Half Yearly Examination 2026',
      dueFeesCount: 1,
      dueFeesAmount: 1200.0,
      newNoticesCount: 3,
      newMessagesCount: 1,
    ),
    ChildSummary(
      studentId: 2,
      admissionNumber: 'DRMC-ADM-2024-001',
      rollNumber: 1,
      relationship: 'father',
      photo: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150',
      name: 'তানভীর আহমেদ (Tanvir Ahmed)',
      institutionName: 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ (DRMC)',
      institutionType: 'college',
      institutionCode: 'DRMC-101',
      classOrSemester: 'একাদশ শ্রেণি (HSC Science 1st Year)',
      section: 'বিজ্ঞান-১ (Science A)',
      todayAttendanceStatus: 'present',
      todayAttendanceTime: 'RFID Gate Punch: 07:54 AM',
      latestGpa: 5.00,
      latestGrade: 'A+',
      examTitle: 'Model Test Examination 2026',
      dueFeesCount: 1,
      dueFeesAmount: 1800.0,
      newNoticesCount: 5,
      newMessagesCount: 2,
    ),
    ChildSummary(
      studentId: 3,
      admissionNumber: 'TMKM-ADM-2024-006',
      rollNumber: 3,
      relationship: 'father',
      photo: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150',
      name: 'হাফেজ আব্দুল্লাহ আল নোমান (Hafiz Abdullah)',
      institutionName: 'তা’মীরুল মিল্লাত কামিল মাদ্রাসা (TMKM)',
      institutionType: 'madrasah',
      institutionCode: 'TMKM-202',
      classOrSemester: 'দাখিল ৬ষ্ঠ শ্রেণি (Class 6)',
      section: 'শাখা আলিফ',
      todayAttendanceStatus: 'present',
      todayAttendanceTime: 'Biometric Punch: 07:45 AM',
      latestGpa: 5.00,
      latestGrade: 'A+',
      examTitle: 'First Term Assessment (Mumtaz)',
      dueFeesCount: 0,
      dueFeesAmount: 0.0,
      newNoticesCount: 2,
      newMessagesCount: 1,
    ),
    ChildSummary(
      studentId: 4,
      admissionNumber: 'SUST-ADM-2024-202',
      rollNumber: 12,
      relationship: 'father',
      photo: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150',
      name: 'সুমাইয়া আক্তার (Sumaiya Akter)',
      institutionName: 'শাহজালাল বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয় (SUST)',
      institutionType: 'university',
      institutionCode: 'SUST-404',
      classOrSemester: 'কম্পিউটার সায়েন্স ২য় সেমিস্টার (CSE 2nd Sem)',
      section: 'ব্যাচ ২৬ (Batch 26)',
      todayAttendanceStatus: 'present',
      todayAttendanceTime: 'Lab Terminal Login: 08:30 AM',
      latestGpa: 3.88,
      latestGrade: 'A+',
      examTitle: '1st Semester Final (CGPA 3.88/4.00)',
      dueFeesCount: 1,
      dueFeesAmount: 6500.0,
      newNoticesCount: 4,
      newMessagesCount: 1,
    ),
  ];

  // ==========================================
  // GUARDIAN API METHODS
  // ==========================================

  static Future<List<ChildSummary>> getChildren() async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.getUrl(Uri.parse('$guardianBaseUrl/children'));
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true && data['children'] != null) {
          final list = (data['children'] as List).map((i) => ChildSummary.fromJson(i)).toList();
          if (list.isNotEmpty) return list;
        }
      }
    } catch (_) {}
    return _localChildren;
  }

  static Future<Map<String, dynamic>?> lookupStudent(String studentId) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.postUrl(Uri.parse('$guardianBaseUrl/children/lookup'));
      request.headers.set('content-type', 'application/json');
      request.add(utf8.encode(jsonEncode({'student_id': studentId})));
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true) {
          return data['student'];
        }
      }
    } catch (_) {}

    final query = studentId.toUpperCase().trim();
    if (query.contains('RCS') || query.contains('008')) {
      return {
        'id': 101,
        'name': 'আরিয়ান আহমেদ (Ariyan Ahmed)',
        'admission_number': query,
        'roll_number': 8,
        'institution_name': 'রাজশাহী কলেজিয়েট স্কুল',
        'institution_type': 'school',
        'institution_code': 'RCS-303',
        'class_name': '৮ম শ্রেণি (Class 8)',
        'section_name': 'মেঘনা শাখা',
        'masked_guardian_phone': '01711***000',
      };
    } else if (query.contains('DRMC') || query.contains('101')) {
      return {
        'id': 102,
        'name': 'নাফিজ ইকবাল (Nafiz Iqbal)',
        'admission_number': query,
        'roll_number': 14,
        'institution_name': 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ',
        'institution_type': 'college',
        'institution_code': 'DRMC-101',
        'class_name': 'একাদশ শ্রেণি (HSC Science)',
        'section_name': 'পদ্মা শাখা',
        'masked_guardian_phone': '01711***000',
      };
    } else {
      return {
        'id': 103,
        'name': 'সাকিবুল হাসান (Sakibul Hasan)',
        'admission_number': query,
        'roll_number': 22,
        'institution_name': 'তা’মীরুল মিল্লাত কামিল মাদ্রাসা',
        'institution_type': 'madrasah',
        'institution_code': 'TMKM-202',
        'class_name': 'দাখিল ৬ষ্ঠ শ্রেণি (Class 6)',
        'section_name': 'শাখা বা',
        'masked_guardian_phone': '01711***000',
      };
    }
  }

  static Future<bool> requestLink(int studentId, String relationship) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.postUrl(Uri.parse('$guardianBaseUrl/children/request-link'));
      request.headers.set('content-type', 'application/json');
      request.add(utf8.encode(jsonEncode({
        'student_id': studentId,
        'relationship': relationship,
      })));
      final response = await request.close();
      if (response.statusCode == 200) {
        return true;
      }
    } catch (_) {}
    return true;
  }

  static Future<bool> verifyOtp(int studentId, String otp, Map<String, dynamic> studentData, String relationship) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.postUrl(Uri.parse('$guardianBaseUrl/children/verify-otp'));
      request.headers.set('content-type', 'application/json');
      request.add(utf8.encode(jsonEncode({
        'student_id': studentId,
        'otp': otp,
      })));
      await request.close();
    } catch (_) {}

    _localChildren.add(
      ChildSummary(
        studentId: studentId,
        admissionNumber: studentData['admission_number'] ?? 'NEW-ADM',
        rollNumber: studentData['roll_number'] ?? 10,
        relationship: relationship,
        photo: 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=150',
        name: studentData['name'] ?? 'নতুন সন্তান',
        institutionName: studentData['institution_name'] ?? 'শিক্ষা প্রতিষ্ঠান',
        institutionType: studentData['institution_type'] ?? 'school',
        institutionCode: studentData['institution_code'] ?? 'NEW',
        classOrSemester: studentData['class_name'] ?? 'শ্রেণি',
        section: studentData['section_name'] ?? 'শাখা ক',
        todayAttendanceStatus: 'present',
        todayAttendanceTime: 'Auto Verified: 08:00 AM',
        latestGpa: 5.0,
        latestGrade: 'A+',
        examTitle: 'Latest Term Exam',
        dueFeesCount: 0,
        dueFeesAmount: 0.0,
        newNoticesCount: 1,
        newMessagesCount: 1,
      ),
    );

    return true;
  }

  // ==========================================
  // STUDENT API METHODS (11 Core Features)
  // ==========================================

  /// Get Complete Student Portal Data (all 11 items bundled)
  static Future<Map<String, dynamic>> getStudentPortalData({int? studentId}) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/portal-data?student_id=$sid'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true) {
          return data;
        }
      }
    } catch (_) {}

    // Resilient Local Mock Bundle matching user's image & scenario
    return _buildLocalStudentPortalData();
  }

  /// Submit homework
  static Future<bool> submitHomework(int homeworkId) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/homework/$homeworkId/submit'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      return response.statusCode == 200;
    } catch (_) {
      return true; // Mock success
    }
  }

  /// Mark teacher message as read
  static Future<bool> markMessageAsRead(int messageId, {int? studentId}) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/messages/$messageId/read?student_id=$sid'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      return response.statusCode == 200;
    } catch (_) {
      return true;
    }
  }

  /// Get Comprehensive Attendance Analytics (6 Core Points)
  static Future<AttendanceAnalytics> getAttendanceAnalytics({int? studentId}) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/attendance-analytics?student_id=$sid'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true && data['data'] != null) {
          return AttendanceAnalytics.fromJson(data['data']);
        }
      }
    } catch (_) {}

    return _buildLocalAttendanceAnalytics();
  }

  /// Apply for student leave
  static Future<bool> applyLeave({
    int? studentId,
    required String leaveType,
    required String title,
    required String startDate,
    required String endDate,
    String? reason,
  }) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/leaves/apply?student_id=$sid'));
      request.headers.set('content-type', 'application/json');
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      request.add(utf8.encode(jsonEncode({
        'leave_type': leaveType,
        'title': title,
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason ?? '',
      })));
      final response = await request.close();
      return response.statusCode == 200;
    } catch (_) {
      return true;
    }
  }

  /// Get Academic Results covering all 10 items (Image-matched UI)
  static Future<AcademicDetails> getAcademicResults({int? studentId, int? examId, bool isGuardian = false}) async {
    final sid = studentId ?? currentStudentId;
    final url = isGuardian
        ? '$guardianBaseUrl/children/$sid/academic-results${examId != null ? '?exam_id=$examId' : ''}'
        : '$studentBaseUrl/academic-results?student_id=$sid${examId != null ? '&exam_id=$examId' : ''}';

    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.getUrl(Uri.parse(url));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true && data['data'] != null) {
          return AcademicDetails.fromJson(data['data']);
        }
      }
    } catch (_) {}

    return _buildLocalAcademicDetails(studentId: sid);
  }

  /// Get Secure 5-Button Navigation Hub Data (Home, Study, Attendance, Results, Profile)
  static Future<Map<String, dynamic>> getStudentNavigationHub({int? studentId}) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/navigation-hub?student_id=$sid'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true) {
          return data;
        }
      }
    } catch (_) {}

    return _buildLocalNavigationHub(studentId: sid);
  }

  /// Get Dedicated Study Hub Data (Subjects, Homework, Practicals, Routine)
  static Future<Map<String, dynamic>> getStudentStudyData({int? studentId}) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/study?student_id=$sid'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true) {
          return data;
        }
      }
    } catch (_) {}

    return _buildLocalStudyData();
  }

  /// Get 10-Item Profile Hub Data (Personal, Student ID, Institution, Guardian, Fees, Notices, Messages, Settings, Help, Logout)
  static Future<Map<String, dynamic>> getStudentProfileHub({int? studentId}) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/profile-hub?student_id=$sid'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true) {
          return data;
        }
      }
    } catch (_) {}

    return _buildLocalProfileHub(studentId: sid);
  }

  /// Resolve numeric class level (-2 to 16)
  static int resolveClassLevel(String? className) {
    if (className == null) return 1;
    final lower = className.toLowerCase();
    if (lower.contains('play') || className.contains('প্লে')) return -2;
    if (lower.contains('nursery') || className.contains('নার্সারি')) return -1;
    if (lower.contains('kg') || className.contains('কেজি')) return 0;
    if (lower.contains('diploma') || className.contains('ডিপ্লোমা')) return 13;
    if (lower.contains('honours') || className.contains('অনার্স')) return 14;
    if (lower.contains('degree') || className.contains('ডিগ্রি')) return 15;
    if (lower.contains('master') || className.contains('মাস্টার্স')) return 16;
    if (lower.contains('12') || className.contains('দ্বাদশ')) return 12;
    if (lower.contains('11') || className.contains('একাদশ') || lower.contains('hsc')) return 11;
    if (lower.contains('10') || className.contains('দশম') || lower.contains('ssc')) return 10;
    if (lower.contains('9') || className.contains('নবম')) return 9;
    if (lower.contains('8') || className.contains('অষ্টম') || lower.contains('jsc')) return 8;
    if (lower.contains('7') || className.contains('সপ্তম')) return 7;
    if (lower.contains('6') || className.contains('ষষ্ঠ')) return 6;
    if (lower.contains('5') || className.contains('পঞ্চম') || lower.contains('psc')) return 5;
    if (lower.contains('4') || className.contains('চতুর্থ')) return 4;
    if (lower.contains('3') || className.contains('তৃতীয়')) return 3;
    if (lower.contains('2') || className.contains('দ্বিতীয়')) return 2;
    if (lower.contains('class 1') || lower == '1' || className.contains('প্রথম') || className.contains('১ম')) return 1;
    return 1;
  }

  /// Get Prescribed Textbooks for a student / class (with optional group for Class 9/10)
  static Future<List<TextbookModel>> getTextbooks({
    String? className,
    int? classLevel,
    String? group,
    int? studentId,
  }) async {
    final level = classLevel ?? resolveClassLevel(className);
    final sid = studentId ?? currentStudentId;
    final grp = group ?? currentStudentGroup ?? (currentStudentData?['group'] as String?);

    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final grpParam = (grp != null && grp.isNotEmpty) ? '&group=$grp' : '';
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/textbooks?class_level=$level&student_id=$sid$grpParam'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true && data['books'] is List) {
          final list = (data['books'] as List).map((b) => TextbookModel.fromJson(b)).toList();
          if (list.isNotEmpty) return list;
        }
      }
    } catch (_) {}

    return _buildLocalTextbooks(level, group: grp);
  }

  static List<TextbookModel> _buildLocalTextbooks(int level, {String? group}) {
    final Map<int, List<Map<String, dynamic>>> catalog = {
      // Play Group (প্লে গ্রুপ)
      -2: [
        {'id': 201, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'বাংলা বর্ণমালা পরিচিতি', 'english_title': 'Bangla Alphabet Introduction', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#059669', 'icon': 'menu_book_rounded', 'total_pages': 48},
        {'id': 202, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'English Alphabet (A–Z)', 'english_title': 'English Alphabet (A–Z)', 'subject': 'English', 'group': 'general', 'cover_color_start': '#3B82F6', 'cover_color_end': '#1D4ED8', 'icon': 'school_rounded', 'total_pages': 48},
        {'id': 203, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'সংখ্যা পরিচিতি (১–১০)', 'english_title': 'Number Introduction (1-10)', 'subject': 'Mathematics', 'group': 'general', 'cover_color_start': '#F59E0B', 'cover_color_end': '#D97706', 'icon': 'calculate_rounded', 'total_pages': 40},
        {'id': 204, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'ছড়ার বই', 'english_title': 'Rhymes Book', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#EC4899', 'cover_color_end': '#BE185D', 'icon': 'music_note_rounded', 'total_pages': 44},
        {'id': 205, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'ছবি দেখে শেখা', 'english_title': 'Learning from Pictures', 'subject': 'General Knowledge', 'group': 'general', 'cover_color_start': '#8B5CF6', 'cover_color_end': '#6D28D9', 'icon': 'image_rounded', 'total_pages': 40},
        {'id': 206, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'রং পরিচিতি', 'english_title': 'Color Introduction', 'subject': 'Art', 'group': 'general', 'cover_color_start': '#EF4444', 'cover_color_end': '#DC2626', 'icon': 'palette_rounded', 'total_pages': 36},
        {'id': 207, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'আঁকাআঁকি ও রং করা', 'english_title': 'Drawing & Coloring', 'subject': 'Art', 'group': 'general', 'cover_color_start': '#06B6D4', 'cover_color_end': '#0891B2', 'icon': 'brush_rounded', 'total_pages': 48},
        {'id': 208, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'ফল ও ফুল পরিচিতি', 'english_title': 'Fruits & Flowers', 'subject': 'Science', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#047857', 'icon': 'local_florist_rounded', 'total_pages': 40},
        {'id': 209, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'পশু-পাখি পরিচিতি', 'english_title': 'Animals & Birds', 'subject': 'Science', 'group': 'general', 'cover_color_start': '#F97316', 'cover_color_end': '#C2410C', 'icon': 'pets_rounded', 'total_pages': 40},
        {'id': 210, 'class_level': -2, 'class_name': 'Play Group', 'class_name_bn': 'প্লে গ্রুপ', 'title': 'ভালো আচরণ ও নৈতিক শিক্ষা', 'english_title': 'Good Manners & Moral Education', 'subject': 'Moral Education', 'group': 'general', 'cover_color_start': '#14B8A6', 'cover_color_end': '#0F766E', 'icon': 'sentiment_satisfied_alt_rounded', 'total_pages': 36},
      ],
      // Nursery (নার্সারি)
      -1: [
        {'id': 301, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'বাংলা বর্ণমালা', 'english_title': 'Bangla Alphabet', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#059669', 'icon': 'menu_book_rounded', 'total_pages': 56},
        {'id': 302, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'বাংলা হাতের লেখা', 'english_title': 'Bangla Handwriting', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'edit_note_rounded', 'total_pages': 48},
        {'id': 303, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'English Alphabet', 'english_title': 'English Alphabet', 'subject': 'English', 'group': 'general', 'cover_color_start': '#3B82F6', 'cover_color_end': '#1D4ED8', 'icon': 'school_rounded', 'total_pages': 56},
        {'id': 304, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'English Handwriting', 'english_title': 'English Handwriting', 'subject': 'English', 'group': 'general', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'draw_rounded', 'total_pages': 48},
        {'id': 305, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'English Rhymes', 'english_title': 'English Rhymes', 'subject': 'English', 'group': 'general', 'cover_color_start': '#6366F1', 'cover_color_end': '#4338CA', 'icon': 'auto_stories_rounded', 'total_pages': 52},
        {'id': 306, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'প্রাথমিক গণিত', 'english_title': 'Elementary Mathematics', 'subject': 'Mathematics', 'group': 'general', 'cover_color_start': '#F59E0B', 'cover_color_end': '#D97706', 'icon': 'calculate_rounded', 'total_pages': 64},
        {'id': 307, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'সংখ্যা লেখা ও গণনা', 'english_title': 'Writing Numbers & Counting', 'subject': 'Mathematics', 'group': 'general', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'format_list_numbered_rounded', 'total_pages': 48},
        {'id': 308, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'ছবি আঁকা ও রং করা', 'english_title': 'Drawing & Coloring', 'subject': 'Art', 'group': 'general', 'cover_color_start': '#EC4899', 'cover_color_end': '#BE185D', 'icon': 'palette_rounded', 'total_pages': 52},
        {'id': 309, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'ছড়া ও কবিতা', 'english_title': 'Rhymes & Poems', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#A855F7', 'cover_color_end': '#7E22CE', 'icon': 'library_music_rounded', 'pages': 50},
        {'id': 310, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'সাধারণ জ্ঞান', 'english_title': 'General Knowledge', 'subject': 'General Knowledge', 'group': 'general', 'cover_color_start': '#06B6D4', 'cover_color_end': '#0891B2', 'icon': 'psychology_rounded', 'total_pages': 46},
        {'id': 311, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'পরিবেশ পরিচিতি', 'english_title': 'Environment Introduction', 'subject': 'Science', 'group': 'general', 'cover_color_start': '#84CC16', 'cover_color_end': '#4D7C0F', 'icon': 'eco_rounded', 'total_pages': 48},
        {'id': 312, 'class_level': -1, 'class_name': 'Nursery', 'class_name_bn': 'নার্সারি', 'title': 'নৈতিক শিক্ষা', 'english_title': 'Moral Education', 'subject': 'Moral Education', 'group': 'general', 'cover_color_start': '#14B8A6', 'cover_color_end': '#0F766E', 'icon': 'favorite_rounded', 'total_pages': 44},
      ],
      // KG (কেজি)
      0: [
        {'id': 401, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'বাংলা পাঠ', 'english_title': 'Bangla Reading', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#059669', 'icon': 'menu_book_rounded', 'total_pages': 64},
        {'id': 402, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'বাংলা ব্যাকরণ প্রাথমিক', 'english_title': 'Elementary Bangla Grammar', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'spellcheck_rounded', 'total_pages': 56},
        {'id': 403, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'বাংলা হাতের লেখা', 'english_title': 'Bangla Handwriting', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#0D9488', 'cover_color_end': '#0F766E', 'icon': 'edit_note_rounded', 'total_pages': 50},
        {'id': 404, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'English Reading', 'english_title': 'English Reading', 'subject': 'English', 'group': 'general', 'cover_color_start': '#3B82F6', 'cover_color_end': '#1D4ED8', 'icon': 'school_rounded', 'total_pages': 64},
        {'id': 405, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'English Grammar', 'english_title': 'English Grammar', 'subject': 'English', 'group': 'general', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'fact_check_rounded', 'total_pages': 56},
        {'id': 406, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'English Handwriting', 'english_title': 'English Handwriting', 'subject': 'English', 'group': 'general', 'cover_color_start': '#1D4ED8', 'cover_color_end': '#1E3A8A', 'icon': 'draw_rounded', 'total_pages': 50},
        {'id': 407, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'English Rhymes', 'english_title': 'English Rhymes', 'subject': 'English', 'group': 'general', 'cover_color_start': '#6366F1', 'cover_color_end': '#4338CA', 'icon': 'auto_stories_rounded', 'total_pages': 56},
        {'id': 408, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'গণিত', 'english_title': 'Mathematics', 'subject': 'Mathematics', 'group': 'general', 'cover_color_start': '#F59E0B', 'cover_color_end': '#D97706', 'icon': 'calculate_rounded', 'total_pages': 72},
        {'id': 409, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'যোগ ও বিয়োগ', 'english_title': 'Addition & Subtraction', 'subject': 'Mathematics', 'group': 'general', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'functions_rounded', 'total_pages': 52},
        {'id': 410, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'প্রাথমিক বিজ্ঞান', 'english_title': 'Elementary Science', 'subject': 'Science', 'group': 'general', 'cover_color_start': '#06B6D4', 'cover_color_end': '#0891B2', 'icon': 'science_rounded', 'total_pages': 58},
        {'id': 411, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'সাধারণ জ্ঞান', 'english_title': 'General Knowledge', 'subject': 'General Knowledge', 'group': 'general', 'cover_color_start': '#8B5CF6', 'cover_color_end': '#6D28D9', 'icon': 'lightbulb_rounded', 'total_pages': 52},
        {'id': 412, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'বাংলাদেশ পরিচিতি', 'english_title': 'Introduction to Bangladesh', 'subject': 'BGS', 'group': 'general', 'cover_color_start': '#0284C7', 'cover_color_end': '#0369A1', 'icon': 'public_rounded', 'total_pages': 54},
        {'id': 413, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'পরিবেশ পরিচিতি', 'english_title': 'Environment Introduction', 'subject': 'Science', 'group': 'general', 'cover_color_start': '#84CC16', 'cover_color_end': '#4D7C0F', 'icon': 'nature_people_rounded', 'total_pages': 50},
        {'id': 414, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'ধর্ম ও নৈতিক শিক্ষা', 'english_title': 'Religion & Moral Education', 'subject': 'Religion', 'group': 'general', 'cover_color_start': '#14B8A6', 'cover_color_end': '#0F766E', 'icon': 'menu_book_rounded', 'total_pages': 56},
        {'id': 415, 'class_level': 0, 'class_name': 'KG', 'class_name_bn': 'কেজি', 'title': 'ছবি আঁকা ও হাতের কাজ', 'english_title': 'Drawing & Craft', 'subject': 'Art', 'group': 'general', 'cover_color_start': '#EC4899', 'cover_color_end': '#BE185D', 'icon': 'palette_rounded', 'total_pages': 56},
      ],
      // Class 1 (প্রথম শ্রেণি)
      1: [
        {
          'id': 101,
          'class_level': 1,
          'class_name': 'Class 1',
          'class_name_bn': 'প্রথম শ্রেণি',
          'title': 'আমার বাংলা বই',
          'english_title': 'Amar Bangla Boi',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 96,
        },
        {
          'id': 102,
          'class_level': 1,
          'class_name': 'Class 1',
          'class_name_bn': 'প্রথম শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today (Class 1)',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 88,
        },
        {
          'id': 103,
          'class_level': 1,
          'class_name': 'Class 1',
          'class_name_bn': 'প্রথম শ্রেণি',
          'title': 'প্রাথমিক গণিত',
          'english_title': 'Elementary Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 104,
        },
      ],
      // Class 2 (দ্বিতীয় শ্রেণি)
      2: [
        {
          'id': 201,
          'class_level': 2,
          'class_name': 'Class 2',
          'class_name_bn': 'দ্বিতীয় শ্রেণি',
          'title': 'আমার বাংলা বই',
          'english_title': 'Amar Bangla Boi',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 108,
        },
        {
          'id': 202,
          'class_level': 2,
          'class_name': 'Class 2',
          'class_name_bn': 'দ্বিতীয় শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today (Class 2)',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 96,
        },
        {
          'id': 203,
          'class_level': 2,
          'class_name': 'Class 2',
          'class_name_bn': 'দ্বিতীয় শ্রেণি',
          'title': 'প্রাথমিক গণিত',
          'english_title': 'Elementary Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 112,
        },
      ],
      // Class 3 (তৃতীয় শ্রেণি)
      3: [
        {
          'id': 301,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'আমার বাংলা বই',
          'english_title': 'Amar Bangla Boi',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 120,
        },
        {
          'id': 302,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 110,
        },
        {
          'id': 303,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'প্রাথমিক গণিত',
          'english_title': 'Elementary Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 128,
        },
        {
          'id': 304,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'প্রাথমিক বিজ্ঞান',
          'english_title': 'Elementary Science',
          'subject': 'Science',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'science_rounded',
          'total_pages': 104,
        },
        {
          'id': 305,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'public_rounded',
          'total_pages': 96,
        },
        {
          'id': 306,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'ইসলাম ও নৈতিক শিক্ষা',
          'english_title': 'Islam & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#14B8A6',
          'cover_color_end': '#0F766E',
          'icon': 'auto_stories_rounded',
          'total_pages': 92,
        },
        {
          'id': 307,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'হিন্দুধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Hindu Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#EA580C',
          'icon': 'auto_stories_rounded',
          'total_pages': 90,
        },
        {
          'id': 308,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'বৌদ্ধধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Buddhist Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#EAB308',
          'cover_color_end': '#CA8A04',
          'icon': 'auto_stories_rounded',
          'total_pages': 88,
        },
        {
          'id': 309,
          'class_level': 3,
          'class_name': 'Class 3',
          'class_name_bn': 'তৃতীয় শ্রেণি',
          'title': 'খ্রিষ্টধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Christian Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4F46E5',
          'icon': 'auto_stories_rounded',
          'total_pages': 88,
        },
      ],
      // Class 4 (চতুর্থ শ্রেণি)
      4: [
        {
          'id': 401,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'আমার বাংলা বই',
          'english_title': 'Amar Bangla Boi',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 128,
        },
        {
          'id': 402,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 116,
        },
        {
          'id': 403,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'প্রাথমিক গণিত',
          'english_title': 'Elementary Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 136,
        },
        {
          'id': 404,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'প্রাথমিক বিজ্ঞান',
          'english_title': 'Elementary Science',
          'subject': 'Science',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'science_rounded',
          'total_pages': 112,
        },
        {
          'id': 405,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'public_rounded',
          'total_pages': 104,
        },
        {
          'id': 406,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'ইসলাম ও নৈতিক শিক্ষা',
          'english_title': 'Islam & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#14B8A6',
          'cover_color_end': '#0F766E',
          'icon': 'auto_stories_rounded',
          'total_pages': 98,
        },
        {
          'id': 407,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'হিন্দুধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Hindu Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#EA580C',
          'icon': 'auto_stories_rounded',
          'total_pages': 94,
        },
        {
          'id': 408,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'বৌদ্ধধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Buddhist Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#EAB308',
          'cover_color_end': '#CA8A04',
          'icon': 'auto_stories_rounded',
          'total_pages': 92,
        },
        {
          'id': 409,
          'class_level': 4,
          'class_name': 'Class 4',
          'class_name_bn': 'চতুর্থ শ্রেণি',
          'title': 'খ্রিষ্টধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Christian Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4F46E5',
          'icon': 'auto_stories_rounded',
          'total_pages': 92,
        },
      ],
      // Class 5 (পঞ্চম শ্রেণি)
      5: [
        {
          'id': 501,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'আমার বাংলা বই',
          'english_title': 'Amar Bangla Boi',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 140,
        },
        {
          'id': 502,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 124,
        },
        {
          'id': 503,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'প্রাথমিক গণিত',
          'english_title': 'Elementary Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 144,
        },
        {
          'id': 504,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'প্রাথমিক বিজ্ঞান',
          'english_title': 'Elementary Science',
          'subject': 'Science',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'science_rounded',
          'total_pages': 120,
        },
        {
          'id': 505,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'public_rounded',
          'total_pages': 116,
        },
        {
          'id': 506,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'ইসলাম ও নৈতিক শিক্ষা',
          'english_title': 'Islam & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#14B8A6',
          'cover_color_end': '#0F766E',
          'icon': 'auto_stories_rounded',
          'total_pages': 108,
        },
        {
          'id': 507,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'হিন্দুধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Hindu Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#EA580C',
          'icon': 'auto_stories_rounded',
          'total_pages': 102,
        },
        {
          'id': 508,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'বৌদ্ধধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Buddhist Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#EAB308',
          'cover_color_end': '#CA8A04',
          'icon': 'auto_stories_rounded',
          'total_pages': 98,
        },
        {
          'id': 509,
          'class_level': 5,
          'class_name': 'Class 5',
          'class_name_bn': 'পঞ্চম শ্রেণি',
          'title': 'খ্রিষ্টধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Christian Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4F46E5',
          'icon': 'auto_stories_rounded',
          'total_pages': 98,
        },
      ],
      // Class 6 (ষষ্ঠ শ্রেণি)
      6: [
        {
          'id': 601,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'চারুপাঠ',
          'english_title': 'Charupath',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 136,
        },
        {
          'id': 602,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'আনন্দপাঠ',
          'english_title': 'Anandapath (Rapid Reader)',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'auto_stories_rounded',
          'total_pages': 96,
        },
        {
          'id': 603,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'বাংলা ব্যাকরণ ও নির্মিতি',
          'english_title': 'Bangla Grammar & Composition',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#0F766E',
          'icon': 'edit_note_rounded',
          'total_pages': 112,
        },
        {
          'id': 604,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 140,
        },
        {
          'id': 605,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'English Grammar and Composition',
          'english_title': 'English Grammar & Composition',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'spellcheck_rounded',
          'total_pages': 128,
        },
        {
          'id': 606,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'গণিত',
          'english_title': 'Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 164,
        },
        {
          'id': 607,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'বিজ্ঞান',
          'english_title': 'General Science',
          'subject': 'Science',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'science_rounded',
          'total_pages': 148,
        },
        {
          'id': 608,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'তথ্য ও যোগাযোগ প্রযুক্তি',
          'english_title': 'Information & Communication Technology',
          'subject': 'ICT',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'computer_rounded',
          'total_pages': 104,
        },
        {
          'id': 609,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#0284C7',
          'cover_color_end': '#0369A1',
          'icon': 'public_rounded',
          'total_pages': 134,
        },
        {
          'id': 610,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'শারীরিক শিক্ষা ও স্বাস্থ্য',
          'english_title': 'Physical Education & Health',
          'subject': 'Physical Ed',
          'group': 'general',
          'cover_color_start': '#EF4444',
          'cover_color_end': '#DC2626',
          'icon': 'sports_soccer_rounded',
          'total_pages': 92,
        },
        {
          'id': 611,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'কর্ম ও জীবনমুখী শিক্ষা',
          'english_title': 'Work & Life Oriented Education',
          'subject': 'Life Skills',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#EA580C',
          'icon': 'work_outline_rounded',
          'total_pages': 88,
        },
        {
          'id': 612,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'কৃষিশিক্ষা',
          'english_title': 'Agricultural Studies',
          'subject': 'Agriculture',
          'group': 'general',
          'cover_color_start': '#84CC16',
          'cover_color_end': '#65A30D',
          'icon': 'eco_rounded',
          'total_pages': 108,
        },
        {
          'id': 613,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'গার্হস্থ্য বিজ্ঞান',
          'english_title': 'Home Science',
          'subject': 'Home Science',
          'group': 'general',
          'cover_color_start': '#EC4899',
          'cover_color_end': '#BE185D',
          'icon': 'home_work_rounded',
          'total_pages': 104,
        },
        {
          'id': 614,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'চারু ও কারুকলা',
          'english_title': 'Arts & Crafts',
          'subject': 'Arts',
          'group': 'general',
          'cover_color_start': '#A855F7',
          'cover_color_end': '#7E22CE',
          'icon': 'palette_rounded',
          'total_pages': 84,
        },
        {
          'id': 615,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'ইসলাম শিক্ষা',
          'english_title': 'Islam Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#047857',
          'icon': 'menu_book_rounded',
          'total_pages': 116,
        },
        {
          'id': 616,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'হিন্দুধর্ম শিক্ষা',
          'english_title': 'Hinduism Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#C2410C',
          'icon': 'menu_book_rounded',
          'total_pages': 110,
        },
        {
          'id': 617,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'বৌদ্ধধর্ম শিক্ষা',
          'english_title': 'Buddhism Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#EAB308',
          'cover_color_end': '#A16207',
          'icon': 'menu_book_rounded',
          'total_pages': 106,
        },
        {
          'id': 618,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'খ্রিষ্টধর্ম শিক্ষা',
          'english_title': 'Christianity Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4338CA',
          'icon': 'menu_book_rounded',
          'total_pages': 106,
        },
        {
          'id': 619,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'সচিত্র আরবি পাঠ',
          'english_title': 'Illustrated Arabic Reader',
          'subject': 'Arabic',
          'group': 'general',
          'cover_color_start': '#14B8A6',
          'cover_color_end': '#0D9488',
          'icon': 'translate_rounded',
          'total_pages': 96,
        },
        {
          'id': 620,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'সংস্কৃত',
          'english_title': 'Sanskrit Studies',
          'subject': 'Classical',
          'group': 'general',
          'cover_color_start': '#78716C',
          'cover_color_end': '#57534E',
          'icon': 'translate_rounded',
          'total_pages': 90,
        },
        {
          'id': 621,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'পালি',
          'english_title': 'Pali Studies',
          'subject': 'Classical',
          'group': 'general',
          'cover_color_start': '#A3E635',
          'cover_color_end': '#65A30D',
          'icon': 'translate_rounded',
          'total_pages': 88,
        },
        {
          'id': 622,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'সংগীত',
          'english_title': 'Music',
          'subject': 'Music',
          'group': 'general',
          'cover_color_start': '#F43F5E',
          'cover_color_end': '#BE123C',
          'icon': 'music_note_rounded',
          'total_pages': 80,
        },
        {
          'id': 623,
          'class_level': 6,
          'class_name': 'Class 6',
          'class_name_bn': 'ষষ্ঠ শ্রেণি',
          'title': 'ক্ষুদ্র নৃগোষ্ঠীর ভাষা ও সংস্কৃতি',
          'english_title': 'Indigenous Language & Culture',
          'subject': 'Culture',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'groups_rounded',
          'total_pages': 94,
        },
      ],
      // Class 7 (সপ্তম শ্রেণি)
      7: [
        {
          'id': 701,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'সপ্তবর্ণা',
          'english_title': 'Saptabarna',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 140,
        },
        {
          'id': 702,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'আনন্দপাঠ',
          'english_title': 'Anandapath (Rapid Reader)',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'auto_stories_rounded',
          'total_pages': 100,
        },
        {
          'id': 703,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'বাংলা ব্যাকরণ ও নির্মিতি',
          'english_title': 'Bangla Grammar & Composition',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#0F766E',
          'icon': 'edit_note_rounded',
          'total_pages': 116,
        },
        {
          'id': 704,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 144,
        },
        {
          'id': 705,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'English Grammar and Composition',
          'english_title': 'English Grammar & Composition',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'spellcheck_rounded',
          'total_pages': 132,
        },
        {
          'id': 706,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'গণিত',
          'english_title': 'Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 168,
        },
        {
          'id': 707,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'বিজ্ঞান',
          'english_title': 'General Science',
          'subject': 'Science',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'science_rounded',
          'total_pages': 152,
        },
        {
          'id': 708,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'তথ্য ও যোগাযোগ প্রযুক্তি',
          'english_title': 'Information & Communication Technology',
          'subject': 'ICT',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'computer_rounded',
          'total_pages': 108,
        },
        {
          'id': 709,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#0284C7',
          'cover_color_end': '#0369A1',
          'icon': 'public_rounded',
          'total_pages': 138,
        },
        {
          'id': 710,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'শারীরিক শিক্ষা ও স্বাস্থ্য',
          'english_title': 'Physical Education & Health',
          'subject': 'Physical Ed',
          'group': 'general',
          'cover_color_start': '#EF4444',
          'cover_color_end': '#DC2626',
          'icon': 'sports_soccer_rounded',
          'total_pages': 96,
        },
        {
          'id': 711,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'কর্ম ও জীবনমুখী শিক্ষা',
          'english_title': 'Work & Life Oriented Education',
          'subject': 'Life Skills',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#EA580C',
          'icon': 'work_outline_rounded',
          'total_pages': 92,
        },
        {
          'id': 712,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'কৃষিশিক্ষা',
          'english_title': 'Agricultural Studies',
          'subject': 'Agriculture',
          'group': 'general',
          'cover_color_start': '#84CC16',
          'cover_color_end': '#65A30D',
          'icon': 'eco_rounded',
          'total_pages': 112,
        },
        {
          'id': 713,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'গার্হস্থ্য বিজ্ঞান',
          'english_title': 'Home Science',
          'subject': 'Home Science',
          'group': 'general',
          'cover_color_start': '#EC4899',
          'cover_color_end': '#BE185D',
          'icon': 'home_work_rounded',
          'total_pages': 108,
        },
        {
          'id': 714,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'চারু ও কারুকলা',
          'english_title': 'Arts & Crafts',
          'subject': 'Arts',
          'group': 'general',
          'cover_color_start': '#A855F7',
          'cover_color_end': '#7E22CE',
          'icon': 'palette_rounded',
          'total_pages': 88,
        },
        {
          'id': 715,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'ইসলাম শিক্ষা',
          'english_title': 'Islam Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#047857',
          'icon': 'menu_book_rounded',
          'total_pages': 120,
        },
        {
          'id': 716,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'হিন্দুধর্ম শিক্ষা',
          'english_title': 'Hinduism Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#C2410C',
          'icon': 'menu_book_rounded',
          'total_pages': 114,
        },
        {
          'id': 717,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'বৌদ্ধধর্ম শিক্ষা',
          'english_title': 'Buddhism Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#EAB308',
          'cover_color_end': '#A16207',
          'icon': 'menu_book_rounded',
          'total_pages': 110,
        },
        {
          'id': 718,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'খ্রিষ্টধর্ম শিক্ষা',
          'english_title': 'Christianity Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4338CA',
          'icon': 'menu_book_rounded',
          'total_pages': 110,
        },
        {
          'id': 719,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'সচিত্র আরবি পাঠ',
          'english_title': 'Illustrated Arabic Reader',
          'subject': 'Arabic',
          'group': 'general',
          'cover_color_start': '#14B8A6',
          'cover_color_end': '#0D9488',
          'icon': 'translate_rounded',
          'total_pages': 100,
        },
        {
          'id': 720,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'সংস্কৃত',
          'english_title': 'Sanskrit Studies',
          'subject': 'Classical',
          'group': 'general',
          'cover_color_start': '#78716C',
          'cover_color_end': '#57534E',
          'icon': 'translate_rounded',
          'total_pages': 94,
        },
        {
          'id': 721,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'পালি',
          'english_title': 'Pali Studies',
          'subject': 'Classical',
          'group': 'general',
          'cover_color_start': '#A3E635',
          'cover_color_end': '#65A30D',
          'icon': 'translate_rounded',
          'total_pages': 92,
        },
        {
          'id': 722,
          'class_level': 7,
          'class_name': 'Class 7',
          'class_name_bn': 'সপ্তম শ্রেণি',
          'title': 'সংগীত',
          'english_title': 'Music',
          'subject': 'Music',
          'group': 'general',
          'cover_color_start': '#F43F5E',
          'cover_color_end': '#BE123C',
          'icon': 'music_note_rounded',
          'total_pages': 84,
        },
      ],
      // Class 8 (অষ্টম শ্রেণি)
      8: [
        {
          'id': 801,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'সাহিত্য-কণিকা',
          'english_title': 'Sahitya-Kanika',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 148,
        },
        {
          'id': 802,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'আনন্দপাঠ',
          'english_title': 'Anandapath (Rapid Reader)',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'auto_stories_rounded',
          'total_pages': 104,
        },
        {
          'id': 803,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'বাংলা ব্যাকরণ ও নির্মিতি',
          'english_title': 'Bangla Grammar & Composition',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#0F766E',
          'icon': 'edit_note_rounded',
          'total_pages': 120,
        },
        {
          'id': 804,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 148,
        },
        {
          'id': 805,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'English Grammar and Composition',
          'english_title': 'English Grammar & Composition',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'spellcheck_rounded',
          'total_pages': 136,
        },
        {
          'id': 806,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'গণিত',
          'english_title': 'Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 176,
        },
        {
          'id': 807,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'বিজ্ঞান',
          'english_title': 'General Science',
          'subject': 'Science',
          'group': 'general',
          'cover_color_start': '#8B5CF6',
          'cover_color_end': '#6D28D9',
          'icon': 'science_rounded',
          'total_pages': 158,
        },
        {
          'id': 808,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'তথ্য ও যোগাযোগ প্রযুক্তি',
          'english_title': 'Information & Communication Technology',
          'subject': 'ICT',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'computer_rounded',
          'total_pages': 112,
        },
        {
          'id': 809,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#0284C7',
          'cover_color_end': '#0369A1',
          'icon': 'public_rounded',
          'total_pages': 142,
        },
        {
          'id': 810,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'শারীরিক শিক্ষা ও স্বাস্থ্য',
          'english_title': 'Physical Education & Health',
          'subject': 'Physical Ed',
          'group': 'general',
          'cover_color_start': '#EF4444',
          'cover_color_end': '#DC2626',
          'icon': 'sports_soccer_rounded',
          'total_pages': 100,
        },
        {
          'id': 811,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'কর্ম ও জীবনমুখী শিক্ষা',
          'english_title': 'Work & Life Oriented Education',
          'subject': 'Life Skills',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#EA580C',
          'icon': 'work_outline_rounded',
          'total_pages': 96,
        },
        {
          'id': 812,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'কৃষিশিক্ষা',
          'english_title': 'Agricultural Studies',
          'subject': 'Agriculture',
          'group': 'general',
          'cover_color_start': '#84CC16',
          'cover_color_end': '#65A30D',
          'icon': 'eco_rounded',
          'total_pages': 116,
        },
        {
          'id': 813,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'গার্হস্থ্য বিজ্ঞান',
          'english_title': 'Home Science',
          'subject': 'Home Science',
          'group': 'general',
          'cover_color_start': '#EC4899',
          'cover_color_end': '#BE185D',
          'icon': 'home_work_rounded',
          'total_pages': 112,
        },
        {
          'id': 814,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'চারু ও কারুকলা',
          'english_title': 'Arts & Crafts',
          'subject': 'Arts',
          'group': 'general',
          'cover_color_start': '#A855F7',
          'cover_color_end': '#7E22CE',
          'icon': 'palette_rounded',
          'total_pages': 92,
        },
        {
          'id': 815,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'ইসলাম শিক্ষা',
          'english_title': 'Islam Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#047857',
          'icon': 'menu_book_rounded',
          'total_pages': 124,
        },
        {
          'id': 816,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'হিন্দুধর্ম শিক্ষা',
          'english_title': 'Hinduism Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#C2410C',
          'icon': 'menu_book_rounded',
          'total_pages': 118,
        },
        {
          'id': 817,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'বৌদ্ধধর্ম শিক্ষা',
          'english_title': 'Buddhism Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#EAB308',
          'cover_color_end': '#A16207',
          'icon': 'menu_book_rounded',
          'total_pages': 114,
        },
        {
          'id': 818,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'খ্রিষ্টধর্ম শিক্ষা',
          'english_title': 'Christianity Studies',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4338CA',
          'icon': 'menu_book_rounded',
          'total_pages': 114,
        },
        {
          'id': 819,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'সচিত্র আরবি পাঠ',
          'english_title': 'Illustrated Arabic Reader',
          'subject': 'Arabic',
          'group': 'general',
          'cover_color_start': '#14B8A6',
          'cover_color_end': '#0D9488',
          'icon': 'translate_rounded',
          'total_pages': 104,
        },
        {
          'id': 820,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'সংস্কৃত',
          'english_title': 'Sanskrit Studies',
          'subject': 'Classical',
          'group': 'general',
          'cover_color_start': '#78716C',
          'cover_color_end': '#57534E',
          'icon': 'translate_rounded',
          'total_pages': 98,
        },
        {
          'id': 821,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'পালি',
          'english_title': 'Pali Studies',
          'subject': 'Classical',
          'group': 'general',
          'cover_color_start': '#A3E635',
          'cover_color_end': '#65A30D',
          'icon': 'translate_rounded',
          'total_pages': 96,
        },
        {
          'id': 822,
          'class_level': 8,
          'class_name': 'Class 8',
          'class_name_bn': 'অষ্টম শ্রেণি',
          'title': 'সংগীত',
          'english_title': 'Music',
          'subject': 'Music',
          'group': 'general',
          'cover_color_start': '#F43F5E',
          'cover_color_end': '#BE123C',
          'icon': 'music_note_rounded',
          'total_pages': 88,
        },
      ],
      // Class 9 (নবম শ্রেণি)
      9: [
        {
          'id': 901,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'বাংলা সাহিত্য',
          'english_title': 'Bangla Literature',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 180,
        },
        {
          'id': 902,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'বাংলা সহপাঠ',
          'english_title': 'Bangla Companion',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'auto_stories_rounded',
          'total_pages': 110,
        },
        {
          'id': 903,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'বাংলা ভাষার ব্যাকরণ ও নির্মিতি',
          'english_title': 'Bangla Grammar & Composition',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#0F766E',
          'icon': 'edit_note_rounded',
          'total_pages': 140,
        },
        {
          'id': 904,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today (SSC)',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 160,
        },
        {
          'id': 905,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'English Grammar and Composition',
          'english_title': 'English Grammar & Composition',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'spellcheck_rounded',
          'total_pages': 150,
        },
        {
          'id': 906,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'গণিত',
          'english_title': 'General Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 192,
        },
        {
          'id': 907,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'তথ্য ও যোগাযোগ প্রযুক্তি',
          'english_title': 'Information & Communication Technology',
          'subject': 'ICT',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'computer_rounded',
          'total_pages': 138,
        },
        {
          'id': 908,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'ক্যারিয়ার শিক্ষা',
          'english_title': 'Career Education',
          'subject': 'Career',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#C2410C',
          'icon': 'work_outline_rounded',
          'total_pages': 96,
        },
        {
          'id': 909,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'শারীরিক শিক্ষা, স্বাস্থ্য বিজ্ঞান ও খেলাধুলা',
          'english_title': 'Physical Education, Health & Sports',
          'subject': 'Physical Ed',
          'group': 'general',
          'cover_color_start': '#EF4444',
          'cover_color_end': '#B91C1C',
          'icon': 'sports_soccer_rounded',
          'total_pages': 108,
        },
        {
          'id': 910,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'ধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#047857',
          'icon': 'menu_book_rounded',
          'total_pages': 134,
        },
        {
          'id': 911,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'কৃষিশিক্ষা',
          'english_title': 'Agricultural Studies',
          'subject': 'Agriculture',
          'group': 'general',
          'cover_color_start': '#84CC16',
          'cover_color_end': '#4D7C0F',
          'icon': 'eco_rounded',
          'total_pages': 120,
        },
        {
          'id': 912,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'গার্হস্থ্য বিজ্ঞান',
          'english_title': 'Home Science',
          'subject': 'Home Science',
          'group': 'general',
          'cover_color_start': '#EC4899',
          'cover_color_end': '#BE185D',
          'icon': 'home_work_rounded',
          'total_pages': 124,
        },
        {
          'id': 913,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'চারু ও কারুকলা',
          'english_title': 'Arts & Crafts',
          'subject': 'Arts',
          'group': 'general',
          'cover_color_start': '#A855F7',
          'cover_color_end': '#7E22CE',
          'icon': 'palette_rounded',
          'total_pages': 98,
        },
        {
          'id': 914,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#0284C7',
          'cover_color_end': '#0369A1',
          'icon': 'public_rounded',
          'total_pages': 152,
        },
        {
          'id': 915,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'পদার্থবিজ্ঞান',
          'english_title': 'Physics',
          'subject': 'Physics',
          'group': 'science',
          'cover_color_start': '#EA580C',
          'cover_color_end': '#C2410C',
          'icon': 'bolt_rounded',
          'total_pages': 174,
        },
        {
          'id': 916,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'রসায়ন',
          'english_title': 'Chemistry',
          'subject': 'Chemistry',
          'group': 'science',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'science_rounded',
          'total_pages': 168,
        },
        {
          'id': 917,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'জীববিজ্ঞান',
          'english_title': 'Biology',
          'subject': 'Biology',
          'group': 'science',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4338CA',
          'icon': 'biotech_rounded',
          'total_pages': 170,
        },
        {
          'id': 918,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'উচ্চতর গণিত',
          'english_title': 'Higher Mathematics',
          'subject': 'Higher Math',
          'group': 'science',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'functions_rounded',
          'total_pages': 186,
        },
        {
          'id': 919,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'বাংলাদেশের ইতিহাস ও বিশ্বসভ্যতা',
          'english_title': 'History of Bangladesh & World Civilization',
          'subject': 'History',
          'group': 'humanities',
          'cover_color_start': '#854D0E',
          'cover_color_end': '#713F12',
          'icon': 'account_balance_rounded',
          'total_pages': 164,
        },
        {
          'id': 920,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'ভূগোল ও পরিবেশ',
          'english_title': 'Geography & Environment',
          'subject': 'Geography',
          'group': 'humanities',
          'cover_color_start': '#0891B2',
          'cover_color_end': '#0E7490',
          'icon': 'terrain_rounded',
          'total_pages': 156,
        },
        {
          'id': 921,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'অর্থনীতি',
          'english_title': 'Economics',
          'subject': 'Economics',
          'group': 'humanities',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'trending_up_rounded',
          'total_pages': 148,
        },
        {
          'id': 922,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'পৌরনীতি ও নাগরিকতা',
          'english_title': 'Civics & Citizenship',
          'subject': 'Civics',
          'group': 'humanities',
          'cover_color_start': '#7C3AED',
          'cover_color_end': '#5B21B6',
          'icon': 'gavel_rounded',
          'total_pages': 150,
        },
        {
          'id': 923,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'হিসাববিজ্ঞান',
          'english_title': 'Accounting',
          'subject': 'Accounting',
          'group': 'business',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1D4ED8',
          'icon': 'receipt_long_rounded',
          'total_pages': 172,
        },
        {
          'id': 924,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'ফিন্যান্স ও ব্যাংকিং',
          'english_title': 'Finance & Banking',
          'subject': 'Finance',
          'group': 'business',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#115E59',
          'icon': 'account_balance_wallet_rounded',
          'total_pages': 154,
        },
        {
          'id': 925,
          'class_level': 9,
          'class_name': 'Class 9',
          'class_name_bn': 'নবম শ্রেণি',
          'title': 'ব্যবসায় উদ্যোগ',
          'english_title': 'Business Entrepreneurship',
          'subject': 'Business',
          'group': 'business',
          'cover_color_start': '#EA580C',
          'cover_color_end': '#C2410C',
          'icon': 'business_center_rounded',
          'total_pages': 160,
        },
      ],
      // Class 10 (দশম শ্রেণি)
      10: [
        {
          'id': 1001,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'বাংলা সাহিত্য',
          'english_title': 'Bangla Literature',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#059669',
          'icon': 'menu_book_rounded',
          'total_pages': 180,
        },
        {
          'id': 1002,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'বাংলা সহপাঠ',
          'english_title': 'Bangla Companion',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'auto_stories_rounded',
          'total_pages': 110,
        },
        {
          'id': 1003,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'বাংলা ভাষার ব্যাকরণ ও নির্মিতি',
          'english_title': 'Bangla Grammar & Composition',
          'subject': 'Bangla',
          'group': 'general',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#0F766E',
          'icon': 'edit_note_rounded',
          'total_pages': 140,
        },
        {
          'id': 1004,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'English for Today',
          'english_title': 'English for Today (SSC)',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#3B82F6',
          'cover_color_end': '#1D4ED8',
          'icon': 'school_rounded',
          'total_pages': 160,
        },
        {
          'id': 1005,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'English Grammar and Composition',
          'english_title': 'English Grammar & Composition',
          'subject': 'English',
          'group': 'general',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'spellcheck_rounded',
          'total_pages': 150,
        },
        {
          'id': 1006,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'গণিত',
          'english_title': 'General Mathematics',
          'subject': 'Mathematics',
          'group': 'general',
          'cover_color_start': '#F59E0B',
          'cover_color_end': '#D97706',
          'icon': 'calculate_rounded',
          'total_pages': 192,
        },
        {
          'id': 1007,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'তথ্য ও যোগাযোগ প্রযুক্তি',
          'english_title': 'Information & Communication Technology',
          'subject': 'ICT',
          'group': 'general',
          'cover_color_start': '#06B6D4',
          'cover_color_end': '#0891B2',
          'icon': 'computer_rounded',
          'total_pages': 138,
        },
        {
          'id': 1008,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'ক্যারিয়ার শিক্ষা',
          'english_title': 'Career Education',
          'subject': 'Career',
          'group': 'general',
          'cover_color_start': '#F97316',
          'cover_color_end': '#C2410C',
          'icon': 'work_outline_rounded',
          'total_pages': 96,
        },
        {
          'id': 1009,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'শারীরিক শিক্ষা, স্বাস্থ্য বিজ্ঞান ও খেলাধুলা',
          'english_title': 'Physical Education, Health & Sports',
          'subject': 'Physical Ed',
          'group': 'general',
          'cover_color_start': '#EF4444',
          'cover_color_end': '#B91C1C',
          'icon': 'sports_soccer_rounded',
          'total_pages': 108,
        },
        {
          'id': 1010,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'ধর্ম ও নৈতিক শিক্ষা',
          'english_title': 'Religion & Moral Education',
          'subject': 'Religion',
          'group': 'general',
          'cover_color_start': '#10B981',
          'cover_color_end': '#047857',
          'icon': 'menu_book_rounded',
          'total_pages': 134,
        },
        {
          'id': 1011,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'কৃষিশিক্ষা',
          'english_title': 'Agricultural Studies',
          'subject': 'Agriculture',
          'group': 'general',
          'cover_color_start': '#84CC16',
          'cover_color_end': '#4D7C0F',
          'icon': 'eco_rounded',
          'total_pages': 120,
        },
        {
          'id': 1012,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'গার্হস্থ্য বিজ্ঞান',
          'english_title': 'Home Science',
          'subject': 'Home Science',
          'group': 'general',
          'cover_color_start': '#EC4899',
          'cover_color_end': '#BE185D',
          'icon': 'home_work_rounded',
          'total_pages': 124,
        },
        {
          'id': 1013,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'চারু ও কারুকলা',
          'english_title': 'Arts & Crafts',
          'subject': 'Arts',
          'group': 'general',
          'cover_color_start': '#A855F7',
          'cover_color_end': '#7E22CE',
          'icon': 'palette_rounded',
          'total_pages': 98,
        },
        {
          'id': 1014,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'বাংলাদেশ ও বিশ্বপরিচয়',
          'english_title': 'Bangladesh & Global Studies',
          'subject': 'BGS',
          'group': 'general',
          'cover_color_start': '#0284C7',
          'cover_color_end': '#0369A1',
          'icon': 'public_rounded',
          'total_pages': 152,
        },
        {
          'id': 1015,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'পদার্থবিজ্ঞান',
          'english_title': 'Physics',
          'subject': 'Physics',
          'group': 'science',
          'cover_color_start': '#EA580C',
          'cover_color_end': '#C2410C',
          'icon': 'bolt_rounded',
          'total_pages': 174,
        },
        {
          'id': 1016,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'রসায়ন',
          'english_title': 'Chemistry',
          'subject': 'Chemistry',
          'group': 'science',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'science_rounded',
          'total_pages': 168,
        },
        {
          'id': 1017,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'জীববিজ্ঞান',
          'english_title': 'Biology',
          'subject': 'Biology',
          'group': 'science',
          'cover_color_start': '#6366F1',
          'cover_color_end': '#4338CA',
          'icon': 'biotech_rounded',
          'total_pages': 170,
        },
        {
          'id': 1018,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'উচ্চতর গণিত',
          'english_title': 'Higher Mathematics',
          'subject': 'Higher Math',
          'group': 'science',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1E40AF',
          'icon': 'functions_rounded',
          'total_pages': 186,
        },
        {
          'id': 1019,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'বাংলাদেশের ইতিহাস ও বিশ্বসভ্যতা',
          'english_title': 'History of Bangladesh & World Civilization',
          'subject': 'History',
          'group': 'humanities',
          'cover_color_start': '#854D0E',
          'cover_color_end': '#713F12',
          'icon': 'account_balance_rounded',
          'total_pages': 164,
        },
        {
          'id': 1020,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'ভূগোল ও পরিবেশ',
          'english_title': 'Geography & Environment',
          'subject': 'Geography',
          'group': 'humanities',
          'cover_color_start': '#0891B2',
          'cover_color_end': '#0E7490',
          'icon': 'terrain_rounded',
          'total_pages': 156,
        },
        {
          'id': 1021,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'অর্থনীতি',
          'english_title': 'Economics',
          'subject': 'Economics',
          'group': 'humanities',
          'cover_color_start': '#059669',
          'cover_color_end': '#047857',
          'icon': 'trending_up_rounded',
          'total_pages': 148,
        },
        {
          'id': 1022,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'পৌরনীতি ও নাগরিকতা',
          'english_title': 'Civics & Citizenship',
          'subject': 'Civics',
          'group': 'humanities',
          'cover_color_start': '#7C3AED',
          'cover_color_end': '#5B21B6',
          'icon': 'gavel_rounded',
          'total_pages': 150,
        },
        {
          'id': 1023,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'হিসাববিজ্ঞান',
          'english_title': 'Accounting',
          'subject': 'Accounting',
          'group': 'business',
          'cover_color_start': '#2563EB',
          'cover_color_end': '#1D4ED8',
          'icon': 'receipt_long_rounded',
          'total_pages': 172,
        },
        {
          'id': 1024,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'ফিন্যান্স ও ব্যাংকিং',
          'english_title': 'Finance & Banking',
          'subject': 'Finance',
          'group': 'business',
          'cover_color_start': '#0D9488',
          'cover_color_end': '#115E59',
          'icon': 'account_balance_wallet_rounded',
          'total_pages': 154,
        },
        {
          'id': 1025,
          'class_level': 10,
          'class_name': 'Class 10',
          'class_name_bn': 'দশম শ্রেণি',
          'title': 'ব্যবসায় উদ্যোগ',
          'english_title': 'Business Entrepreneurship',
          'subject': 'Business',
          'group': 'business',
          'cover_color_start': '#EA580C',
          'cover_color_end': '#C2410C',
          'icon': 'business_center_rounded',
          'total_pages': 160,
        },
      ],
      // Class 11 (একাদশ শ্রেণি) - ২১টি বই
      11: [
        // সবার জন্য আবশ্যিক বিষয়
        {'id': 1101, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'বাংলা ১ম পত্র — সাহিত্যপাঠ', 'english_title': 'Bangla 1st Paper - Sahitya Path', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#059669', 'icon': 'menu_book_rounded', 'total_pages': 210},
        {'id': 1102, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'বাংলা ২য় পত্র — বাংলা ভাষা ও ব্যাকরণ', 'english_title': 'Bangla 2nd Paper - Bangla Grammar', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'auto_stories_rounded', 'total_pages': 140},
        {'id': 1103, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'English 1st Paper — English for Today', 'english_title': 'English 1st Paper - English for Today', 'subject': 'English', 'group': 'general', 'cover_color_start': '#3B82F6', 'cover_color_end': '#1D4ED8', 'icon': 'school_rounded', 'total_pages': 190},
        {'id': 1104, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'English 2nd Paper — Grammar and Composition', 'english_title': 'English 2nd Paper - Grammar & Composition', 'subject': 'English', 'group': 'general', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'spellcheck_rounded', 'total_pages': 165},
        {'id': 1105, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)', 'english_title': 'Information & Communication Technology', 'subject': 'ICT', 'group': 'general', 'cover_color_start': '#06B6D4', 'cover_color_end': '#0891B2', 'icon': 'computer_rounded', 'total_pages': 180},
        // বিজ্ঞান বিভাগের বই
        {'id': 1106, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'পদার্থবিজ্ঞান ১ম পত্র', 'english_title': 'Physics 1st Paper', 'subject': 'Physics', 'group': 'science', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'bolt_rounded', 'total_pages': 240},
        {'id': 1107, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'রসায়ন ১ম পত্র', 'english_title': 'Chemistry 1st Paper', 'subject': 'Chemistry', 'group': 'science', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'science_rounded', 'total_pages': 230},
        {'id': 1108, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'জীববিজ্ঞান ১ম পত্র', 'english_title': 'Biology 1st Paper', 'subject': 'Biology', 'group': 'science', 'cover_color_start': '#6366F1', 'cover_color_end': '#4338CA', 'icon': 'biotech_rounded', 'total_pages': 220},
        {'id': 1109, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'উচ্চতর গণিত ১ম পত্র', 'english_title': 'Higher Mathematics 1st Paper', 'subject': 'Higher Math', 'group': 'science', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'functions_rounded', 'total_pages': 260},
        // মানবিক বিভাগের বই
        {'id': 1110, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'ইতিহাস ১ম পত্র', 'english_title': 'History 1st Paper', 'subject': 'History', 'group': 'humanities', 'cover_color_start': '#854D0E', 'cover_color_end': '#713F12', 'icon': 'account_balance_rounded', 'total_pages': 195},
        {'id': 1111, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'ইসলামের ইতিহাস ও সংস্কৃতি ১ম পত্র', 'english_title': 'Islamic History & Culture 1st Paper', 'subject': 'History', 'group': 'humanities', 'cover_color_start': '#0891B2', 'cover_color_end': '#0E7490', 'icon': 'history_edu_rounded', 'total_pages': 185},
        {'id': 1112, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'পৌরনীতি ও সুশাসন ১ম পত্র', 'english_title': 'Civics & Good Governance 1st Paper', 'subject': 'Civics', 'group': 'humanities', 'cover_color_start': '#7C3AED', 'cover_color_end': '#5B21B6', 'icon': 'gavel_rounded', 'total_pages': 200},
        {'id': 1113, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'অর্থনীতি ১ম পত্র', 'english_title': 'Economics 1st Paper', 'subject': 'Economics', 'group': 'humanities', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'trending_up_rounded', 'total_pages': 195},
        {'id': 1114, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'ভূগোল ১ম পত্র', 'english_title': 'Geography 1st Paper', 'subject': 'Geography', 'group': 'humanities', 'cover_color_start': '#0E7490', 'cover_color_end': '#155E75', 'icon': 'terrain_rounded', 'total_pages': 180},
        {'id': 1115, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'যুক্তিবিদ্যা ১ম পত্র', 'english_title': 'Logic 1st Paper', 'subject': 'Logic', 'group': 'humanities', 'cover_color_start': '#4F46E5', 'cover_color_end': '#3730A3', 'icon': 'psychology_alt_rounded', 'total_pages': 175},
        {'id': 1116, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'সমাজবিজ্ঞান ১ম পত্র', 'english_title': 'Sociology 1st Paper', 'subject': 'Sociology', 'group': 'humanities', 'cover_color_start': '#854D0E', 'cover_color_end': '#713F12', 'icon': 'groups_rounded', 'total_pages': 190},
        {'id': 1117, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'সমাজকর্ম ১ম পত্র', 'english_title': 'Social Work 1st Paper', 'subject': 'Social Work', 'group': 'humanities', 'cover_color_start': '#D97706', 'cover_color_end': '#B45309', 'icon': 'volunteer_activism_rounded', 'total_pages': 185},
        // ব্যবসায় শিক্ষা বিভাগের বই
        {'id': 1118, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'হিসাববিজ্ঞান ১ম পত্র', 'english_title': 'Accounting 1st Paper', 'subject': 'Accounting', 'group': 'business', 'cover_color_start': '#2563EB', 'cover_color_end': '#1D4ED8', 'icon': 'receipt_long_rounded', 'total_pages': 225},
        {'id': 1119, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'ব্যবসায় সংগঠন ও ব্যবস্থাপনা ১ম পত্র', 'english_title': 'Business Organization & Management 1st Paper', 'subject': 'Business', 'group': 'business', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'business_center_rounded', 'total_pages': 210},
        {'id': 1120, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'ফিন্যান্স, ব্যাংকিং ও বিমা ১ম পত্র', 'english_title': 'Finance, Banking & Insurance 1st Paper', 'subject': 'Finance', 'group': 'business', 'cover_color_start': '#0D9488', 'cover_color_end': '#115E59', 'icon': 'account_balance_wallet_rounded', 'total_pages': 205},
        {'id': 1121, 'class_level': 11, 'class_name': 'Class 11', 'class_name_bn': 'একাদশ শ্রেণি', 'title': 'উৎপাদন ব্যবস্থাপনা ও বিপণন ১ম পত্র', 'english_title': 'Production Management & Marketing 1st Paper', 'subject': 'Marketing', 'group': 'business', 'cover_color_start': '#D97706', 'cover_color_end': '#B45309', 'icon': 'storefront_rounded', 'total_pages': 195},
      ],
      // Class 12 (দ্বাদশ শ্রেণি) - ২১টি বই
      12: [
        // সবার জন্য আবশ্যিক বিষয়
        {'id': 1201, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'বাংলা ১ম পত্র — সাহিত্যপাঠ', 'english_title': 'Bangla 1st Paper - Sahitya Path', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#059669', 'icon': 'menu_book_rounded', 'total_pages': 210},
        {'id': 1202, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'বাংলা ২য় পত্র — বাংলা ভাষা ও ব্যাকরণ', 'english_title': 'Bangla 2nd Paper - Bangla Grammar', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'auto_stories_rounded', 'total_pages': 140},
        {'id': 1203, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'English 1st Paper — English for Today', 'english_title': 'English 1st Paper - English for Today', 'subject': 'English', 'group': 'general', 'cover_color_start': '#3B82F6', 'cover_color_end': '#1D4ED8', 'icon': 'school_rounded', 'total_pages': 190},
        {'id': 1204, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'English 2nd Paper — Grammar and Composition', 'english_title': 'English 2nd Paper - Grammar & Composition', 'subject': 'English', 'group': 'general', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'spellcheck_rounded', 'total_pages': 165},
        {'id': 1205, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)', 'english_title': 'Information & Communication Technology', 'subject': 'ICT', 'group': 'general', 'cover_color_start': '#06B6D4', 'cover_color_end': '#0891B2', 'icon': 'computer_rounded', 'total_pages': 180},
        // বিজ্ঞান বিভাগের বই
        {'id': 1206, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'পদার্থবিজ্ঞান ২য় পত্র', 'english_title': 'Physics 2nd Paper', 'subject': 'Physics', 'group': 'science', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'bolt_rounded', 'total_pages': 250},
        {'id': 1207, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'রসায়ন ২য় পত্র', 'english_title': 'Chemistry 2nd Paper', 'subject': 'Chemistry', 'group': 'science', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'science_rounded', 'total_pages': 240},
        {'id': 1208, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'জীববিজ্ঞান ২য় পত্র', 'english_title': 'Biology 2nd Paper', 'subject': 'Biology', 'group': 'science', 'cover_color_start': '#6366F1', 'cover_color_end': '#4338CA', 'icon': 'biotech_rounded', 'total_pages': 230},
        {'id': 1209, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'উচ্চতর গণিত ২য় পত্র', 'english_title': 'Higher Mathematics 2nd Paper', 'subject': 'Higher Math', 'group': 'science', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'functions_rounded', 'total_pages': 270},
        // মানবিক বিভাগের বই
        {'id': 1210, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'ইতিহাস ২য় পত্র', 'english_title': 'History 2nd Paper', 'subject': 'History', 'group': 'humanities', 'cover_color_start': '#854D0E', 'cover_color_end': '#713F12', 'icon': 'account_balance_rounded', 'total_pages': 200},
        {'id': 1211, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'ইসলামের ইতিহাস ও সংস্কৃতি ২য় পত্র', 'english_title': 'Islamic History & Culture 2nd Paper', 'subject': 'History', 'group': 'humanities', 'cover_color_start': '#0891B2', 'cover_color_end': '#0E7490', 'icon': 'history_edu_rounded', 'total_pages': 190},
        {'id': 1212, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'পৌরনীতি ও সুশাসন ২য় পত্র', 'english_title': 'Civics & Good Governance 2nd Paper', 'subject': 'Civics', 'group': 'humanities', 'cover_color_start': '#7C3AED', 'cover_color_end': '#5B21B6', 'icon': 'gavel_rounded', 'total_pages': 210},
        {'id': 1213, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'অর্থনীতি ২য় পত্র', 'english_title': 'Economics 2nd Paper', 'subject': 'Economics', 'group': 'humanities', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'trending_up_rounded', 'total_pages': 200},
        {'id': 1214, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'ভূগোল ২য় পত্র', 'english_title': 'Geography 2nd Paper', 'subject': 'Geography', 'group': 'humanities', 'cover_color_start': '#0E7490', 'cover_color_end': '#155E75', 'icon': 'terrain_rounded', 'total_pages': 190},
        {'id': 1215, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'যুক্তিবিদ্যা ২য় পত্র', 'english_title': 'Logic 2nd Paper', 'subject': 'Logic', 'group': 'humanities', 'cover_color_start': '#4F46E5', 'cover_color_end': '#3730A3', 'icon': 'psychology_alt_rounded', 'total_pages': 180},
        {'id': 1216, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'সমাজবিজ্ঞান ২য় পত্র', 'english_title': 'Sociology 2nd Paper', 'subject': 'Sociology', 'group': 'humanities', 'cover_color_start': '#854D0E', 'cover_color_end': '#713F12', 'icon': 'groups_rounded', 'total_pages': 195},
        {'id': 1217, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'সমাজকর্ম ২য় পত্র', 'english_title': 'Social Work 2nd Paper', 'subject': 'Social Work', 'group': 'humanities', 'cover_color_start': '#D97706', 'cover_color_end': '#B45309', 'icon': 'volunteer_activism_rounded', 'total_pages': 190},
        // ব্যবসায় শিক্ষা বিভাগের বই
        {'id': 1218, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'হিসাববিজ্ঞান ২য় পত্র', 'english_title': 'Accounting 2nd Paper', 'subject': 'Accounting', 'group': 'business', 'cover_color_start': '#2563EB', 'cover_color_end': '#1D4ED8', 'icon': 'receipt_long_rounded', 'total_pages': 230},
        {'id': 1219, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'ব্যবসায় সংগঠন ও ব্যবস্থাপনা ২য় পত্র', 'english_title': 'Business Organization & Management 2nd Paper', 'subject': 'Business', 'group': 'business', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'business_center_rounded', 'total_pages': 215},
        {'id': 1220, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'ফিন্যান্স, ব্যাংকিং ও বিমা ২য় পত্র', 'english_title': 'Finance, Banking & Insurance 2nd Paper', 'subject': 'Finance', 'group': 'business', 'cover_color_start': '#0D9488', 'cover_color_end': '#115E59', 'icon': 'account_balance_wallet_rounded', 'total_pages': 210},
        {'id': 1221, 'class_level': 12, 'class_name': 'Class 12', 'class_name_bn': 'দ্বাদশ শ্রেণি', 'title': 'উৎপাদন ব্যবস্থাপনা ও বিপণন ২য় পত্র', 'english_title': 'Production Management & Marketing 2nd Paper', 'subject': 'Marketing', 'group': 'business', 'cover_color_start': '#D97706', 'cover_color_end': '#B45309', 'icon': 'storefront_rounded', 'total_pages': 200},
      ],
      // Diploma (ডিপ্লোমা - কারিগরি ও প্রযুক্তি)
      13: [
        {'id': 1301, 'class_level': 13, 'class_name': 'Diploma', 'class_name_bn': 'ডিপ্লোমা', 'title': 'প্রকৌশল অঙ্কন ও অটোক্যাড', 'english_title': 'Engineering Drawing & CAD', 'subject': 'Engineering', 'group': 'general', 'cover_color_start': '#0284C7', 'cover_color_end': '#0369A1', 'icon': 'architecture_rounded', 'total_pages': 210},
        {'id': 1302, 'class_level': 13, 'class_name': 'Diploma', 'class_name_bn': 'ডিপ্লোমা', 'title': 'কম্পিউটার প্রোগ্রামিং ও সি++', 'english_title': 'Computer Programming & C++', 'subject': 'CSE', 'group': 'general', 'cover_color_start': '#6366F1', 'cover_color_end': '#4338CA', 'icon': 'code_rounded', 'total_pages': 225},
        {'id': 1303, 'class_level': 13, 'class_name': 'Diploma', 'class_name_bn': 'ডিপ্লোমা', 'title': 'বেসিক ইলেকট্রিক্যাল ইঞ্জিনিয়ারিং', 'english_title': 'Basic Electrical Engineering', 'subject': 'Electrical', 'group': 'general', 'cover_color_start': '#F59E0B', 'cover_color_end': '#D97706', 'icon': 'bolt_rounded', 'total_pages': 195},
        {'id': 1304, 'class_level': 13, 'class_name': 'Diploma', 'class_name_bn': 'ডিপ্লোমা', 'title': 'ডিপ্লোমা ইঞ্জিনিয়ারিং গণিত', 'english_title': 'Engineering Mathematics', 'subject': 'Mathematics', 'group': 'general', 'cover_color_start': '#10B981', 'cover_color_end': '#059669', 'icon': 'functions_rounded', 'total_pages': 240},
        {'id': 1305, 'class_level': 13, 'class_name': 'Diploma', 'class_name_bn': 'ডিপ্লোমা', 'title': 'ফলিত পদার্থবিজ্ঞান', 'english_title': 'Applied Physics', 'subject': 'Physics', 'group': 'general', 'cover_color_start': '#8B5CF6', 'cover_color_end': '#6D28D9', 'icon': 'science_rounded', 'total_pages': 200},
        {'id': 1306, 'class_level': 13, 'class_name': 'Diploma', 'class_name_bn': 'ডিপ্লোমা', 'title': 'শিল্পোদ্যোগ ও প্রজেক্ট ম্যানেজমেন্ট', 'english_title': 'Industrial Management & Entrepreneurship', 'subject': 'Management', 'group': 'general', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'business_center_rounded', 'total_pages': 180},
      ],
      // Honours (অনার্স - স্নাতক সম্মান)
      14: [
        {'id': 1401, 'class_level': 14, 'class_name': 'Honours', 'class_name_bn': 'অনার্স', 'title': 'উচ্চতর গবেষণা পদ্ধতি ও পরিসংখ্যান', 'english_title': 'Research Methodology & Statistics', 'subject': 'Research', 'group': 'general', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'insights_rounded', 'total_pages': 260},
        {'id': 1402, 'class_level': 14, 'class_name': 'Honours', 'class_name_bn': 'অনার্স', 'title': 'অনার্স কোর কোর্স পেপার ১', 'english_title': 'Major Discipline Core Course - Paper I', 'subject': 'Core Major', 'group': 'general', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'menu_book_rounded', 'total_pages': 280},
        {'id': 1403, 'class_level': 14, 'class_name': 'Honours', 'class_name_bn': 'অনার্স', 'title': 'অনার্স কোর কোর্স পেপার ২', 'english_title': 'Major Discipline Core Course - Paper II', 'subject': 'Core Major', 'group': 'general', 'cover_color_start': '#1D4ED8', 'cover_color_end': '#1E3A8A', 'icon': 'auto_stories_rounded', 'total_pages': 290},
        {'id': 1404, 'class_level': 14, 'class_name': 'Honours', 'class_name_bn': 'অনার্স', 'title': 'সমন্বিত ইন্টারডিসিপ্লিনারি স্টাডিজ', 'english_title': 'Interdisciplinary Allied Studies', 'subject': 'Allied', 'group': 'general', 'cover_color_start': '#7C3AED', 'cover_color_end': '#5B21B6', 'icon': 'hub_rounded', 'total_pages': 220},
        {'id': 1405, 'class_level': 14, 'class_name': 'Honours', 'class_name_bn': 'অনার্স', 'title': 'তথ্যপ্রযুক্তি ও উপাত্ত বিশ্লেষণ', 'english_title': 'ICT & Data Analytics', 'subject': 'ICT', 'group': 'general', 'cover_color_start': '#0891B2', 'cover_color_end': '#0E7490', 'icon': 'computer_rounded', 'total_pages': 210},
        {'id': 1406, 'class_level': 14, 'class_name': 'Honours', 'class_name_bn': 'অনার্স', 'title': 'একাডেমিক যোগাযোগ ও প্রেজেন্টেশন', 'english_title': 'Academic Communication & Presentation', 'subject': 'English', 'group': 'general', 'cover_color_start': '#DB2777', 'cover_color_end': '#9D174D', 'icon': 'record_voice_over_rounded', 'total_pages': 190},
      ],
      // Degree (ডিগ্রি পাস কোর্স)
      15: [
        {'id': 1501, 'class_level': 15, 'class_name': 'Degree', 'class_name_bn': 'ডিগ্রি', 'title': 'স্বাধীন বাংলাদেশের অভ্যুদয়ের ইতিহাস', 'english_title': 'History of Emergence of Independent Bangladesh', 'subject': 'History', 'group': 'general', 'cover_color_start': '#16A34A', 'cover_color_end': '#15803D', 'icon': 'flag_rounded', 'total_pages': 240},
        {'id': 1502, 'class_level': 15, 'class_name': 'Degree', 'class_name_bn': 'ডিগ্রি', 'title': 'বাংলা সাহিত্য ও প্রায়োগিক ভাষা', 'english_title': 'Bangla Literature & Applied Language', 'subject': 'Bangla', 'group': 'general', 'cover_color_start': '#0D9488', 'cover_color_end': '#0F766E', 'icon': 'library_books_rounded', 'total_pages': 220},
        {'id': 1503, 'class_level': 15, 'class_name': 'Degree', 'class_name_bn': 'ডিগ্রি', 'title': 'ইংরেজি যোগাযোগ দক্ষতা', 'english_title': 'English Communication Skills', 'subject': 'English', 'group': 'general', 'cover_color_start': '#3B82F6', 'cover_color_end': '#1D4ED8', 'icon': 'school_rounded', 'total_pages': 210},
        {'id': 1504, 'class_level': 15, 'class_name': 'Degree', 'class_name_bn': 'ডিগ্রি', 'title': 'ডিগ্রি নির্বাচনি কোর্স ১', 'english_title': 'Degree Elective Course 1', 'subject': 'Elective', 'group': 'general', 'cover_color_start': '#F97316', 'cover_color_end': '#C2410C', 'icon': 'book_rounded', 'total_pages': 250},
        {'id': 1505, 'class_level': 15, 'class_name': 'Degree', 'class_name_bn': 'ডিগ্রি', 'title': 'ডিগ্রি নির্বাচনি কোর্স ২', 'english_title': 'Degree Elective Course 2', 'subject': 'Elective', 'group': 'general', 'cover_color_start': '#8B5CF6', 'cover_color_end': '#6D28D9', 'icon': 'menu_book_rounded', 'total_pages': 250},
        {'id': 1506, 'class_level': 15, 'class_name': 'Degree', 'class_name_bn': 'ডিগ্রি', 'title': 'ডিগ্রি নির্বাচনি কোর্স ৩', 'english_title': 'Degree Elective Course 3', 'subject': 'Elective', 'group': 'general', 'cover_color_start': '#06B6D4', 'cover_color_end': '#0891B2', 'icon': 'auto_stories_rounded', 'total_pages': 250},
      ],
      // Masters (মাস্টার্স - স্নাতকোত্তর)
      16: [
        {'id': 1601, 'class_level': 16, 'class_name': 'Masters', 'class_name_bn': 'মাস্টার্স', 'title': 'মাস্টার্স থিসিস ও গবেষণা গ্রন্থনা', 'english_title': "Master's Thesis & Advanced Research", 'subject': 'Thesis', 'group': 'general', 'cover_color_start': '#4F46E5', 'cover_color_end': '#3730A3', 'icon': 'science_rounded', 'total_pages': 300},
        {'id': 1602, 'class_level': 16, 'class_name': 'Masters', 'class_name_bn': 'মাস্টার্স', 'title': 'উচ্চতর তাত্ত্বিক সমীক্ষা ও প্রয়োগ', 'english_title': 'Contemporary Advanced Theories & Evaluation', 'subject': 'Theory', 'group': 'general', 'cover_color_start': '#059669', 'cover_color_end': '#047857', 'icon': 'psychology_rounded', 'total_pages': 275},
        {'id': 1603, 'class_level': 16, 'class_name': 'Masters', 'class_name_bn': 'মাস্টার্স', 'title': 'উচ্চতর একাডেমিক সেমিনার ও গবেষণা', 'english_title': 'Advanced Academic Seminar & Research', 'subject': 'Seminar', 'group': 'general', 'cover_color_start': '#2563EB', 'cover_color_end': '#1E40AF', 'icon': 'co_present_rounded', 'total_pages': 230},
        {'id': 1604, 'class_level': 16, 'class_name': 'Masters', 'class_name_bn': 'মাস্টার্স', 'title': 'পেশাগত নীতিশাস্ত্র ও জার্নাল প্রকাশনা', 'english_title': 'Professional Ethics & Research Publication', 'subject': 'Publication', 'group': 'general', 'cover_color_start': '#EA580C', 'cover_color_end': '#C2410C', 'icon': 'assignment_turned_in_rounded', 'total_pages': 210},
        {'id': 1605, 'class_level': 16, 'class_name': 'Masters', 'class_name_bn': 'মাস্টার্স', 'title': 'বিশেষায়িত মাস্টার্স কোর্স ১', 'english_title': 'Specialized Subject Course - Advanced I', 'subject': 'Specialization', 'group': 'general', 'cover_color_start': '#8B5CF6', 'cover_color_end': '#6D28D9', 'icon': 'military_tech_rounded', 'total_pages': 260},
        {'id': 1606, 'class_level': 16, 'class_name': 'Masters', 'class_name_bn': 'মাস্টার্স', 'title': 'বিশেষায়িত মাস্টার্স কোর্স ২', 'english_title': 'Specialized Subject Course - Advanced II', 'subject': 'Specialization', 'group': 'general', 'cover_color_start': '#0D9488', 'cover_color_end': '#115E59', 'icon': 'workspace_premium_rounded', 'total_pages': 260},
      ],
    };

    final rawBooks = catalog[level] ?? (level < 1 ? catalog[-2] : (level >= 13 ? catalog[16] : catalog[1]))!;
    final filtered = (level >= 9 && level <= 12) && group != null && group.isNotEmpty && group != 'all'
        ? rawBooks.where((b) => b['group'] == 'general' || b['group'] == group).toList()
        : rawBooks;

    return filtered.map((b) => TextbookModel.fromJson(b)).toList();
  }

  /// 13 Institution Types specified for Registration
  static final List<Map<String, dynamic>> userInstitutionTypes = [
    {
      'id': 'govt_school',
      'name_bn': '১. সরকারি স্কুল',
      'name_en': '1. Government School',
      'default_inst': 'চট্টগ্রাম সরকারি উচ্চ বিদ্যালয়',
    },
    {
      'id': 'private_school',
      'name_bn': '২. বেসরকারি স্কুল',
      'name_en': '2. Private School',
      'default_inst': 'আইডিয়াল স্কুল অ্যান্ড কলেজ',
    },
    {
      'id': 'govt_college',
      'name_bn': '৩. সরকারি কলেজ',
      'name_en': '3. Government College',
      'default_inst': 'ঢাকা কলেজ',
    },
    {
      'id': 'private_college',
      'name_bn': '৪. বেসরকারি কলেজ',
      'name_en': '4. Private College',
      'default_inst': 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ (DRMC)',
    },
    {
      'id': 'primary_school',
      'name_bn': '৫. প্রাথমিক বিদ্যালয়',
      'name_en': '5. Primary School',
      'default_inst': 'ধানমন্ডি মডেল সরকারি প্রাথমিক বিদ্যালয়',
    },
    {
      'id': 'kindergarten',
      'name_bn': '৬. কিন্ডারগার্টেন',
      'name_en': '6. Kindergarten',
      'default_inst': 'লিটল ফ্লাওয়ার্স কিন্ডারগার্টেন',
    },
    {
      'id': 'madrasah',
      'name_bn': '৭. মাদ্রাসা',
      'name_en': '7. Madrasah',
      'default_inst': 'তা’মীরুল মিল্লাত কামিল মাদ্রাসা',
    },
    {
      'id': 'vocational',
      'name_bn': '৮. কারিগরি প্রতিষ্ঠান',
      'name_en': '8. Technical Institution',
      'default_inst': 'বাংলাদেশ কোরিয়া কারিগরি প্রশিক্ষণ কেন্দ্র',
    },
    {
      'id': 'polytechnic',
      'name_bn': '৯. পলিটেকনিক',
      'name_en': '9. Polytechnic',
      'default_inst': 'ঢাকা পলিটেকনিক ইনস্টিটিউট',
    },
    {
      'id': 'university',
      'name_bn': '১০. বিশ্ববিদ্যালয়',
      'name_en': '10. University',
      'default_inst': 'শাহজালাল বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয় (SUST)',
    },
    {
      'id': 'medical_college',
      'name_bn': '১১. মেডিকেল কলেজ',
      'name_en': '11. Medical College',
      'default_inst': 'শহীদ সোহরাওয়ার্দী মেডিকেল কলেজ',
    },
    {
      'id': 'engineering_college',
      'name_bn': '১২. ইঞ্জিনিয়ারিং কলেজ',
      'name_en': '12. Engineering College',
      'default_inst': 'ময়মনসিংহ ইঞ্জিনিয়ারিং কলেজ',
    },
    {
      'id': 'other_institution',
      'name_bn': '১৩. অন্যান্য প্রতিষ্ঠান',
      'name_en': '13. Other Institution',
      'default_inst': 'অন্যান্য শিক্ষা প্রতিষ্ঠান',
    },
  ];

  /// 20 Class / Degree options specified for Registration
  static final List<Map<String, String>> classDegreeOptions = [
    {'id': 'Play Group', 'name_bn': 'প্লে গ্রুপ', 'name_en': 'Play Group', 'name': 'প্লে গ্রুপ (Play Group)'},
    {'id': 'Nursery', 'name_bn': 'নার্সারি', 'name_en': 'Nursery', 'name': 'নার্সারি (Nursery)'},
    {'id': 'KG', 'name_bn': 'কেজি', 'name_en': 'KG', 'name': 'কেজি (KG)'},
    {'id': 'Class 1', 'name_bn': 'প্রথম শ্রেণি', 'name_en': 'Class 1', 'name': 'Class 1 (প্রথম শ্রেণি)'},
    {'id': 'Class 2', 'name_bn': 'দ্বিতীয় শ্রেণি', 'name_en': 'Class 2', 'name': 'Class 2 (দ্বিতীয় শ্রেণি)'},
    {'id': 'Class 3', 'name_bn': 'তৃতীয় শ্রেণি', 'name_en': 'Class 3', 'name': 'Class 3 (তৃতীয় শ্রেণি)'},
    {'id': 'Class 4', 'name_bn': 'চতুর্থ শ্রেণি', 'name_en': 'Class 4', 'name': 'Class 4 (চতুর্থ শ্রেণি)'},
    {'id': 'Class 5', 'name_bn': 'পঞ্চম শ্রেণি', 'name_en': 'Class 5', 'name': 'Class 5 (পঞ্চম শ্রেণি)'},
    {'id': 'Class 6', 'name_bn': 'ষষ্ঠ শ্রেণি', 'name_en': 'Class 6', 'name': 'Class 6 (ষষ্ঠ শ্রেণি)'},
    {'id': 'Class 7', 'name_bn': 'সপ্তম শ্রেণি', 'name_en': 'Class 7', 'name': 'Class 7 (সপ্তম শ্রেণি)'},
    {'id': 'Class 8', 'name_bn': 'অষ্টম শ্রেণি', 'name_en': 'Class 8', 'name': 'Class 8 (অষ্টম শ্রেণি)'},
    {'id': 'Class 9', 'name_bn': 'নবম শ্রেণি', 'name_en': 'Class 9', 'name': 'Class 9 (নবম শ্রেণি)'},
    {'id': 'Class 10', 'name_bn': 'দশম শ্রেণি', 'name_en': 'Class 10', 'name': 'Class 10 (দশম শ্রেণি)'},
    {'id': 'Class 11', 'name_bn': 'একাদশ শ্রেণি', 'name_en': 'Class 11', 'name': 'Class 11 (একাদশ শ্রেণি)'},
    {'id': 'Class 12', 'name_bn': 'দ্বাদশ শ্রেণি', 'name_en': 'Class 12', 'name': 'Class 12 (দ্বাদশ শ্রেণি)'},
    {'id': 'Diploma', 'name_bn': 'ডিপ্লোমা', 'name_en': 'Diploma', 'name': 'ডিপ্লোমা (Diploma)'},
    {'id': 'Honours', 'name_bn': 'অনার্স', 'name_en': 'Honours', 'name': 'অনার্স (Honours)'},
    {'id': 'Degree', 'name_bn': 'ডিগ্রি', 'name_en': 'Degree', 'name': 'ডিগ্রি (Degree)'},
    {'id': 'Masters', 'name_bn': 'মাস্টার্স', 'name_en': 'Masters', 'name': 'মাস্টার্স (Masters)'},
    {'id': 'Others', 'name_bn': 'অন্যান্য', 'name_en': 'Others', 'name': 'অন্যান্য (Others)'},
  ];

  /// All 19 Supported Institution Types with Full Management (পূর্ণাঙ্গ ব্যবস্থাপনা)
  static final List<Map<String, dynamic>> allInstitutionTypes = [
    {
      'id': 'govt_primary_school',
      'name_bn': 'সরকারি প্রাথমিক বিদ্যালয়',
      'name_en': 'Government Primary School',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'ধানমন্ডি মডেল সরকারি প্রাথমিক বিদ্যালয়',
      'classes': [
        'Class 1 (প্রথম শ্রেণি)',
        'Class 2 (দ্বিতীয় শ্রেণি)',
        'Class 3 (তৃতীয় শ্রেণি)',
        'Class 4 (চতুর্থ শ্রেণি)',
        'Class 5 (পঞ্চম শ্রেণি)',
        'প্রাক-প্রাথমিক / শিশু শ্রেণি',
      ],
    },
    {
      'id': 'private_primary_school',
      'name_bn': 'বেসরকারি প্রাথমিক বিদ্যালয়',
      'name_en': 'Private Primary School',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'গ্রীন ভিউ বেসরকারি প্রাথমিক বিদ্যালয়',
      'classes': [
        'প্লে গ্রুপ (Play)',
        'নার্সারি (Nursery)',
        'কেজি (KG)',
        'Class 1 (প্রথম শ্রেণি)',
        'Class 2 (দ্বিতীয় শ্রেণি)',
        'Class 3 (তৃতীয় শ্রেণি)',
        'Class 4 (চতুর্থ শ্রেণি)',
        'Class 5 (পঞ্চম শ্রেণি)',
      ],
    },
    {
      'id': 'govt_high_school',
      'name_bn': 'সরকারি উচ্চ বিদ্যালয়',
      'name_en': 'Government High School',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'চট্টগ্রাম সরকারি উচ্চ বিদ্যালয়',
      'classes': [
        'Class 6 (ষষ্ঠ শ্রেণি)',
        'Class 7 (সপ্তম শ্রেণি)',
        'Class 8 (অষ্টম শ্রেণি)',
        'Class 9 (নবম শ্রেণি)',
        'Class 10 (দশম শ্রেণি)',
        'SSC পরীক্ষার্থী ব্যাচ',
      ],
    },
    {
      'id': 'private_high_school',
      'name_bn': 'বেসরকারি উচ্চ বিদ্যালয়',
      'name_en': 'Private High School',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'আইডিয়াল স্কুল অ্যান্ড কলেজ (হাইস্কুল শাখা)',
      'classes': [
        'Class 6 (ষষ্ঠ শ্রেণি)',
        'Class 7 (সপ্তম শ্রেণি)',
        'Class 8 (অষ্টম শ্রেণি)',
        'Class 9 (নবম শ্রেণি)',
        'Class 10 (দশম শ্রেণি)',
        'SSC পরীক্ষার্থী ব্যাচ',
      ],
    },
    {
      'id': 'kindergarten',
      'name_bn': 'কিন্ডারগার্টেন',
      'name_en': 'Kindergarten',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'লিটল ফ্লাওয়ার্স কিন্ডারগার্টেন',
      'classes': [
        'প্লে গ্রুপ (Play)',
        'নার্সারি (Nursery)',
        'কেজি (KG)',
        'Class 1 (প্রথম শ্রেণি)',
        'Class 2 (দ্বিতীয় শ্রেণি)',
        'Class 3 (তৃতীয় শ্রেণি)',
        'Class 4 (চতুর্থ শ্রেণি)',
        'Class 5 (পঞ্চম শ্রেণি)',
      ],
    },
    {
      'id': 'preschool_nursery',
      'name_bn': 'প্রি-স্কুল ও নার্সারি',
      'name_en': 'Pre-School & Nursery',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'কিডস হ্যাভেন প্রি-স্কুল ও নার্সারি',
      'classes': [
        'টডলার (Toddler)',
        'প্লে গ্রুপ (Play)',
        'নার্সারি (Nursery)',
        'জুনিয়র কেজি (Jr. KG)',
        'সিনিয়র কেজি (Sr. KG)',
        'Class 1 (প্রথম শ্রেণি)',
      ],
    },
    {
      'id': 'govt_college',
      'name_bn': 'সরকারি কলেজ',
      'name_en': 'Government College',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'ঢাকা কলেজ (Dhaka College)',
      'classes': [
        'একাদশ শ্রেণি (HSC 1st Year)',
        'দ্বাদশ শ্রেণি (HSC 2nd Year)',
        'ডিগ্রি ১ম বর্ষ',
        'ডিগ্রি ২য় বর্ষ',
        'ডিগ্রি ৩য় বর্ষ',
        'অনার্স ১ম বর্ষ',
      ],
    },
    {
      'id': 'private_college',
      'name_bn': 'বেসরকারি কলেজ',
      'name_en': 'Private College',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ (DRMC)',
      'classes': [
        'একাদশ শ্রেণি বিজ্ঞান (HSC Science)',
        'একাদশ শ্রেণি মানবিক (HSC Humanities)',
        'একাদশ শ্রেণি ব্যবসায় শিক্ষা (HSC Business Studies)',
        'দ্বাদশ শ্রেণি (HSC 2nd Year)',
        'ডিগ্রি / পাস কোর্স',
      ],
    },
    {
      'id': 'madrasah',
      'name_bn': 'মাদ্রাসা',
      'name_en': 'Madrasah',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'তা’মীরুল মিল্লাত কামিল মাদ্রাসা',
      'classes': [
        'ইবতেদায়ী ১ম শ্রেণি (Class 1)',
        'ইবতেদায়ী ৫ম শ্রেণি (Class 5)',
        'দাখিল ৬ষ্ঠ শ্রেণি (Class 6)',
        'দাখিল ৮ম শ্রেণি (Class 8)',
        'দাখিল ১০ম শ্রেণি (Class 10)',
        'আলিম ১ম বর্ষ (HSC Equivalent)',
      ],
    },
    {
      'id': 'alia_madrasah',
      'name_bn': 'আলিয়া মাদ্রাসা',
      'name_en': 'Alia Madrasah',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'ঢাকা সরকারি আলিয়া মাদ্রাসা',
      'classes': [
        'ইবতেদায়ী ১ম শ্রেণি (Class 1)',
        'ইবতেদায়ী ৫ম শ্রেণি (Class 5)',
        'দাখিল ১০ম শ্রেণি (Dakhil)',
        'আলিম ১ম বর্ষ (Alim 1st Year)',
        'আলিম ২য় বর্ষ (Alim 2nd Year)',
        'ফাজিল ১ম-৩য় বর্ষ',
        'কামিল হাদিস / তাফসির',
      ],
    },
    {
      'id': 'qawmi_madrasah',
      'name_bn': 'কওমি মাদ্রাসা',
      'name_en': 'Qawmi Madrasah',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'জামিয়া ইসলামিয়া দারুল উলুম কওমি মাদ্রাসা',
      'classes': [
        'নূরানী ও মক্তব',
        'নাজেরা জামাত',
        'হিফজুল কুরআন বিভাগ',
        'মিযান জামাত',
        'শরহে বেকায়া',
        'জালালাইন জামাত',
        'দাওরায়ে হাদিস (তাকমীল)',
      ],
    },
    {
      'id': 'university',
      'name_bn': 'বিশ্ববিদ্যালয়',
      'name_en': 'University',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'শাহজালাল বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয় (SUST)',
      'classes': [
        '১ম বর্ষ ১ম সেমিস্টার (1st Sem)',
        '১ম বর্ষ ২য় সেমিস্টার (2nd Sem)',
        '২য় বর্ষ ৩য় সেমিস্টার (3rd Sem)',
        '২য় বর্ষ ৪র্থ সেমিস্টার (4th Sem)',
        '৩য় বর্ষ ৫ম সেমিস্টার (5th Sem)',
        '৩য় বর্ষ ৬ষ্ঠ সেমিস্টার (6th Sem)',
        '৪র্থ বর্ষ ৭ম সেমিস্টার (7th Sem)',
        '৪র্থ বর্ষ ৮ম সেমিস্টার (8th Sem)',
        'মাস্টার্স / এমফিল / পিএইচডি',
      ],
    },
    {
      'id': 'medical_college',
      'name_bn': 'মেডিকেল কলেজ',
      'name_en': 'Medical College',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'শহীদ সোহরাওয়ার্দী মেডিকেল কলেজ হাসপাতাল',
      'classes': [
        'MBBS ১ম প্রফেশনাল (1st Prof)',
        'MBBS ২য় প্রফেশনাল (2nd Prof)',
        'MBBS ৩য় প্রফেশনাল (3rd Prof)',
        'MBBS চূড়ান্ত প্রফেশনাল (Final Prof)',
        'BDS ডেন্টাল বিভাগ',
        'ইন্টার্নশিপ চিকিৎসক',
      ],
    },
    {
      'id': 'engineering_college',
      'name_bn': 'ইঞ্জিনিয়ারিং কলেজ',
      'name_en': 'Engineering College',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'ময়মনসিংহ ইঞ্জিনিয়ারিং কলেজ',
      'classes': [
        'B.Sc Engineering ১ম সেমিস্টার',
        'B.Sc Engineering ২য় সেমিস্টার',
        'B.Sc Engineering ৩য় সেমিস্টার',
        'B.Sc Engineering ৪র্থ সেমিস্টার',
        'B.Sc Engineering ৫ম সেমিস্টার',
        'B.Sc Engineering ৬ষ্ঠ সেমিস্টার',
        'B.Sc Engineering ৭ম সেমিস্টার',
        'B.Sc Engineering ৮ম সেমিস্টার',
      ],
    },
    {
      'id': 'polytechnic',
      'name_bn': 'পলিটেকনিক',
      'name_en': 'Polytechnic Institute',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'ঢাকা পলিটেকনিক ইনস্টিটিউট',
      'classes': [
        'ডিপ্লোমা ১ম পর্ব (1st Sem)',
        'ডিপ্লোমা ২য় পর্ব (2nd Sem)',
        'ডিপ্লোমা ৩য় পর্ব (3rd Sem)',
        'ডিপ্লোমা ৪র্থ পর্ব (4th Sem)',
        'ডিপ্লোমা ৫ম পর্ব (5th Sem)',
        'ডিপ্লোমা ৬ষ্ঠ পর্ব (6th Sem)',
        'ডিপ্লোমা ৭ম পর্ব (7th Sem)',
        'ডিপ্লোমা ৮ম পর্ব (ইন্টার্নশিপ)',
      ],
    },
    {
      'id': 'vocational_training',
      'name_bn': 'কারিগরি প্রশিক্ষণ কেন্দ্র',
      'name_en': 'Technical & Vocational Center',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'বাংলাদেশ কোরিয়া কারিগরি প্রশিক্ষণ কেন্দ্র (BKTTC)',
      'classes': [
        'ভোকেশনাল এসএসসি ১ম বর্ষ',
        'ভোকেশনাল এসএসসি ২য় বর্ষ',
        'ইলেকট্রিক্যাল ও ইলেকট্রনিক্স ট্রেড',
        'অটোমোবাইল ও মেকানিক্যাল ট্রেড',
        'কম্পিউটার অপারেশন ও গ্রাফিক্স ট্রেড',
        'শর্ট কোর্স (৩ মাস / ৬ মাস)',
      ],
    },
    {
      'id': 'coaching_center',
      'name_bn': 'কোচিং সেন্টার',
      'name_en': 'Coaching Center',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'উদ্ভাস-উন্মেষ ও উত্তরণ শিক্ষা পরিবার',
      'classes': [
        'মেডিকেল ভর্তি কোচিং ব্যাচ',
        'বুয়েট ও ইঞ্জিনিয়ারিং ভর্তি ব্যাচ',
        'বিশ্ববিদ্যালয় ক-ইউনিট বিজ্ঞান',
        'বিশ্ববিদ্যালয় খ/গ ইউনিট মানবিক ও ব্যবসায়',
        'এইচএসসি একাডেমিক মডেল টেস্ট ব্যাচ',
        'এসএসসি স্পেশাল রিভিশন ব্যাচ',
      ],
    },
    {
      'id': 'english_medium',
      'name_bn': 'ইংরেজি মাধ্যম প্রতিষ্ঠান',
      'name_en': 'English Medium Institution',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'মাস্টারমাইন্ড ও সানিডেল ইংলিশ মিডিয়াম স্কুল',
      'classes': [
        'Playgroup',
        'Nursery',
        'Kindergarten (KG)',
        'Class 1 (Grade 1)',
        'Class 2 (Grade 2)',
        'Class 3 (Grade 3)',
        'Class 4 (Grade 4)',
        'Class 5 (Grade 5)',
        'Grade 6 to Grade 8 (Junior Section)',
        'O Level (Grade 9 & 10)',
        'A Level (AS & A2 Level)',
      ],
    },
    {
      'id': 'religious_other',
      'name_bn': 'ধর্মীয় ও অন্যান্য শিক্ষা প্রতিষ্ঠান',
      'name_en': 'Religious & Other Institutions',
      'management': 'পূর্ণাঙ্গ',
      'default_inst': 'দারুল কুরআন ইসলামী একাডেমি ও প্রশিক্ষণ কেন্দ্র',
      'classes': [
        'নূরানী কোরআন শিক্ষা কোর্স',
        'বয়স্ক দ্বীনি তালিম ও তাজবীদ',
        'হিফজুল কুরআন বিভাগ',
        'ভাষা ও ধর্মীয় সংস্কৃতি শিক্ষা',
        'বিশেষ ধর্মতত্ত্ব ও নৈতিক শিক্ষা কোর্স',
      ],
    },
  ];

  static List<Map<String, dynamic>> _dynamicClassesCache = [];

  /// Get live dynamic classes from the database / API
  static Future<List<Map<String, dynamic>>> getAvailableClasses({int? institutionId}) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final instParam = institutionId != null ? '?institution_id=$institutionId' : '';
      final request = await client.getUrl(Uri.parse('$studentBaseUrl/available-classes$instParam'));
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true && data['classes'] is List) {
          final list = (data['classes'] as List).map((c) => Map<String, dynamic>.from(c)).toList();
          if (list.isNotEmpty) {
            _dynamicClassesCache = list;
            return list;
          }
        }
      }
    } catch (_) {}

    if (_dynamicClassesCache.isNotEmpty) {
      return _dynamicClassesCache;
    }

    return classDegreeOptions.map((c) => {
      'id': c['id']!,
      'name': c['name'] ?? c['name_bn']!,
      'name_bn': c['name_bn']!,
      'name_en': c['name_en']!,
      'numeric_level': resolveClassLevel(c['id']),
      'group': 'general',
      'medium': 'bangla',
      'sections': [
        {'id': 1, 'name': 'শাখা - ক (Section A)', 'shift': 'morning', 'room_number': 'Room 101'}
      ],
    }).toList();
  }

  /// Create and Sync a new dynamic custom class with the database
  static Future<Map<String, dynamic>> createCustomClass({
    required String name,
    int? numericLevel,
    String? group,
    String? sectionName,
    int? institutionId,
  }) async {
    final level = numericLevel ?? resolveClassLevel(name);
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/classes'));
      request.headers.set('content-type', 'application/json');
      request.add(utf8.encode(jsonEncode({
        'name': name,
        'numeric_level': level,
        'group': group ?? ((level >= 9 && level <= 12) ? 'science' : 'general'),
        'section_name': sectionName ?? 'শাখা - ক (Section A)',
        if (institutionId != null) 'institution_id': institutionId,
      })));
      final response = await request.close();
      if (response.statusCode == 200 || response.statusCode == 201) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true && data['class'] != null) {
          final newClass = Map<String, dynamic>.from(data['class']);
          _dynamicClassesCache.removeWhere((c) => c['name'] == newClass['name'] || c['id'] == newClass['name']);
          _dynamicClassesCache.add(newClass);
          return {'success': true, 'class': newClass};
        }
      }
    } catch (_) {}

    final fallbackClass = {
      'id': name,
      'class_id': 999,
      'name': name,
      'name_bn': name,
      'name_en': name,
      'numeric_level': level,
      'group': group ?? ((level >= 9 && level <= 12) ? 'science' : 'general'),
      'medium': 'bangla',
      'sections': [
        {'id': 999, 'name': sectionName ?? 'শাখা - ক (Section A)', 'shift': 'morning', 'room_number': 'Room 101'}
      ],
    };
    _dynamicClassesCache.removeWhere((c) => c['name'] == name || c['id'] == name);
    _dynamicClassesCache.add(fallbackClass);
    return {'success': true, 'class': fallbackClass};
  }

  /// Student Registration with name, email, password, rollNumber, className, and institution details
  static const List<Map<String, String>> groupOptions = [
    {'id': 'science', 'name': 'Science', 'name_bn': 'বিজ্ঞান'},
    {'id': 'humanities', 'name': 'Humanities', 'name_bn': 'মানবিক'},
    {'id': 'business', 'name': 'Business Studies', 'name_bn': 'ব্যবসায় শিক্ষা'},
  ];

  /// Student Registration with name, email, password, rollNumber, className, group, and institution details
  static Future<Map<String, dynamic>> studentRegister({
    required String name,
    required String email,
    required String password,
    required String rollNumber,
    required String className,
    int? academicClassId,
    int? sectionId,
    String? group,
    String? fatherName,
    String? motherName,
    String? institutionType,
    String? institutionName,
    int? institutionId,
  }) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/register'));
      request.headers.set('content-type', 'application/json');
      request.add(utf8.encode(jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'roll_number': rollNumber,
        'class_name': className,
        if (academicClassId != null) 'academic_class_id': academicClassId,
        if (sectionId != null) 'section_id': sectionId,
        if (group != null && group.isNotEmpty) 'group': group,
        if (fatherName != null && fatherName.isNotEmpty) 'father_name': fatherName,
        if (motherName != null && motherName.isNotEmpty) 'mother_name': motherName,
        if (institutionType != null) 'institution_type': institutionType,
        if (institutionName != null) 'institution_name': institutionName,
        if (institutionId != null) 'institution_id': institutionId,
      })));
      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      final data = jsonDecode(body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (data['success'] == true) {
          registeredEmail = email;
          registeredPassword = password;
          if (data['token'] != null) currentAuthToken = data['token'];
          if (data['student'] != null) {
            currentStudentData = Map<String, dynamic>.from(data['student']);
            registeredStudentData = Map<String, dynamic>.from(data['student']);
            if (group != null) currentStudentGroup = group;
            if (data['student']['group'] != null) currentStudentGroup = data['student']['group'];
            if (data['student']['id'] != null) {
              currentStudentId = data['student']['id'];
            }
          }
          return {'success': true, 'message': data['message'], 'student': data['student']};
        }
      }
      return {'success': false, 'message': data['message'] ?? 'Registration failed'};
    } catch (_) {
      // Offline resilient registration simulation
      registeredEmail = email;
      registeredPassword = password;
      final cleanRoll = rollNumber.replaceAll(RegExp(r'\D'), '');
      final rollInt = int.tryParse(cleanRoll) ?? 1;
      currentStudentId = 101;
      final instName = institutionName ?? 'ধানমন্ডি মডেল সরকারি প্রাথমিক বিদ্যালয়';
      final instType = institutionType ?? 'govt_primary_school';
      final assignedFather = fatherName ?? 'মাহমুদুল হাসান (Mahmudul Hasan)';
      final assignedMother = motherName ?? 'আমিনা বেগম (Amina Begum)';
      registeredStudentData = {
        'id': 101,
        'name': name,
        'father_name': assignedFather,
        'mother_name': assignedMother,
        'guardian_name': assignedFather,
        'email': email,
        'roll_number': rollInt,
        'admission_number': 'ADM-2026-C01-${cleanRoll.padLeft(3, '0')}',
        'class_name': className,
        'group': group,
        'academic_class': {'name': className, 'numeric_level': 1, 'group': group},
        'class': {'name': className, 'medium': 'bangla', 'group': group},
        'guardian': {
          'name': assignedFather,
          'father_name': assignedFather,
          'mother_name': assignedMother,
          'phone': '01711000000',
          'relation': 'Father (পিতা)',
        },
        'section': {'name': 'শাখা - ক (Section A)', 'room': 'Room 101', 'shift': 'morning'},
        'institution': {
          'name': instName,
          'type': instType,
          'type_name': allInstitutionTypes.firstWhere((t) => t['id'] == instType, orElse: () => allInstitutionTypes.first)['name_bn'],
          'management': 'পূর্ণাঙ্গ',
          'code': 'INST-01',
          'eiin': '107980',
        },
      };
      currentStudentData = registeredStudentData;
      return {'success': true, 'message': 'Registration successful', 'student': registeredStudentData};
    }
  }

  /// Student Login
  static Future<bool> studentLogin({required String identifier, required String password}) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/login'));
      request.headers.set('content-type', 'application/json');
      request.add(utf8.encode(jsonEncode({
        'identifier': identifier,
        'email': identifier,
        'password': password,
      })));
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        final data = jsonDecode(body);
        if (data['success'] == true) {
          currentAuthToken = data['token'];
          if (data['student'] != null) {
            currentStudentData = Map<String, dynamic>.from(data['student']);
            if (data['student']['group'] != null) {
              currentStudentGroup = data['student']['group'];
            }
            if (data['student']['id'] != null) {
              currentStudentId = data['student']['id'];
            }
          }
          return true;
        }
      }
    } catch (_) {}

    if (identifier.isNotEmpty && password.isNotEmpty) {
      currentAuthToken = 'mock-student-token';
      if (registeredStudentData != null &&
          (identifier == registeredEmail ||
              identifier == registeredStudentData!['admission_number'] ||
              identifier == registeredStudentData!['email'])) {
        currentStudentId = registeredStudentData!['id'];
        currentStudentData = registeredStudentData;
      }
      return true;
    }
    return false;
  }

  /// Student Logout
  static Future<bool> studentLogout() async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 2);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/logout'));
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      await request.close();
    } catch (_) {}

    currentAuthToken = null;
    return true;
  }

  /// Pay Student Fee
  static Future<Map<String, dynamic>> payStudentFee({
    required int feeId,
    required String method,
    required String accountNumber,
    int? studentId,
  }) async {
    final sid = studentId ?? currentStudentId;
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 3);
      final request = await client.postUrl(Uri.parse('$studentBaseUrl/fees/$feeId/pay?student_id=$sid'));
      request.headers.set('content-type', 'application/json');
      if (currentAuthToken != null) {
        request.headers.set('Authorization', 'Bearer $currentAuthToken');
      }
      request.add(utf8.encode(jsonEncode({
        'payment_method': method,
        'account_number': accountNumber,
      })));
      final response = await request.close();
      if (response.statusCode == 200) {
        final body = await response.transform(utf8.decoder).join();
        return jsonDecode(body);
      }
    } catch (_) {}

    final trxId = '${method[0]}TX${DateTime.now().millisecondsSinceEpoch.toString().substring(7)}';
    return {
      'success': true,
      'message': 'ফি সফলভাবে পরিশোধিত হয়েছে! ট্রানজ্যাকশন আইডি: $trxId',
      'transaction_id': trxId,
    };
  }

  // ==========================================
  // LOCAL FALLBACK DATA (Matching Image & DB)
  // ==========================================
  static Map<String, dynamic> _buildLocalStudentPortalData() {
    final sData = currentStudentData ?? registeredStudentData;
    final rawClass = (sData?['class_name'] ?? sData?['academic_class']?['name'] ?? sData?['class']?['name'] ?? '').toString().toLowerCase();
    final isClass1 = sData != null &&
        !rawClass.contains('10') &&
        !rawClass.contains('11') &&
        !rawClass.contains('12') &&
        !rawClass.contains('দশম') &&
        !rawClass.contains('একাদশ') &&
        !rawClass.contains('দ্বাদশ') &&
        (rawClass.contains('class 1') || rawClass.contains('প্রথম') || rawClass.contains('১ম') || rawClass == '1');

    final sName = sData?['name'] ?? (isClass1 ? 'আরিয়ান হাসান (Aaryan Hasan)' : 'Tanvir Ahmed');
    final sRoll = sData?['roll_number'] ?? 1;
    final sClass = isClass1 ? 'Class 1 (প্রথম শ্রেণি)' : (sData?['class_name'] ?? 'Class 11 (HSC Science)');
    final sAdm = sData?['admission_number'] ?? (isClass1 ? 'ADM-2026-C01-001' : 'DRMC-ADM-2024-001');
    final sSection = isClass1 ? 'শাখা - ক (Section A)' : 'Science A';

    final sFather = sData?['father_name'] ?? (isClass1 ? 'মাহমুদুল হাসান (Mahmudul Hasan)' : 'মোঃ রফিকুল ইসলাম (Md. Rafiqul Islam)');
    final sMother = sData?['mother_name'] ?? (isClass1 ? 'আমিনা বেগম (Amina Begum)' : 'নাসরিন সুলতানা (Nasrin Sultana)');

    return {
      'profile': {
        'id': sData?['id'] ?? 2,
        'name': sName,
        'father_name': sFather,
        'mother_name': sMother,
        'admission_number': sAdm,
        'roll_number': sRoll,
        'gender': sData?['gender'] ?? 'male',
        'dob': isClass1 ? '2019-01-15' : '2009-08-14',
        'blood_group': 'B+',
        'photo': isClass1
            ? 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=200'
            : 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200',
        'address': 'Dhanmondi, Dhaka',
        'status': 'active',
        'class': {'name': sClass, 'medium': 'bangla'},
        'section': {'name': sSection, 'room': isClass1 ? 'Room 101' : 'Room 301', 'shift': 'morning'},
        'institution': {
          'name': isClass1 ? 'ধানমন্ডি মডেল সরকারি প্রাথমিক বিদ্যালয়' : 'Dhaka Residential Model College',
          'code': isClass1 ? 'DGPS-101' : 'DRMC-101',
          'eiin': isClass1 ? '104123' : '108252',
          'type': isClass1 ? 'primary_school' : 'college',
          'address': 'Mirpur Road, Mohammadpur, Dhaka',
          'phone': '01711223344',
          'email': 'info@drmc.edu.bd',
        },
        'guardian': {
          'name': sFather,
          'father_name': sFather,
          'mother_name': sMother,
          'phone': '01711000000',
          'relation': 'Father',
        },
      },
      'dashboard': {
        'attendance': {
          'today_status': 'present',
          'today_remarks': 'RFID Gate Entry: 07:54 AM',
          'percentage': 96.4,
          'total_days': 28,
          'present_days': 27,
        },
        'results_summary': {
          'overall_percentage': isClass1 ? 95.0 : 58.0,
          'gpa': isClass1 ? 5.00 : 4.85,
          'letter_grade': 'A+',
        },
      },
      'homework': {
        'homeworks': isClass1
            ? [
                {
                  'id': 1,
                  'subject': 'বাংলা',
                  'title': 'বর্ণমালা ও সুন্দর হাতের লেখা অনুশীলন (পৃষ্ঠা ১০-১২)',
                  'description': 'আমার বাংলা বই থেকে পৃষ্ঠা ১০-১২ এর স্বরবর্ণ ও ব্যঞ্জনবর্ণ খাতায় সুন্দর করে লিখে আনবে।',
                  'due_date': '10/12/2026',
                  'total_marks': 20,
                  'status': 'due',
                  'color_theme': 'orange',
                },
                {
                  'id': 2,
                  'subject': 'English',
                  'title': 'Alphabet Tracing & Rhymes (Letters A to Z)',
                  'description': 'Practice writing uppercase and lowercase letters from English for Today textbook.',
                  'due_date': '11/12/2026',
                  'total_marks': 25,
                  'status': 'due',
                  'color_theme': 'blue',
                },
                {
                  'id': 3,
                  'subject': 'গণিত',
                  'title': '১ থেকে ১০ ছবি দেখে সংখ্যা গণনা ও মিলকরণ',
                  'description': 'প্রাথমিক গণিত বইয়ের প্রথম অধ্যায় থেকে ছবি গণনা করে খাতায় সংখ্যা লেখো।',
                  'due_date': '12/12/2026',
                  'total_marks': 25,
                  'status': 'due',
                  'color_theme': 'teal',
                },
              ]
            : [
                {
                  'id': 1,
                  'subject': 'English',
                  'title': 'Essay on Emerging AI in Bangladesh',
                  'description': 'Write 250 words with 5 comprehension answers.',
                  'due_date': '10/12/2026',
                  'total_marks': 25,
                  'status': 'due',
                  'color_theme': 'orange',
                },
                {
                  'id': 2,
                  'subject': 'Maths',
                  'title': 'Linear Equations & Matrix Inverse Practice',
                  'description': 'Solve Chapter 4 exercises 4.2 to 4.5.',
                  'due_date': '08/12/2026',
                  'total_marks': 30,
                  'status': 'overdue',
                  'color_theme': 'blue',
                },
                {
                  'id': 3,
                  'subject': 'Science',
                  'title': 'Chemistry Titration & Acid-Base Lab Notes',
                  'description': 'Complete calculations and indicator table in practical book.',
                  'due_date': '12/12/2026',
                  'total_marks': 20,
                  'status': 'due',
                  'color_theme': 'amber',
                },
                {
                  'id': 4,
                  'subject': 'Hindi / ICT',
                  'title': 'Relational Database Schema & SQL Queries',
                  'description': 'Design ER schema and write SQL table queries.',
                  'due_date': '15/12/2026',
                  'total_marks': 25,
                  'status': 'due',
                  'color_theme': 'teal',
                },
              ],
      },
      'routine': {
        'all_routines': isClass1
            ? [
                {'id': 1, 'subject': 'বাংলা (আমার বাংলা বই)', 'start_time': '08:30 AM', 'end_time': '09:15 AM', 'room': 'Room 101', 'teacher': {'name': 'সালমা আক্তার'}},
                {'id': 2, 'subject': 'English (English for Today)', 'start_time': '09:20 AM', 'end_time': '10:05 AM', 'room': 'Room 101', 'teacher': {'name': 'Mrs. Nasreen'}},
                {'id': 3, 'subject': 'টিফিন বিরতি ও খেলাধুলা', 'start_time': '10:05 AM', 'end_time': '10:35 AM', 'room': 'প্লে-গ্রাউন্ড', 'teacher': {'name': 'শারীরিক শিক্ষা শিক্ষক'}},
                {'id': 4, 'subject': 'প্রাথমিক গণিত', 'start_time': '10:35 AM', 'end_time': '11:20 AM', 'room': 'Room 101', 'teacher': {'name': 'মো: রফিকুল ইসলাম'}},
              ]
            : [
                {'id': 1, 'subject': 'Higher Mathematics', 'start_time': '08:00 AM', 'end_time': '08:45 AM', 'room': 'Room 301', 'teacher': {'name': 'Md. Rafiqul Islam'}},
                {'id': 2, 'subject': 'Physics', 'start_time': '08:50 AM', 'end_time': '09:35 AM', 'room': 'Lab 2', 'teacher': {'name': 'Dr. M. A. Karim'}},
                {'id': 3, 'subject': 'English', 'start_time': '09:40 AM', 'end_time': '10:25 AM', 'room': 'Room 301', 'teacher': {'name': 'Mr. John Doe'}},
                {'id': 4, 'subject': 'Chemistry', 'start_time': '11:00 AM', 'end_time': '11:45 AM', 'room': 'Lab 1', 'teacher': {'name': 'Prof. A. Rahman'}},
                {'id': 5, 'subject': 'ICT', 'start_time': '11:50 AM', 'end_time': '12:35 PM', 'room': 'Computer Lab', 'teacher': {'name': 'Engr. Shafi'}},
              ],
      },
      'exam_routine': {
        'exam_schedules': isClass1
            ? [
                {'id': 1, 'subject': 'আমার বাংলা বই', 'exam_date': '2026-11-25', 'start_time': '10:00 AM', 'end_time': '11:30 AM', 'room': 'Room 101', 'marks': 50},
                {'id': 2, 'subject': 'English for Today', 'exam_date': '2026-11-27', 'start_time': '10:00 AM', 'end_time': '11:30 AM', 'room': 'Room 101', 'marks': 50},
                {'id': 3, 'subject': 'প্রাথমিক গণিত', 'exam_date': '2026-11-30', 'start_time': '10:00 AM', 'end_time': '11:30 AM', 'room': 'Room 101', 'marks': 50},
              ]
            : [
                {'id': 1, 'subject': 'Chemistry 1', 'exam_date': '2026-11-25', 'start_time': '10:00 AM', 'end_time': '01:00 PM', 'room': 'Science Hall 401', 'marks': 100},
                {'id': 2, 'subject': 'Mathematics', 'exam_date': '2026-11-27', 'start_time': '10:00 AM', 'end_time': '01:00 PM', 'room': 'Main Exam Hall', 'marks': 100},
                {'id': 3, 'subject': 'Physics', 'exam_date': '2026-11-30', 'start_time': '10:00 AM', 'end_time': '01:00 PM', 'room': 'Science Hall 402', 'marks': 100},
                {'id': 4, 'subject': 'English', 'exam_date': '2026-12-02', 'start_time': '10:00 AM', 'end_time': '01:00 PM', 'room': 'Room 301', 'marks': 100},
                {'id': 5, 'subject': 'ICT & Computer Science', 'exam_date': '2026-12-05', 'start_time': '10:00 AM', 'end_time': '01:00 PM', 'room': 'Computer Lab 1', 'marks': 100},
              ],
      },
      'results': {
        'overall_percentage': isClass1 ? 95.0 : 58.0,
        'gpa': isClass1 ? 5.00 : 4.85,
        'terms': isClass1
            ? [
                {
                  'term_name': '1st Midterm',
                  'term_period': '15 Sep to 18 Sep',
                  'status_badge': 'Promoted',
                  'is_promoted': true,
                  'results': [
                    {'id': 1, 'subject': 'বাংলা', 'marks': 95.0, 'total_marks': 100, 'status': 'PASS', 'grade': 'A+', 'date': '28/08/2026'},
                    {'id': 2, 'subject': 'English', 'marks': 92.0, 'total_marks': 100, 'status': 'PASS', 'grade': 'A+', 'date': '28/08/2026'},
                    {'id': 3, 'subject': 'প্রাথমিক গণিত', 'marks': 98.0, 'total_marks': 100, 'status': 'PASS', 'grade': 'A+', 'date': '28/08/2026'},
                  ],
                },
              ]
            : [
                {
                  'term_name': '1st Midterm',
                  'term_period': '15 Sep to 18 Sep',
                  'status_badge': 'Promoted',
                  'is_promoted': true,
                  'results': [
                    {'id': 1, 'subject': 'Chemistry 1', 'marks': 65.0, 'total_marks': 100, 'status': 'PASS', 'grade': 'A-', 'date': '28/08/2026'},
                    {'id': 2, 'subject': 'Mathematics', 'marks': 56.0, 'total_marks': 100, 'status': 'PASS', 'grade': 'B', 'date': '28/08/2026'},
                    {'id': 3, 'subject': 'Physics', 'marks': 28.0, 'total_marks': 100, 'status': 'FAILED', 'grade': 'F', 'date': '28/08/2026'},
                    {'id': 4, 'subject': 'English', 'marks': 84.0, 'total_marks': 100, 'status': 'PASS', 'grade': 'A+', 'date': '28/08/2026'},
                  ],
                },
                {
                  'term_name': '2nd Midterm',
                  'term_period': '15 Sep to 18 Sep',
                  'status_badge': '3 Failed',
                  'is_promoted': false,
                  'results': [],
                },
                {
                  'term_name': '3rd Midterm',
                  'term_period': '15 Sep to 18 Sep',
                  'status_badge': 'Promoted',
                  'is_promoted': true,
                  'results': [],
                },
              ],
      },
      'messages': {
        'messages': [
          {
            'id': 1,
            'sender_name': 'মুহাম্মদ রফিকুল ইসলাম (সিনিয়র শিক্ষক)',
            'sender_role': 'teacher',
            'title': 'আন্তঃহাউস ক্রীড়া প্রতিযোগিতা',
            'message': 'তানভীর আহমেদ কলেজ ক্রিকেট টুর্নামেন্টে নাজির হাউস দলের অধিনায়ক নির্বাচিত হয়েছে।',
            'is_read': false,
            'created_at': '2026-11-18 10:00:00',
          },
          {
            'id': 2,
            'sender_name': 'Brig. Gen. Kazi Shameem (Principal)',
            'sender_role' : 'principal',
            'title': 'বার্ষিক বিজ্ঞান মেলা ও রোবোটিক্স উৎসব ২০২৬',
            'message': 'বিজ্ঞান মেলায় প্রজেক্ট জমা দেওয়ার শেষ তারিখ ২৫ নভেম্বর।',
            'is_read': false,
            'created_at': '2026-11-17 14:30:00',
          },
        ],
      },
      'notices': [
        {
          'id': 1,
          'title': '📢 সমগ্র বাংলাদেশে ই-শিক্ষা প্ল্যাটফর্মে জাতীয় শিক্ষা সপ্তাহ ২০২৬ নিবন্ধন শুরু',
          'content': 'মাধ্যমিক ও উচ্চশিক্ষা অধিদপ্তর (মাউশি) এবং মাদ্রাসা শিক্ষা বোর্ডের নির্দেশনানুযায়ী সকল শিক্ষা প্রতিষ্ঠানে ডিজিটাল শিক্ষা সপ্তাহ ২০২৬ পালনের আহ্বান জানানো হচ্ছে।',
          'published_at': '2026-11-18',
          'is_pinned': true,
        },
        {
          'id': 2,
          'title': '🏛️ কলেজ ক্যাম্পাসে বার্ষিক বিজ্ঞান মেলা ও রোবোটিক্স উৎসব ২০২৬',
          'content': 'আগামী ১৫ নভেম্বর থেকে তিন দিনব্যাপী বার্ষিক বিজ্ঞান মেলা ও রোবোটিক্স প্রদর্শনীর আয়োজন করা হয়েছে।',
          'published_at': '2026-11-18',
          'is_pinned': true,
        },
      ],
      'attendance': {
        'attendance_percentage': 96.4,
        'present_count': 27,
        'late_count': 1,
        'absent_count': 0,
        'attendances': [
          {'date': '2026-11-18', 'status': 'present', 'remarks': 'RFID Gate Entry: 07:54 AM'},
          {'date': '2026-11-17', 'status': 'present', 'remarks': 'RFID Gate Entry: 07:51 AM'},
          {'date': '2026-11-16', 'status': 'present', 'remarks': 'RFID Gate Entry: 07:55 AM'},
          {'date': '2026-11-15', 'status': 'late', 'remarks': 'RFID Gate Entry: 08:05 AM'},
          {'date': '2026-11-14', 'status': 'present', 'remarks': 'RFID Gate Entry: 07:49 AM'},
        ],
      },
    };
  }

  static AttendanceAnalytics _buildLocalAttendanceAnalytics() {
    return AttendanceAnalytics(
      studentId: 2,
      studentName: 'Tanvir Ahmed',
      today: TodayAttendance(
        isPresent: true,
        status: 'present',
        statusLabel: 'উপস্থিত (Present)',
        remarks: 'RFID Gate Entry: 07:54 AM',
        date: '2026-11-18',
      ),
      overallPercentage: 92.5,
      totalDays: 31,
      presentCount: 20,
      lateCount: 3,
      absentCount: 2,
      leaveCount: 6,
      absentDays: [
        AbsentDay(id: 1, date: '2026-11-02', formattedDate: '02 Nov, 2026', dayName: 'Monday', remarks: 'অনুপস্থিত (তীব্র মাথাব্যথা ও অসুস্থতা)'),
        AbsentDay(id: 2, date: '2026-10-14', formattedDate: '14 Oct, 2026', dayName: 'Wednesday', remarks: 'অনুপস্থিত (অনির্দিষ্ট অনুপস্থিতি / Uninformed)'),
      ],
      lateDays: [
        LateDay(id: 1, date: '2026-11-15', formattedDate: '15 Nov, 2026', dayName: 'Sunday', remarks: 'Gate Entry: 08:05 AM (বাস বিলম্ব - ৫ মিনিট দেরি)'),
        LateDay(id: 2, date: '2026-11-08', formattedDate: '08 Nov, 2026', dayName: 'Sunday', remarks: 'Gate Entry: 08:15 AM (বৃষ্টির কারণে বিলম্ব - ১৫ মিনিট দেরি)'),
        LateDay(id: 3, date: '2026-10-28', formattedDate: '28 Oct, 2026', dayName: 'Wednesday', remarks: 'Gate Entry: 08:25 AM (মিরপুর রোডে তীব্র যানজট - ২৫ মিনিট দেরি)'),
      ],
      monthlyHistory: [
        MonthlyAttendance(monthName: 'November 2026', totalDays: 16, presentCount: 11, lateCount: 2, absentCount: 1, leaveCount: 2, percentage: 81.3),
        MonthlyAttendance(monthName: 'October 2026', totalDays: 15, presentCount: 9, lateCount: 1, absentCount: 1, leaveCount: 4, percentage: 86.7),
      ],
      leavesHistory: [
        LeaveRecord(
          id: 1,
          leaveType: 'medical',
          title: 'চিকিৎসাজনিত ছুটি (Medical Leave - Viral Fever)',
          startDate: '2026-10-20',
          endDate: '2026-10-22',
          daysCount: 3,
          reason: 'Doctor prescribed complete rest and medication for viral fever.',
          status: 'approved',
          approvedBy: 'Brig. Gen. Kazi Shameem (Principal)',
        ),
        LeaveRecord(
          id: 2,
          leaveType: 'family',
          title: 'পারিবারিক অনুষ্ঠান ও সামাজিক ছুটি (Family Wedding)',
          startDate: '2026-11-05',
          endDate: '2026-11-06',
          daysCount: 2,
          reason: 'Elder sister wedding reception at village home.',
          status: 'approved',
          approvedBy: 'Muhammad Rafiqul Islam (Class Teacher)',
        ),
        LeaveRecord(
          id: 3,
          leaveType: 'holiday',
          title: 'জাতীয় শিক্ষা সপ্তাহ প্রস্তুতিমূলক ছুটি (Debate Competition)',
          startDate: '2026-11-12',
          endDate: '2026-11-12',
          daysCount: 1,
          reason: 'Representing College in District Level Inter-College Debate Final.',
          status: 'approved',
          approvedBy: 'Academic Coordinator',
        ),
      ],
    );
  }

  static AcademicDetails _buildLocalAcademicDetails({int studentId = 2}) {
    final mathsInternal = [
      InternalAssessmentItem(name: 'Assignment 1', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 85.0),
      InternalAssessmentItem(name: 'Assignment 2', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 85.0),
      InternalAssessmentItem(name: 'Assignment 3', marksObtained: 4, totalMarks: 5, display: '4/5', attendanceRate: 85.0),
      InternalAssessmentItem(name: 'Assignment 4', marksObtained: 3, totalMarks: 5, display: '3/5', attendanceRate: 85.0),
      InternalAssessmentItem(name: 'Sessional 1', marksObtained: 13, totalMarks: 15, display: '13/15', attendanceRate: 85.0),
      InternalAssessmentItem(name: 'Sessional 2', marksObtained: 11, totalMarks: 15, display: '11/15', attendanceRate: 85.0),
    ];

    final mosInternal = [
      InternalAssessmentItem(name: 'Assignment 1', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 95.0),
      InternalAssessmentItem(name: 'Assignment 2', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 95.0),
      InternalAssessmentItem(name: 'Assignment 3', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 95.0),
      InternalAssessmentItem(name: 'Assignment 4', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 95.0),
      InternalAssessmentItem(name: 'Sessional 1', marksObtained: 15, totalMarks: 15, display: '15/15', attendanceRate: 95.0),
      InternalAssessmentItem(name: 'Sessional 2', marksObtained: 14, totalMarks: 15, display: '14/15', attendanceRate: 95.0),
    ];

    final subjects = [
      AcademicSubjectMark(
        id: 1,
        subject: 'Mathematics III',
        credits: 4,
        totalMarks: 100,
        internalMarks: 38,
        theoryMarks: 40,
        practicalMarks: 0,
        obtainedMarks: 78,
        highestMarks: 95,
        grade: 'A',
        gradePoint: 4.0,
        attendanceRate: 85.0,
        status: 'PASS',
        internalItems: mathsInternal,
      ),
      AcademicSubjectMark(
        id: 2,
        subject: 'MOS',
        credits: 3,
        totalMarks: 100,
        internalMarks: 49,
        theoryMarks: 43,
        practicalMarks: 0,
        obtainedMarks: 92,
        highestMarks: 96,
        grade: 'A+',
        gradePoint: 5.0,
        attendanceRate: 95.0,
        status: 'PASS',
        internalItems: mosInternal,
      ),
      AcademicSubjectMark(
        id: 3,
        subject: 'BME',
        credits: 3,
        totalMarks: 100,
        internalMarks: 37,
        theoryMarks: 33,
        practicalMarks: 0,
        obtainedMarks: 70,
        highestMarks: 88,
        grade: 'B',
        gradePoint: 3.0,
        attendanceRate: 82.0,
        status: 'PASS',
        internalItems: [],
      ),
      AcademicSubjectMark(
        id: 4,
        subject: 'BE',
        credits: 3,
        totalMarks: 100,
        internalMarks: 40,
        theoryMarks: 40,
        practicalMarks: 0,
        obtainedMarks: 80,
        highestMarks: 90,
        grade: 'A',
        gradePoint: 4.0,
        attendanceRate: 90.0,
        status: 'PASS',
        internalItems: [],
      ),
      AcademicSubjectMark(
        id: 5,
        subject: 'Physics',
        credits: 3,
        totalMarks: 100,
        internalMarks: 38,
        theoryMarks: 30,
        practicalMarks: 0,
        obtainedMarks: 68,
        highestMarks: 85,
        grade: 'B',
        gradePoint: 3.0,
        attendanceRate: 88.0,
        status: 'PASS',
        internalItems: [],
      ),
      AcademicSubjectMark(
        id: 6,
        subject: 'English',
        credits: 3,
        totalMarks: 100,
        internalMarks: 42,
        theoryMarks: 42,
        practicalMarks: 0,
        obtainedMarks: 84,
        highestMarks: 88,
        grade: 'A',
        gradePoint: 4.0,
        attendanceRate: 92.0,
        status: 'PASS',
        internalItems: [],
      ),
      AcademicSubjectMark(
        id: 7,
        subject: 'EG',
        credits: 3,
        totalMarks: 100,
        internalMarks: 46,
        theoryMarks: 48,
        practicalMarks: 0,
        obtainedMarks: 94,
        highestMarks: 98,
        grade: 'A+',
        gradePoint: 5.0,
        attendanceRate: 98.0,
        status: 'PASS',
        internalItems: [],
      ),
      AcademicSubjectMark(
        id: 8,
        subject: 'Workshop',
        credits: 1,
        totalMarks: 100,
        internalMarks: 43,
        theoryMarks: 42,
        practicalMarks: 0,
        obtainedMarks: 85,
        highestMarks: 90,
        grade: 'A',
        gradePoint: 4.0,
        attendanceRate: 90.0,
        status: 'PASS',
        internalItems: [],
      ),
    ];

    return AcademicDetails(
      student: {
        'id': studentId,
        'name': 'Tanvir Ahmed',
        'roll_number': 1,
        'admission_number': 'DRMC-ADM-2024-001',
        'class_name': 'Class 11 (HSC Science 1st Year)',
        'institution_name': 'Dhaka Residential Model College',
      },
      examName: '1st Midterm Examination 2026',
      examId: 2,
      examDate: '15 Sep to 18 Sep',
      startDate: '15 Sep, 2026',
      endDate: '18 Sep, 2026',
      sessionYear: '2026',
      subjectMarks: subjects,
      totalMarksPossible: 800,
      totalMarksObtained: 658,
      grade: 'A+',
      gpa: 4.15,
      cgpa: 8.6,
      gpaCgpaLabel: 'GPA: 4.15 / 5.00 • CGPA: 8.6',
      totalMarks: '658 / 800 (82.25%)',
      percentage: 82.25,
      meritPosition: AcademicMeritPosition(
        sectionRank: 1,
        classRank: 3,
        totalStudentsSection: 45,
        totalStudentsClass: 180,
        badge: '১ম স্থান (Section Rank: 1/45)',
        description: 'সেকশনে ১ম স্থান এবং সার্বিক ক্লাসে ৩য় স্থান অধিকার করেছে।',
      ),
      status: 'promoted',
      remarks: 'অসাধারণ ধারাবাহিক ফলাফল! সকল অভ্যন্তরীণ ও তত্ত্বীয় বিষয়ে মেধার স্বাক্ষর রেখেছে।',
      pdfMarksheetUrl: 'http://localhost:8000/api/student/marksheet/2/pdf?student_id=$studentId',
    );
  }

  static Map<String, dynamic> _buildLocalStudyData() {
    final sData = currentStudentData ?? registeredStudentData;
    final rawClass = (sData?['class_name'] ?? sData?['academic_class']?['name'] ?? sData?['class']?['name'] ?? '').toString().toLowerCase();
    final isClass1 = sData != null &&
        !rawClass.contains('10') &&
        !rawClass.contains('11') &&
        !rawClass.contains('12') &&
        !rawClass.contains('দশম') &&
        !rawClass.contains('একাদশ') &&
        !rawClass.contains('দ্বাদশ') &&
        (rawClass.contains('class 1') || rawClass.contains('প্রথম') || rawClass.contains('১ম') || rawClass == '1');

    if (isClass1) {
      return {
        'summary': {
          'total_subjects': 3,
          'due_homework_count': 2,
          'overdue_homework_count': 0,
          'submitted_homework_count': 1,
          'practicals_count': 3,
          'today_classes_count': 3,
        },
        'subjects': [
          {
            'id': 1,
            'name': 'Amar Bangla Boi',
            'bangla_name': 'আমার বাংলা বই (১ম শ্রেণি)',
            'code': 'BAN-101',
            'chapters_count': 8,
            'completed_chapters': 5,
            'progress': 65,
            'teacher_name': 'মিসেস রুমানা পারভীন',
            'teacher_designation': 'সহকারী শিক্ষক (প্রাথমিক শাখা)',
            'room': 'Room 101',
            'color_theme': 'emerald',
            'icon': 'menu_book_rounded',
            'materials_count': 12,
            'topics': ['বর্ণমালা শেখা (অ-ঔ, ক-ঁ)', 'ইচ্ছে মতো আঁকি', 'ছড়া: আতা গাছে তোতা পাখি', 'ছোট গল্প ও বাক্য গঠন'],
          },
          {
            'id': 2,
            'name': 'English for Today',
            'bangla_name': 'English for Today (Class 1)',
            'code': 'ENG-101',
            'chapters_count': 6,
            'completed_chapters': 4,
            'progress': 68,
            'teacher_name': 'সালমা জাহান',
            'teacher_designation': 'সহকারী শিক্ষক',
            'room': 'Room 101',
            'color_theme': 'blue',
            'icon': 'school_rounded',
            'materials_count': 10,
            'topics': ['Alphabet (A to Z)', 'Greetings & Farewells', 'Numbers 1 to 10', 'Rhymes & Action Songs'],
          },
          {
            'id': 3,
            'name': 'Elementary Mathematics',
            'bangla_name': 'প্রাথমিক গণিত (১ম শ্রেণি)',
            'code': 'MATH-101',
            'chapters_count': 7,
            'completed_chapters': 4,
            'progress': 60,
            'teacher_name': 'মোঃ জহিরুল হক',
            'teacher_designation': 'সহকারী শিক্ষক',
            'room': 'Room 101',
            'color_theme': 'orange',
            'icon': 'calculate_rounded',
            'materials_count': 14,
            'topics': ['তুলনা করি (কম-বেশি, ছোট-বড়)', 'সংখ্যা গণনা (১-২০)', 'যোগের প্রাথমিক ধারণা', 'জ্যামিতিক আকৃতি পরিচিতি'],
          },
        ],
        'homeworks': [
          {
            'id': 1,
            'subject': 'আমার বাংলা বই',
            'title': 'বর্ণমালা সুন্দর করে লেখা',
            'description': 'খাতায় ক থেকে ঙ পর্যন্ত সুন্দর হস্তাক্ষরে লিখে আনবে।',
            'due_date': 'আগামীকাল',
            'total_marks': 10,
            'status': 'due',
            'color_theme': 'emerald',
          },
          {
            'id': 2,
            'subject': 'English for Today',
            'title': 'Capital & Small Letters (A-E)',
            'description': 'Trace and write capital and small letters A to E in activity book.',
            'due_date': 'পরশু',
            'total_marks': 10,
            'status': 'due',
            'color_theme': 'blue',
          },
        ],
        'practicals': [
          {
            'id': 1,
            'title': 'হাতের সুন্দর লেখা ও বর্ণমালা আঁকা (Handwriting Lab)',
            'bangla_title': 'হাতের সুন্দর লেখা ও বর্ণমালা অনুশীলন',
            'subject': 'Bengali & English',
            'experiments_count': 8,
            'lab_room': 'Activity Center Room 101',
            'icon': 'brush_rounded',
            'color_theme': 'teal',
          },
          {
            'id': 2,
            'title': 'রং চেনা ও আকৃতি মেলানো (Colors & Shapes Matching)',
            'bangla_title': 'রং চেনা ও আকৃতি মেলানো ল্যাব',
            'subject': 'Maths & Art',
            'experiments_count': 6,
            'lab_room': 'Kindergarten Play Lab',
            'icon': 'extension_rounded',
            'color_theme': 'indigo',
          },
        ],
      };
    }

    return {
      'summary': {
        'total_subjects': 6,
        'due_homework_count': 3,
        'overdue_homework_count': 1,
        'submitted_homework_count': 1,
        'practicals_count': 4,
        'today_classes_count': 5,
      },
      'subjects': [
        {
          'id': 1,
          'name': 'Higher Mathematics',
          'bangla_name': 'উচ্চতর গণিত',
          'code': 'HM-265',
          'chapters_count': 10,
          'completed_chapters': 7,
          'progress': 70,
          'teacher_name': 'মুহাম্মদ রফিকুল ইসলাম',
          'teacher_designation': 'সিনিয়র সহকারী শিক্ষক',
          'room': 'Room 301',
          'color_theme': 'blue',
          'icon': 'calculate_rounded',
          'materials_count': 14,
          'topics': ['Matrix & Determinants', 'Vectors', 'Differentiation', 'Integration', 'Coordinate Geometry'],
        },
        {
          'id': 2,
          'name': 'Physics',
          'bangla_name': 'পদার্থবিজ্ঞান',
          'code': 'PHY-174',
          'chapters_count': 10,
          'completed_chapters': 6,
          'progress': 60,
          'teacher_name': 'ড. আহসান হাবীব',
          'teacher_designation': 'সহযোগী অধ্যাপক',
          'room': 'Lab 2',
          'color_theme': 'orange',
          'icon': 'bolt_rounded',
          'materials_count': 12,
          'topics': ['Vector Mechanics', 'Work, Power & Energy', 'Gravitation & Gravity'],
        },
        {
          'id': 3,
          'name': 'Chemistry',
          'bangla_name': 'রসায়ন',
          'code': 'CHEM-176',
          'chapters_count': 8,
          'completed_chapters': 5,
          'progress': 62,
          'teacher_name': 'মোঃ মোখলেসুর রহমান',
          'teacher_designation': 'সহকারী অধ্যাপক',
          'room': 'Lab 1',
          'color_theme': 'emerald',
          'icon': 'science_rounded',
          'materials_count': 10,
          'topics': ['Qualitative Chemistry', 'Periodic Table', 'Chemical Changes'],
        },
        {
          'id': 4,
          'name': 'English',
          'bangla_name': 'ইংরেজি',
          'code': 'ENG-107',
          'chapters_count': 12,
          'completed_chapters': 9,
          'progress': 75,
          'teacher_name': 'মিসেস ফারহানা আক্তার',
          'teacher_designation': 'প্রভাষক',
          'room': 'Room 301',
          'color_theme': 'purple',
          'icon': 'menu_book_rounded',
          'materials_count': 16,
          'topics': ['Reading Skills', 'Grammar in Context', 'Writing Formal Letters & Essays'],
        },
        {
          'id': 5,
          'name': 'ICT',
          'bangla_name': 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)',
          'code': 'ICT-275',
          'chapters_count': 6,
          'completed_chapters': 5,
          'progress': 83,
          'teacher_name': 'প্রকৌশলী তানভীর আহমেদ',
          'teacher_designation': 'আইসিটি ইনস্ট্রাক্টর',
          'room': 'Computer Lab',
          'color_theme': 'teal',
          'icon': 'computer_rounded',
          'materials_count': 18,
          'topics': ['Communication Systems', 'HTML & Web Design', 'C Programming', 'Database'],
        },
        {
          'id': 6,
          'name': 'Biology',
          'bangla_name': 'জীববিজ্ঞান',
          'code': 'BIO-178',
          'chapters_count': 12,
          'completed_chapters': 7,
          'progress': 58,
          'teacher_name': 'ড. নাসরিন জাহান',
          'teacher_designation': 'সহকারী অধ্যাপক',
          'room': 'Biology Lab',
          'color_theme': 'indigo',
          'icon': 'biotech_rounded',
          'materials_count': 11,
          'topics': ['Cell and its Structure', 'Cell Division', 'Plant Physiology'],
        },
      ],
      'practicals': [
        {
          'id': 1,
          'title': 'Chemistry Titration & Acid-Base Practical',
          'bangla_title': 'রসায়ন এসিড-ক্ষার টাইট্রেশন ল্যাব',
          'subject': 'Chemistry',
          'experiments_count': 12,
          'lab_room': 'Chemistry Lab 1',
          'icon': 'science_rounded',
          'color_theme': 'emerald',
        },
        {
          'id': 2,
          'title': 'Physics Screw Gauge & Spherometer Lab',
          'bangla_title': 'পদার্থবিজ্ঞান স্লাইড ক্যালিপার্স ও ভার্নিয়ার ল্যাব',
          'subject': 'Physics',
          'experiments_count': 10,
          'lab_room': 'Physics Lab 2',
          'icon': 'bolt_rounded',
          'color_theme': 'amber',
        },
        {
          'id': 3,
          'title': 'C Programming & Web Dev Practical Lab',
          'bangla_title': 'সি প্রোগ্রামিং ও এইচটিএমএল ল্যাব',
          'subject': 'ICT',
          'experiments_count': 15,
          'lab_room': 'Central Computer Lab',
          'icon': 'computer_rounded',
          'color_theme': 'teal',
        },
        {
          'id': 4,
          'title': 'Plant Tissue Identification & Cell Slide Lab',
          'bangla_title': 'উদ্ভিদ টিস্যু ও কোষ স্লাইড পর্যবেক্ষণ',
          'subject': 'Biology',
          'experiments_count': 8,
          'lab_room': 'Biology Lab',
          'icon': 'biotech_rounded',
          'color_theme': 'indigo',
        },
      ],
      'homeworks': [
        {
          'id': 1,
          'subject': 'English',
          'title': 'Essay on Emerging AI in Bangladesh',
          'description': 'Write 250 words with 5 comprehension answers.',
          'due_date': '10/12/2026',
          'total_marks': 25,
          'status': 'due',
          'color_theme': 'orange',
        },
        {
          'id': 2,
          'subject': 'Maths',
          'title': 'Linear Equations & Matrix Inverse Practice Set',
          'description': 'Complete exercises 4.2 to 4.5 from textbook.',
          'due_date': '12/12/2026',
          'total_marks': 30,
          'status': 'overdue',
          'color_theme': 'blue',
        },
        {
          'id': 3,
          'subject': 'Science',
          'title': 'Chemistry Titration & Acid-Base Practical Notes',
          'description': 'Record chemical indicators and calculations.',
          'due_date': '15/12/2026',
          'total_marks': 20,
          'status': 'due',
          'color_theme': 'amber',
        },
        {
          'id': 4,
          'subject': 'ICT',
          'title': 'Relational Database Management & SQL Queries',
          'description': 'Write SQL statements for student enrollment.',
          'due_date': '18/12/2026',
          'total_marks': 25,
          'status': 'due',
          'color_theme': 'teal',
        },
      ],
      'routines': [
        {'id': 1, 'subject': 'Higher Maths', 'start_time': '08:00 AM', 'end_time': '08:45 AM', 'room': 'Room 301', 'teacher': {'name': 'মুহাম্মদ রফিকুল ইসলাম'}},
        {'id': 2, 'subject': 'Physics', 'start_time': '08:50 AM', 'end_time': '09:35 AM', 'room': 'Lab 2', 'teacher': {'name': 'ড. আহসান হাবীব'}},
        {'id': 3, 'subject': 'English', 'start_time': '09:40 AM', 'end_time': '10:25 AM', 'room': 'Room 301', 'teacher': {'name': 'মিসেস ফারহানা আক্তার'}},
        {'id': 4, 'subject': 'Chemistry', 'start_time': '11:00 AM', 'end_time': '11:45 AM', 'room': 'Lab 1', 'teacher': {'name': 'মোঃ মোখলেসুর রহমান'}},
        {'id': 5, 'subject': 'ICT', 'start_time': '11:50 AM', 'end_time': '12:35 PM', 'room': 'Computer Lab', 'teacher': {'name': 'প্রকৌশলী তানভীর আহমেদ'}},
      ],
    };
  }

  static Map<String, dynamic> _buildLocalNavigationHub({int studentId = 2}) {
    final study = _buildLocalStudyData();
    final attendance = _buildLocalAttendanceAnalytics();
    final academic = _buildLocalAcademicDetails(studentId: studentId);
    final portal = _buildLocalStudentPortalData();

    return {
      'success': true,
      'buttons': {
        'home': {
          'id': 'home',
          'title': 'Home',
          'bangla_title': 'হোম',
          'icon': 'home_filled',
          'badge': null,
          'summary': {
            'greeting': 'Good Morning!',
            'student_name': 'Tanvir Ahmed',
            'banner_title': 'SPECIAL Coaching Class Started!',
            'today_attendance': 'present',
            'unread_messages': 2,
            'pending_homework': 3,
          },
        },
        'study': {
          'id': 'study',
          'title': 'Study',
          'bangla_title': 'স্টাডি',
          'icon': 'auto_stories_rounded',
          'badge': '3',
          'summary': study['summary'],
        },
        'attendance': {
          'id': 'attendance',
          'title': 'Attendance',
          'bangla_title': 'উপস্থিতি',
          'icon': 'how_to_reg_rounded',
          'badge': null,
          'summary': {
            'today_status': 'present',
            'percentage': attendance.overallPercentage,
            'absent_count': attendance.absentCount,
            'late_count': attendance.lateCount,
            'leave_count': attendance.leaveCount,
          },
        },
        'results': {
          'id': 'results',
          'title': 'Results',
          'bangla_title': 'ফলাফল',
          'icon': 'assessment_outlined',
          'badge': 'A+',
          'summary': {
            'latest_exam': academic.examName,
            'overall_percentage': academic.percentage,
            'gpa': academic.gpa,
            'cgpa': academic.cgpa,
            'letter_grade': academic.grade,
            'section_rank': academic.meritPosition.sectionRank,
            'class_rank': academic.meritPosition.classRank,
            'pdf_url': academic.pdfMarksheetUrl,
          },
        },
        'profile': {
          'id': 'profile',
          'title': 'Profile',
          'bangla_title': 'প্রোফাইল',
          'icon': 'person_outline',
          'badge': null,
          'summary': {
            'name': 'Tanvir Ahmed',
            'roll': 1,
            'admission_number': 'DRMC-ADM-2024-001',
            'class_name': 'Class 11 (HSC Science)',
            'institution_name': 'Dhaka Residential Model College',
          },
        },
      },
      'home_data': portal['dashboard'],
      'study_data': study,
      'attendance_data': attendance,
      'results_data': academic,
      'profile_data': portal['profile'],
    };
  }

  static Map<String, dynamic> _buildLocalProfileHub({int studentId = 2}) {
    final sData = currentStudentData ?? registeredStudentData;
    final rawClass = (sData?['class_name'] ?? sData?['academic_class']?['name'] ?? sData?['class']?['name'] ?? '').toString().toLowerCase();
    final isClass1 = sData != null &&
        !rawClass.contains('10') &&
        !rawClass.contains('11') &&
        !rawClass.contains('12') &&
        !rawClass.contains('দশম') &&
        !rawClass.contains('একাদশ') &&
        !rawClass.contains('দ্বাদশ') &&
        (rawClass.contains('class 1') || rawClass.contains('প্রথম') || rawClass.contains('১ম') || rawClass == '1');

    final sName = sData?['name'] ?? (isClass1 ? 'আরিয়ান হাসান (Aaryan Hasan)' : 'Tanvir Ahmed');
    final sFather = sData?['father_name'] ?? (isClass1 ? 'মাহমুদুল হাসান (Mahmudul Hasan)' : 'মোঃ রফিকুল ইসলাম (Md. Rafiqul Islam)');
    final sMother = sData?['mother_name'] ?? (isClass1 ? 'আমিনা বেগম (Amina Begum)' : 'নাসরিন সুলতানা (Nasrin Sultana)');
    final sRoll = sData?['roll_number'] ?? 1;
    final sClass = isClass1 ? 'Class 1 (প্রথম শ্রেণি)' : (sData?['class_name'] ?? 'Class 11 (HSC Science)');
    final sAdm = sData?['admission_number'] ?? (isClass1 ? 'ADM-2026-C01-001' : 'DRMC-ADM-2024-001');
    final sSection = isClass1 ? 'শাখা - ক (Section A)' : 'Science A';
    final sEmail = sData?['email'] ?? registeredEmail ?? 'tanvir.ahmed@drmc.edu.bd';

    return {
      'success': true,
      'personal_info': {
        'id': sData?['id'] ?? studentId,
        'name': sName,
        'bangla_name': sName,
        'father_name': sFather,
        'mother_name': sMother,
        'admission_number': sAdm,
        'roll_number': sRoll,
        'gender': sData?['gender'] ?? 'male',
        'dob': isClass1 ? '2019-01-15' : '2008-01-15',
        'formatted_dob': isClass1 ? '15 Jan, 2019' : '15 Jan, 2008',
        'blood_group': 'A+',
        'religion': 'Islam (ইসলাম)',
        'address': 'House 42, Road 9A, Dhanmondi, Dhaka - 1209',
        'phone': '01712-345678',
        'email': sEmail,
        'emergency_contact': '01711000000',
        'status': 'active',
        'photo': isClass1
            ? 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=200'
            : 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200',
      },
      'student_id': {
        'card_number': 'DRMC-STU-2026-0001',
        'admission_number': sAdm,
        'roll_number': sRoll,
        'class_name': sClass,
        'section_name': sSection,
        'room_number': isClass1 ? 'Room 101' : 'Room 301',
        'session': '2025-2026',
        'shift': 'Morning (প্রভাতী)',
        'barcode': 'DRMC20260001',
        'qr_data': 'ESHIKKHA:STUDENT:$studentId:$sAdm',
        'issued_date': '01 Jan 2026',
        'valid_until': '31 Dec 2026',
        'rfid_chip_id': 'RFID-DRMC-98124',
      },
      'student': {
        'id': sData?['id'] ?? studentId,
        'name': sName,
        'father_name': sFather,
        'mother_name': sMother,
        'admission_number': sAdm,
        'roll_number': sRoll.toString(),
        'class_name': sClass,
        'section_name': sSection,
        'date_of_birth': isClass1 ? '2019-01-15' : '2008-01-15',
        'blood_group': 'A+',
        'gender': 'male',
        'religion': 'Islam',
        'address': 'Dhaka, Bangladesh',
        'phone': '01711000000',
        'email': sEmail,
        'emergency_contact': '01811000000',
      },
      'institution_info': {
        'id': 1,
        'name': 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ',
        'name_en': 'Dhaka Residential Model College (DRMC)',
        'eiin': '108252',
        'code': 'DRMC-101',
        'type': 'college',
        'campus': 'Mirpur Road, Mohammadpur, Dhaka - 1207',
        'principal_name': 'Brigadier General Kazi Shameem Farhad',
        'phone': '+880 2-9118322',
        'email': 'principal@drmc.edu.bd',
        'website': 'https://drmc.edu.bd',
        'established': '1960',
      },
      'guardian_info': {
        'name': sFather,
        'father_name': sFather,
        'mother_name': sMother,
        'relation': 'Father (পিতা)',
        'phone': '01711000000',
        'occupation': 'ব্যবসায়ী (Businessman)',
        'address': 'ধানমন্ডি, ঢাকা',
        'nid': '1975269123456789',
      },
      'fees_and_payments': {
        'total_payable': 18500.0,
        'total_paid': 15000.0,
        'due_amount': 3500.0,
        'status': 'due',
        'status_label': 'বকেয়া ফি আছে (Due)',
        'last_payment_date': '12 Oct 2026',
        'last_transaction_id': 'BKTRX987654',
        'recent_history': [
          {
            'id': 1,
            'title': 'November Tuition Fee',
            'amount': 2500.0,
            'due_date': '10 Nov 2026',
            'status': 'due',
            'payment_method': null,
            'transaction_id': null,
          },
          {
            'id': 2,
            'title': 'Physics & Chemistry Lab Fee',
            'amount': 1000.0,
            'due_date': '15 Nov 2026',
            'status': 'due',
            'payment_method': null,
            'transaction_id': null,
          },
          {
            'id': 3,
            'title': 'October Tuition Fee',
            'amount': 2500.0,
            'due_date': '10 Oct 2026',
            'status': 'paid',
            'payment_method': 'bKash',
            'transaction_id': 'BKTX874512',
          },
        ],
      },
      'notifications': {
        'unread_count': 3,
        'items': [
          {
            'id': 101,
            'title': 'আগামী ২০ নভেম্বর অভিভাবক সমাবেশ',
            'content': 'কলেজ অডিটোরিয়ামে সকাল ১০:০০ টায় একাদশ শ্রেণির অভিভাবক সমাবেশ অনুষ্ঠিত হবে।',
            'published_at': '18 Nov 2026, 04:30 PM',
            'is_urgent': true,
          },
          {
            'id': 102,
            'title': 'মডেল টেস্ট পরীক্ষার সময়সূচি প্রকাশ',
            'content': 'সকল বিষয়ের পূর্ণাঙ্গ পরীক্ষার সময়সূচি রুটিন সেকশনে দেখা যাবে।',
            'published_at': '15 Nov 2026, 11:00 AM',
            'is_urgent': false,
          },
          {
            'id': 103,
            'title': 'শীতকালীন ছুটির নোটিশ',
            'content': '২৩ ডিসেম্বর হতে ৩১ ডিসেম্বর পর্যন্ত অ্যাকাডেমিক ক্লাস স্থগিত থাকবে।',
            'published_at': '10 Nov 2026, 09:15 AM',
            'is_urgent': false,
          },
        ],
        'preferences': {
          'push_notifications': true,
          'sms_alerts': true,
          'email_reports': true,
        },
      },
      'messages': {
        'unread_count': 2,
        'items': [
          {
            'id': 201,
            'teacher_name': 'মুহাম্মদ রফিকুল ইসলাম',
            'designation': 'সিনিয়র শিক্ষক (উচ্চতর গণিত)',
            'subject': 'Higher Mathematics Homework Revision',
            'message': 'ক্যালকুলাস ও ভেক্টর অংশের হোমওয়ার্ক আগামী সোমবারের মধ্যে সম্পন্ন করে জমা দিতে হবে।',
            'is_read': false,
            'sent_at': 'Today, 10:15 AM',
          },
          {
            'id': 202,
            'teacher_name': 'ড. আহসান হাবীব',
            'designation': 'সহযোগী অধ্যাপক (পদার্থবিজ্ঞান)',
            'subject': 'Physics Lab Practical Guidelines',
            'message': 'ল্যাব খাতায় ভার্নিয়ার স্কেলের রিডিং সুন্দরভাবে লিখে স্বাক্ষর করিয়ে নিবে।',
            'is_read': false,
            'sent_at': 'Yesterday, 02:40 PM',
          },
        ],
      },
      'settings': {
        'current_language': 'bn',
        'languages': [
          {'code': 'bn', 'name': 'বাংলা (Bengali)'},
          {'code': 'en', 'name': 'English (International)'},
        ],
        'theme': 'light',
        'biometric_auth': true,
        'app_version': 'v2.4.0 (Smart Bangladesh)',
      },
      'help_and_support': {
        'hotline': '16300',
        'hotline_label': 'জাতীয় শিক্ষা হেল্পলাইন (National Education Helpline)',
        'support_phone': '+880 9612345678',
        'support_email': 'support@eshikkha.gov.bd',
        'website': 'https://eshikkha.gov.bd',
        'support_hours': '24/7 (২৪ ঘণ্টা সেবা)',
        'faqs': [
          {
            'question': 'কীভাবে অনলাইনে বকেয়া ফি পরিশোধ করব?',
            'answer': 'প্রোফাইল থেকে "ফি ও পেমেন্ট" অপশনে গিয়ে "Pay Online" বাটনে ক্লিক করে bKash, Nagad বা Rocket এর মাধ্যমে তাৎক্ষণিক ফি প্রদান করা যায়।',
          },
          {
            'question': 'ডিজিটাল স্টুডেন্ট আইডি কার্ড কীভাবে প্রদর্শন করব?',
            'answer': 'প্রোফাইল থেকে "Student ID" কার্ডে ট্যাপ করলে ফুলস্ক্রিন ডিজিটাল আইডি কার্ড ও কিউআর কোড দেখা যাবে।',
          },
          {
            'question': 'ছুটির জন্য কীভাবে আবেদন করতে পারি?',
            'answer': 'Attendance ট্যাব থেকে "Apply for Leave" বাটনে ক্লিক করে ছুটির কারণ ও তারিখ নির্বাচন করে আবেদন জমা দেওয়া যাবে।',
          },
        ],
        'privacy_policy_url': 'https://eshikkha.gov.bd/privacy',
        'terms_url': 'https://eshikkha.gov.bd/terms',
      },
      'logout': {
        'endpoint': '/api/student/logout',
        'confirmation_title': 'লগআউট নিশ্চিতকরণ (Confirm Logout)',
        'confirmation_message': 'আপনি কি নিশ্চিত যে আপনি শিক্ষার্থী অ্যাকাউন্ট থেকে লগআউট করতে চান?',
      },
    };
  }
}
