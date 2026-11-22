class InternalAssessmentItem {
  final String name;
  final int marksObtained;
  final int totalMarks;
  final String display;
  final double attendanceRate;

  InternalAssessmentItem({
    required this.name,
    required this.marksObtained,
    required this.totalMarks,
    required this.display,
    required this.attendanceRate,
  });

  factory InternalAssessmentItem.fromJson(Map<String, dynamic> json) {
    return InternalAssessmentItem(
      name: json['name'] ?? '',
      marksObtained: (json['marks_obtained'] as num?)?.toInt() ?? 0,
      totalMarks: (json['total_marks'] as num?)?.toInt() ?? 0,
      display: json['display'] ?? '',
      attendanceRate: (json['attendance_rate'] as num?)?.toDouble() ?? 85.0,
    );
  }
}

class AcademicSubjectMark {
  final int id;
  final String subject;
  final int credits;
  final int totalMarks;
  final int internalMarks;
  final int theoryMarks;
  final int practicalMarks;
  final int obtainedMarks;
  final int highestMarks;
  final String grade;
  final double gradePoint;
  final double attendanceRate;
  final String status;
  final List<InternalAssessmentItem> internalItems;

  AcademicSubjectMark({
    required this.id,
    required this.subject,
    required this.credits,
    required this.totalMarks,
    required this.internalMarks,
    required this.theoryMarks,
    required this.practicalMarks,
    required this.obtainedMarks,
    required this.highestMarks,
    required this.grade,
    required this.gradePoint,
    required this.attendanceRate,
    required this.status,
    required this.internalItems,
  });

  factory AcademicSubjectMark.fromJson(Map<String, dynamic> json) {
    final itemsList = json['internal_items'] as List? ?? [];
    return AcademicSubjectMark(
      id: json['id'] ?? 0,
      subject: json['subject'] ?? '',
      credits: (json['credits'] as num?)?.toInt() ?? 3,
      totalMarks: (json['total_marks'] as num?)?.toInt() ?? 100,
      internalMarks: (json['internal_marks'] as num?)?.toInt() ?? 0,
      theoryMarks: (json['theory_marks'] as num?)?.toInt() ?? 0,
      practicalMarks: (json['practical_marks'] as num?)?.toInt() ?? 0,
      obtainedMarks: (json['obtained_marks'] as num?)?.toInt() ?? 0,
      highestMarks: (json['highest_marks'] as num?)?.toInt() ?? 0,
      grade: json['grade'] ?? 'A',
      gradePoint: (json['grade_point'] as num?)?.toDouble() ?? 4.0,
      attendanceRate: (json['attendance_rate'] as num?)?.toDouble() ?? 85.0,
      status: json['status'] ?? 'PASS',
      internalItems: itemsList.map((e) => InternalAssessmentItem.fromJson(e)).toList(),
    );
  }
}

class AcademicMeritPosition {
  final int sectionRank;
  final int classRank;
  final int totalStudentsSection;
  final int totalStudentsClass;
  final String badge;
  final String description;

  AcademicMeritPosition({
    required this.sectionRank,
    required this.classRank,
    required this.totalStudentsSection,
    required this.totalStudentsClass,
    required this.badge,
    required this.description,
  });

  factory AcademicMeritPosition.fromJson(Map<String, dynamic> json) {
    return AcademicMeritPosition(
      sectionRank: (json['section_rank'] as num?)?.toInt() ?? 1,
      classRank: (json['class_rank'] as num?)?.toInt() ?? 3,
      totalStudentsSection: (json['total_students_section'] as num?)?.toInt() ?? 45,
      totalStudentsClass: (json['total_students_class'] as num?)?.toInt() ?? 180,
      badge: json['badge'] ?? '',
      description: json['description'] ?? '',
    );
  }
}

class AcademicDetails {
  final Map<String, dynamic> student;
  final String examName;
  final int examId;
  final String examDate;
  final String startDate;
  final String endDate;
  final String sessionYear;
  final List<AcademicSubjectMark> subjectMarks;
  final int totalMarksPossible;
  final int totalMarksObtained;
  final String grade;
  final double gpa;
  final double cgpa;
  final String gpaCgpaLabel;
  final String totalMarks;
  final double percentage;
  final AcademicMeritPosition meritPosition;
  final String status;
  final String remarks;
  final String pdfMarksheetUrl;

  AcademicDetails({
    required this.student,
    required this.examName,
    required this.examId,
    required this.examDate,
    required this.startDate,
    required this.endDate,
    required this.sessionYear,
    required this.subjectMarks,
    required this.totalMarksPossible,
    required this.totalMarksObtained,
    required this.grade,
    required this.gpa,
    required this.cgpa,
    required this.gpaCgpaLabel,
    required this.totalMarks,
    required this.percentage,
    required this.meritPosition,
    required this.status,
    required this.remarks,
    required this.pdfMarksheetUrl,
  });

  factory AcademicDetails.fromJson(Map<String, dynamic> json) {
    final subList = json['subject_marks'] as List? ?? [];
    return AcademicDetails(
      student: json['student'] as Map<String, dynamic>? ?? {},
      examName: json['exam_name'] ?? '1st Midterm Examination 2026',
      examId: (json['exam_id'] as num?)?.toInt() ?? 2,
      examDate: json['exam_date'] ?? '15 Jun, 2026 to 18 Jun, 2026',
      startDate: json['start_date'] ?? '15 Jun, 2026',
      endDate: json['end_date'] ?? '18 Jun, 2026',
      sessionYear: json['session_year'] ?? '2026',
      subjectMarks: subList.map((e) => AcademicSubjectMark.fromJson(e)).toList(),
      totalMarksPossible: (json['total_marks_possible'] as num?)?.toInt() ?? 800,
      totalMarksObtained: (json['total_marks_obtained'] as num?)?.toInt() ?? 658,
      grade: json['grade'] ?? 'A+',
      gpa: (json['gpa'] as num?)?.toDouble() ?? 4.15,
      cgpa: (json['cgpa'] as num?)?.toDouble() ?? 8.64,
      gpaCgpaLabel: json['gpa_cgpa_label'] ?? 'GPA: 4.15 / 5.00 • CGPA: 8.64',
      totalMarks: json['total_marks'] ?? '658 / 800 (82.25%)',
      percentage: (json['percentage'] as num?)?.toDouble() ?? 82.25,
      meritPosition: AcademicMeritPosition.fromJson(json['merit_position'] as Map<String, dynamic>? ?? {}),
      status: json['status'] ?? 'promoted',
      remarks: json['remarks'] ?? '',
      pdfMarksheetUrl: json['pdf_marksheet_url'] ?? '',
    );
  }
}
