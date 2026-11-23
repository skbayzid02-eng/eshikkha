import 'dart:convert';
import 'dart:io';

class TeacherApiService {
  static const String baseUrl = 'http://localhost:8000/api/teacher';

  static String? authToken;
  static Map<String, dynamic>? currentTeacher;

  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$baseUrl/login'));
      request.headers.set('content-type', 'application/json');
      request.headers.set('accept', 'application/json');
      request.add(utf8.encode(jsonEncode({
        'email': email,
        'password': password,
      })));

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      final data = jsonDecode(body);

      if (response.statusCode == 200 && data['success'] == true) {
        authToken = data['token'];
        currentTeacher = data['teacher'];
        return {'success': true, 'teacher': data['teacher']};
      }
      return {'success': false, 'message': data['message'] ?? 'লগইন ব্যর্থ হয়েছে'};
    } catch (e) {
      return {'success': false, 'message': 'সার্ভারে সংযোগ ব্যর্থ: $e'};
    }
  }

  static Future<Map<String, dynamic>> getDashboard() async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.getUrl(Uri.parse('$baseUrl/dashboard'));
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      final data = jsonDecode(body);

      if (response.statusCode == 200 && data['success'] == true) {
        return data;
      }
      return {'success': false};
    } catch (e) {
      return {'success': false, 'error': e.toString()};
    }
  }

  static Future<List<Map<String, dynamic>>> getStudents({int? classId, int? sectionId}) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      String url = '$baseUrl/students';
      final params = <String>[];
      if (classId != null) params.add('academic_class_id=$classId');
      if (sectionId != null) params.add('section_id=$sectionId');
      if (params.isNotEmpty) url += '?${params.join('&')}';

      final request = await client.getUrl(Uri.parse(url));
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      final data = jsonDecode(body);

      if (response.statusCode == 200 && data['students'] != null) {
        return List<Map<String, dynamic>>.from(data['students']);
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> submitGateAttendance({
    required String date,
    required List<Map<String, dynamic>> attendances,
  }) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$baseUrl/gate-attendance'));
      request.headers.set('content-type', 'application/json');
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }
      request.add(utf8.encode(jsonEncode({
        'date': date,
        'attendances': attendances,
      })));

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      return jsonDecode(body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> submitClassAttendance({
    required int academicClassId,
    int? sectionId,
    int? subjectId,
    String? subjectName,
    int? periodNumber,
    required String date,
    required List<Map<String, dynamic>> attendances,
  }) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$baseUrl/class-attendance'));
      request.headers.set('content-type', 'application/json');
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }
      request.add(utf8.encode(jsonEncode({
        'academic_class_id': academicClassId,
        if (sectionId != null) 'section_id': sectionId,
        if (subjectId != null) 'subject_id': subjectId,
        if (subjectName != null) 'subject_name': subjectName,
        'period_number': periodNumber ?? 1,
        'date': date,
        'attendances': attendances,
      })));

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      return jsonDecode(body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> submitClassReport({
    required int academicClassId,
    int? sectionId,
    int? subjectId,
    required String date,
    int periodNumber = 1,
    required bool classConducted,
    String? topicsCovered,
    String? homeworkAssigned,
    String? cancellationReason,
    String? remarks,
  }) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$baseUrl/class-reports'));
      request.headers.set('content-type', 'application/json');
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }
      request.add(utf8.encode(jsonEncode({
        'academic_class_id': academicClassId,
        if (sectionId != null) 'section_id': sectionId,
        if (subjectId != null) 'subject_id': subjectId,
        'date': date,
        'period_number': periodNumber,
        'class_conducted': classConducted,
        if (topicsCovered != null) 'topics_covered': topicsCovered,
        if (homeworkAssigned != null) 'homework_assigned': homeworkAssigned,
        if (cancellationReason != null) 'cancellation_reason': cancellationReason,
        if (remarks != null) 'remarks': remarks,
      })));

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      return jsonDecode(body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> submitHomework({
    required int academicClassId,
    int? sectionId,
    int? subjectId,
    required String title,
    required String description,
    required String dueDate,
    int? totalMarks,
  }) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$baseUrl/homework'));
      request.headers.set('content-type', 'application/json');
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }
      request.add(utf8.encode(jsonEncode({
        'academic_class_id': academicClassId,
        if (sectionId != null) 'section_id': sectionId,
        if (subjectId != null) 'subject_id': subjectId,
        'title': title,
        'description': description,
        'due_date': dueDate,
        if (totalMarks != null) 'total_marks': totalMarks,
      })));

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      return jsonDecode(body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> submitMarks({
    required int examId,
    required int subjectId,
    required List<Map<String, dynamic>> results,
  }) async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.postUrl(Uri.parse('$baseUrl/marks'));
      request.headers.set('content-type', 'application/json');
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }
      request.add(utf8.encode(jsonEncode({
        'exam_id': examId,
        'subject_id': subjectId,
        'results': results,
      })));

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      return jsonDecode(body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<List<Map<String, dynamic>>> getNotices() async {
    try {
      final client = HttpClient()..connectionTimeout = const Duration(seconds: 4);
      final request = await client.getUrl(Uri.parse('$baseUrl/notices'));
      request.headers.set('accept', 'application/json');
      if (authToken != null) {
        request.headers.set('Authorization', 'Bearer $authToken');
      }

      final response = await request.close();
      final body = await response.transform(utf8.decoder).join();
      final data = jsonDecode(body);

      if (response.statusCode == 200 && data['notices'] != null) {
        return List<Map<String, dynamic>>.from(data['notices']);
      }
      return [];
    } catch (_) {
      return [];
    }
  }
}
