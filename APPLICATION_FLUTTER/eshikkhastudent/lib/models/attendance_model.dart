class TodayAttendance {
  final bool isPresent;
  final String status;
  final String statusLabel;
  final String remarks;
  final String date;

  TodayAttendance({
    required this.isPresent,
    required this.status,
    required this.statusLabel,
    required this.remarks,
    required this.date,
  });

  factory TodayAttendance.fromJson(Map<String, dynamic> json) {
    return TodayAttendance(
      isPresent: json['is_present'] ?? true,
      status: json['status'] ?? 'present',
      statusLabel: json['status_label'] ?? 'উপস্থিত (Present)',
      remarks: json['remarks'] ?? 'RFID Gate Entry: 07:54 AM',
      date: json['date'] ?? '2026-11-18',
    );
  }
}

class AbsentDay {
  final int id;
  final String date;
  final String formattedDate;
  final String dayName;
  final String remarks;

  AbsentDay({
    required this.id,
    required this.date,
    required this.formattedDate,
    required this.dayName,
    required this.remarks,
  });

  factory AbsentDay.fromJson(Map<String, dynamic> json) {
    return AbsentDay(
      id: json['id'] ?? 0,
      date: json['date'] ?? '',
      formattedDate: json['formatted_date'] ?? json['date'] ?? '',
      dayName: json['day_name'] ?? '',
      remarks: json['remarks'] ?? 'অনুপস্থিত (Uninformed absence)',
    );
  }
}

class LateDay {
  final int id;
  final String date;
  final String formattedDate;
  final String dayName;
  final String remarks;

  LateDay({
    required this.id,
    required this.date,
    required this.formattedDate,
    required this.dayName,
    required this.remarks,
  });

  factory LateDay.fromJson(Map<String, dynamic> json) {
    return LateDay(
      id: json['id'] ?? 0,
      date: json['date'] ?? '',
      formattedDate: json['formatted_date'] ?? json['date'] ?? '',
      dayName: json['day_name'] ?? '',
      remarks: json['remarks'] ?? 'দেরিতে উপস্থিতি',
    );
  }
}

class MonthlyAttendance {
  final String monthName;
  final int totalDays;
  final int presentCount;
  final int lateCount;
  final int absentCount;
  final int leaveCount;
  final double percentage;

  MonthlyAttendance({
    required this.monthName,
    required this.totalDays,
    required this.presentCount,
    required this.lateCount,
    required this.absentCount,
    required this.leaveCount,
    required this.percentage,
  });

  factory MonthlyAttendance.fromJson(Map<String, dynamic> json) {
    return MonthlyAttendance(
      monthName: json['month_name'] ?? '',
      totalDays: json['total_days'] ?? 0,
      presentCount: json['present_count'] ?? 0,
      lateCount: json['late_count'] ?? 0,
      absentCount: json['absent_count'] ?? 0,
      leaveCount: json['leave_count'] ?? 0,
      percentage: (json['percentage'] as num?)?.toDouble() ?? 100.0,
    );
  }
}

class LeaveRecord {
  final int id;
  final String leaveType;
  final String title;
  final String startDate;
  final String endDate;
  final int daysCount;
  final String reason;
  final String status;
  final String approvedBy;

  LeaveRecord({
    required this.id,
    required this.leaveType,
    required this.title,
    required this.startDate,
    required this.endDate,
    required this.daysCount,
    required this.reason,
    required this.status,
    required this.approvedBy,
  });

  factory LeaveRecord.fromJson(Map<String, dynamic> json) {
    return LeaveRecord(
      id: json['id'] ?? 0,
      leaveType: json['leave_type'] ?? 'sick',
      title: json['title'] ?? 'Leave Application',
      startDate: json['start_date'] ?? '',
      endDate: json['end_date'] ?? '',
      daysCount: json['days_count'] ?? 1,
      reason: json['reason'] ?? '',
      status: json['status'] ?? 'approved',
      approvedBy: json['approved_by'] ?? 'Institution Authority',
    );
  }
}

class AttendanceAnalytics {
  final int studentId;
  final String studentName;
  final TodayAttendance today;
  final double overallPercentage;
  final int totalDays;
  final int presentCount;
  final int lateCount;
  final int absentCount;
  final int leaveCount;
  final List<AbsentDay> absentDays;
  final List<LateDay> lateDays;
  final List<MonthlyAttendance> monthlyHistory;
  final List<LeaveRecord> leavesHistory;

  AttendanceAnalytics({
    required this.studentId,
    required this.studentName,
    required this.today,
    required this.overallPercentage,
    required this.totalDays,
    required this.presentCount,
    required this.lateCount,
    required this.absentCount,
    required this.leaveCount,
    required this.absentDays,
    required this.lateDays,
    required this.monthlyHistory,
    required this.leavesHistory,
  });

  factory AttendanceAnalytics.fromJson(Map<String, dynamic> json) {
    final todayJson = json['today'] as Map<String, dynamic>? ?? {};
    final overallJson = json['overall'] as Map<String, dynamic>? ?? {};
    final absList = json['absent_days'] as List? ?? [];
    final lateList = json['late_days'] as List? ?? [];
    final monthList = json['monthly_history'] as List? ?? [];
    final leaveList = json['leaves_history'] as List? ?? [];

    return AttendanceAnalytics(
      studentId: json['student_id'] ?? 2,
      studentName: json['student_name'] ?? 'Tanvir Ahmed',
      today: TodayAttendance.fromJson(todayJson),
      overallPercentage: (overallJson['percentage'] as num?)?.toDouble() ?? 92.5,
      totalDays: overallJson['total_days'] ?? 31,
      presentCount: overallJson['present_count'] ?? 20,
      lateCount: overallJson['late_count'] ?? 3,
      absentCount: overallJson['absent_count'] ?? 2,
      leaveCount: overallJson['leave_count'] ?? 6,
      absentDays: absList.map((e) => AbsentDay.fromJson(e)).toList(),
      lateDays: lateList.map((e) => LateDay.fromJson(e)).toList(),
      monthlyHistory: monthList.map((e) => MonthlyAttendance.fromJson(e)).toList(),
      leavesHistory: leaveList.map((e) => LeaveRecord.fromJson(e)).toList(),
    );
  }
}
