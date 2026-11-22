class StudentProfile {
  final int id;
  final String name;
  final String admissionNumber;
  final int rollNumber;
  final String gender;
  final String bloodGroup;
  final String photo;
  final String address;
  final String status;
  final String className;
  final String? group;
  final String sectionName;
  final String roomNumber;
  final String institutionName;
  final String institutionCode;
  final String institutionType;
  final String institutionEiin;
  final String guardianName;
  final String? fatherName;
  final String? motherName;
  final String guardianPhone;
  final String guardianRelation;

  StudentProfile({
    required this.id,
    required this.name,
    required this.admissionNumber,
    required this.rollNumber,
    required this.gender,
    required this.bloodGroup,
    required this.photo,
    required this.address,
    required this.status,
    required this.className,
    this.group,
    required this.sectionName,
    required this.roomNumber,
    required this.institutionName,
    required this.institutionCode,
    required this.institutionType,
    required this.institutionEiin,
    required this.guardianName,
    this.fatherName,
    this.motherName,
    required this.guardianPhone,
    required this.guardianRelation,
  });

  factory StudentProfile.fromJson(Map<String, dynamic> json) {
    final cls = json['class'] as Map<String, dynamic>? ?? {};
    final sec = json['section'] as Map<String, dynamic>? ?? {};
    final inst = json['institution'] as Map<String, dynamic>? ?? {};
    final guard = json['guardian'] as Map<String, dynamic>? ?? {};

    return StudentProfile(
      id: json['id'] ?? 2,
      name: json['name'] ?? 'Tanvir Ahmed',
      admissionNumber: json['admission_number'] ?? 'DRMC-ADM-2024-001',
      rollNumber: json['roll_number'] ?? 1,
      gender: json['gender'] ?? 'male',
      bloodGroup: json['blood_group'] ?? 'B+',
      photo: json['photo'] ?? 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200',
      address: json['address'] ?? 'Dhanmondi, Dhaka',
      status: json['status'] ?? 'active',
      className: cls['name'] ?? json['class_name'] ?? 'Class 11 (HSC Science)',
      group: json['group'] ?? cls['group'] ?? (
        (cls['name'] ?? json['class_name'] ?? '').toString().toLowerCase().contains('science') ||
        (cls['name'] ?? json['class_name'] ?? '').toString().contains('বিজ্ঞান') ? 'science' :
        (cls['name'] ?? json['class_name'] ?? '').toString().toLowerCase().contains('humanities') ||
        (cls['name'] ?? json['class_name'] ?? '').toString().contains('মানবিক') ? 'humanities' :
        (cls['name'] ?? json['class_name'] ?? '').toString().toLowerCase().contains('business') ||
        (cls['name'] ?? json['class_name'] ?? '').toString().contains('ব্যবসায়') ? 'business' : null
      ),
      sectionName: sec['name'] ?? json['section_name'] ?? 'Science A',
      roomNumber: sec['room'] ?? 'Room 301',
      institutionName: inst['name'] ?? 'Dhaka Residential Model College',
      institutionCode: inst['code'] ?? 'DRMC-101',
      institutionType: inst['type'] ?? 'college',
      institutionEiin: inst['eiin'] ?? '108252',
      guardianName: guard['name'] ?? json['guardian_name'] ?? 'Md. Rafiqul Islam Chowdhury',
      fatherName: json['father_name'] ?? guard['father_name'] ?? guard['name'] ?? 'Md. Rafiqul Islam Chowdhury',
      motherName: json['mother_name'] ?? guard['mother_name'] ?? 'Rokeya Begum',
      guardianPhone: guard['phone'] ?? json['guardian_phone'] ?? '01711000000',
      guardianRelation: guard['relation'] ?? json['guardian_relation'] ?? 'Father',
    );
  }
}

class HomeworkItem {
  final int id;
  final String subject;
  final String title;
  final String description;
  final String dueDate;
  final int totalMarks;
  String status; // 'due', 'overdue', 'submitted'
  final String colorTheme; // 'orange', 'blue', 'amber', 'teal'

  HomeworkItem({
    required this.id,
    required this.subject,
    required this.title,
    required this.description,
    required this.dueDate,
    required this.totalMarks,
    required this.status,
    required this.colorTheme,
  });

  factory HomeworkItem.fromJson(Map<String, dynamic> json) {
    return HomeworkItem(
      id: json['id'] ?? 0,
      subject: json['subject'] ?? 'Subject',
      title: json['title'] ?? 'Homework Assignment',
      description: json['description'] ?? '',
      dueDate: json['due_date'] ?? '10/12/2026',
      totalMarks: json['total_marks'] ?? 25,
      status: json['status'] ?? 'due',
      colorTheme: json['color_theme'] ?? 'orange',
    );
  }
}

class ExamScheduleItem {
  final int id;
  final String subject;
  final String examDate;
  final String startTime;
  final String endTime;
  final String room;
  final int marks;

  ExamScheduleItem({
    required this.id,
    required this.subject,
    required this.examDate,
    required this.startTime,
    required this.endTime,
    required this.room,
    required this.marks,
  });

