import 'package:flutter/material.dart';
import 'attendance_detail_screen.dart';

class StudentAttendanceTab extends StatelessWidget {
  final int studentId;
  final String studentName;

  const StudentAttendanceTab({
    super.key,
    required this.studentId,
    required this.studentName,
  });

  @override
  Widget build(BuildContext context) {
    return AttendanceDetailScreen(
      studentId: studentId,
      studentName: studentName,
      showAppBar: false,
    );
  }
}
