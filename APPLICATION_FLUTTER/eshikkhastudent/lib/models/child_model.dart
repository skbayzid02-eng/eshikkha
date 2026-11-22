class ChildSummary {
  final int studentId;
  final String admissionNumber;
  final int rollNumber;
  final String relationship;
  final String photo;
  final String name;
  final String institutionName;
  final String institutionType; // school, college, madrasah, university
  final String institutionCode;
  final String classOrSemester;
  final String section;
  final String todayAttendanceStatus; // present, absent, late, pending
  final String todayAttendanceTime;
  final double latestGpa;
  final String latestGrade;
  final String examTitle;
  final int dueFeesCount;
  final double dueFeesAmount;
  final int newNoticesCount;
  final int newMessagesCount;

  ChildSummary({
    required this.studentId,
    required this.admissionNumber,
    required this.rollNumber,
    required this.relationship,
    required this.photo,
    required this.name,
    required this.institutionName,
    required this.institutionType,
    required this.institutionCode,
    required this.classOrSemester,
    required this.section,
    required this.todayAttendanceStatus,
    required this.todayAttendanceTime,
    required this.latestGpa,
    required this.latestGrade,
    required this.examTitle,
    required this.dueFeesCount,
    required this.dueFeesAmount,
    required this.newNoticesCount,
    required this.newMessagesCount,
  });

  factory ChildSummary.fromJson(Map<String, dynamic> json) {
    final inst = json['institution'] as Map<String, dynamic>? ?? {};
    final att = json['today_attendance'] as Map<String, dynamic>? ?? {};
    final res = json['latest_result'] as Map<String, dynamic>? ?? {};
    final fees = json['due_fees'] as Map<String, dynamic>? ?? {};

    return ChildSummary(
      studentId: json['student_id'] ?? 0,
      admissionNumber: json['admission_number'] ?? '',
      rollNumber: json['roll_number'] ?? 0,
      relationship: json['relationship'] ?? 'father',
      photo: json['photo'] ?? '',
      name: json['name'] ?? '',
      institutionName: inst['name'] ?? '',
      institutionType: inst['type'] ?? 'school',
      institutionCode: inst['code'] ?? '',
      classOrSemester: json['class_or_semester'] ?? '',
      section: json['section'] ?? '',
      todayAttendanceStatus: att['status'] ?? 'pending',
      todayAttendanceTime: att['time_or_remarks'] ?? '',
      latestGpa: (res['gpa'] as num?)?.toDouble() ?? 5.0,
      latestGrade: res['letter_grade'] ?? 'A+',
      examTitle: res['exam_title'] ?? '',
      dueFeesCount: fees['count'] ?? 0,
      dueFeesAmount: (fees['total_amount'] as num?)?.toDouble() ?? 0.0,
      newNoticesCount: json['new_notices_count'] ?? 0,
      newMessagesCount: json['new_messages_count'] ?? 0,
    );
  }
}

class StudentMessageItem {
  final int id;
  final String senderName;
  final String senderRole;
  final String title;
  final String message;
  final String date;
  final bool isRead;

  StudentMessageItem({
    required this.id,
    required this.senderName,
    required this.senderRole,
    required this.title,
    required this.message,
    required this.date,
    required this.isRead,
  });
}