  factory ExamScheduleItem.fromJson(Map<String, dynamic> json) {
    return ExamScheduleItem(
      id: json['id'] ?? 0,
      subject: json['subject'] ?? '',
      examDate: json['exam_date'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      room: json['room'] ?? '',
      marks: json['marks'] ?? 100,
    );
  }
}

class RoutineItem {
  final int id;
  final String day;
  final String subject;
  final String startTime;
  final String endTime;
  final String room;
  final String teacherName;

  RoutineItem({
    required this.id,
    required this.day,
    required this.subject,
    required this.startTime,
    required this.endTime,
    required this.room,
    required this.teacherName,
  });

  factory RoutineItem.fromJson(Map<String, dynamic> json) {
    final teacher = json['teacher'] as Map<String, dynamic>? ?? {};
    return RoutineItem(
      id: json['id'] ?? 0,
      day: json['day'] ?? 'Daily',
      subject: json['subject'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      room: json['room'] ?? '',
      teacherName: teacher['name'] ?? 'Faculty Member',
    );
  }
}

class SubjectResult {
  final int id;
  final String subject;
  final double marks;
  final int totalMarks;
  final String status; // 'PASS', 'FAILED'
  final String grade;
  final String date;

  SubjectResult({
    required this.id,
    required this.subject,
    required this.marks,
    required this.totalMarks,
    required this.status,
    required this.grade,
    required this.date,
  });

  factory SubjectResult.fromJson(Map<String, dynamic> json) {
    return SubjectResult(
      id: json['id'] ?? 0,
      subject: json['subject'] ?? json['subject_name'] ?? 'Subject',
      marks: (json['marks'] as num?)?.toDouble() ?? (json['obtained_marks'] as num?)?.toDouble() ?? 0.0,
      totalMarks: json['total_marks'] ?? 100,
      status: json['status'] ?? (json['marks'] >= 33 ? 'PASS' : 'FAILED'),
      grade: json['grade'] ?? 'A',
      date: json['date'] ?? '28/08/2026',
    );
  }
}

class ExamTerm {
  final String termName;
  final String termPeriod;
  final String statusBadge;
  final bool isPromoted;
  final List<SubjectResult> subjects;

  ExamTerm({
    required this.termName,
    required this.termPeriod,
    required this.statusBadge,
    required this.isPromoted,
    required this.subjects,
  });

  factory ExamTerm.fromJson(Map<String, dynamic> json) {
    final subList = json['results'] as List? ?? [];
    return ExamTerm(
      termName: json['term_name'] ?? '1st Midterm',
      termPeriod: json['term_period'] ?? '15 Sep to 18 Sep',
      statusBadge: json['status_badge'] ?? 'Promoted',
      isPromoted: json['is_promoted'] ?? true,
      subjects: subList.map((e) => SubjectResult.fromJson(e)).toList(),
    );
  }
}

class StudySubjectItem {
  final int id;
  final String name;
  final String banglaName;
  final String code;
  final int chaptersCount;
  final int completedChapters;
  final int progress;
  final String teacherName;
  final String teacherDesignation;
  final String room;
  final String colorTheme;
  final String icon;
  final int materialsCount;
  final List<String> topics;

  StudySubjectItem({
    required this.id,
    required this.name,
    required this.banglaName,
    required this.code,
    required this.chaptersCount,
    required this.completedChapters,
    required this.progress,
    required this.teacherName,
    required this.teacherDesignation,
    required this.room,
    required this.colorTheme,
    required this.icon,
    required this.materialsCount,
    required this.topics,
  });

  factory StudySubjectItem.fromJson(Map<String, dynamic> json) {
    final rawTopics = json['topics'] as List? ?? [];
    return StudySubjectItem(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      banglaName: json['bangla_name'] ?? json['name'] ?? '',
      code: json['code'] ?? '',
      chaptersCount: json['chapters_count'] ?? 10,
      completedChapters: json['completed_chapters'] ?? 5,
      progress: json['progress'] ?? 50,
      teacherName: json['teacher_name'] ?? 'Faculty Member',
      teacherDesignation: json['teacher_designation'] ?? 'Teacher',
      room: json['room'] ?? 'Room 301',
      colorTheme: json['color_theme'] ?? 'blue',
      icon: json['icon'] ?? 'menu_book_rounded',
      materialsCount: json['materials_count'] ?? 10,
      topics: rawTopics.map((e) => e.toString()).toList(),
    );
  }
}

class PracticalLabItem {
  final int id;
  final String title;
  final String banglaTitle;
  final String subject;
  final int experimentsCount;
  final String labRoom;
  final String icon;
  final String colorTheme;

  PracticalLabItem({
    required this.id,
    required this.title,
    required this.banglaTitle,
    required this.subject,
    required this.experimentsCount,
    required this.labRoom,
    required this.icon,
    required this.colorTheme,
  });

  factory PracticalLabItem.fromJson(Map<String, dynamic> json) {
    return PracticalLabItem(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      banglaTitle: json['bangla_title'] ?? json['title'] ?? '',
      subject: json['subject'] ?? '',
      experimentsCount: json['experiments_count'] ?? 10,
      labRoom: json['lab_room'] ?? 'Lab Room',
      icon: json['icon'] ?? 'science_rounded',
      colorTheme: json['color_theme'] ?? 'emerald',
    );
  }
}
