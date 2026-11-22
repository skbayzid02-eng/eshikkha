import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/child_model.dart';
import 'academic_details_screen.dart';
import 'attendance_detail_screen.dart';
import 'student_dashboard_screen.dart';

class ChildDetailScreen extends StatefulWidget {
  final ChildSummary child;
  final List<ChildSummary> allChildren;

  const ChildDetailScreen({
    super.key,
    required this.child,
    required this.allChildren,
  });

  @override
  State<ChildDetailScreen> createState() => _ChildDetailScreenState();
}

class _ChildDetailScreenState extends State<ChildDetailScreen> {
  late ChildSummary _currentChild;
  bool _isFeePaid = false;

  @override
  void initState() {
    super.initState();
    _currentChild = widget.child;
  }

  void _switchChild(ChildSummary newChild) {
    setState(() {
      _currentChild = newChild;
      _isFeePaid = false;
    });
  }

  void _showPaymentModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              left: 24,
              right: 24,
              top: 24,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(color: const Color(0xFFCBD5E1), borderRadius: BorderRadius.circular(2)),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'bKash Payment Gateway',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Total Fee: ৳ ${_currentChild.dueFeesAmount.toInt()}',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 15, color: Color(0xFFD81B60), fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 20),

                  const TextField(
                    decoration: InputDecoration(
                      labelText: 'bKash Account Number',
                      prefixIcon: Icon(Icons.phone_android),
                      hintText: '01XXXXXXXXX',
                    ),
                  ),
                  const SizedBox(height: 12),
                  const TextField(
                    obscureText: true,
                    decoration: InputDecoration(
                      labelText: 'bKash PIN',
                      prefixIcon: Icon(Icons.pin),
                    ),
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      setState(() {
                        _isFeePaid = true;
                      });
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('${AppStrings.tr('payment_success')}BKX9481729'),
                          backgroundColor: const Color(0xFF059669),
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFD81B60),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Confirm Payment', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, lang, _) {
        return Scaffold(
          appBar: AppBar(
            title: Text(
              _currentChild.name,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            actions: [
              // Fast Child Switcher in Top Bar
              PopupMenuButton<ChildSummary>(
                icon: const Icon(Icons.swap_horiz, color: Color(0xFF2563EB)),
                tooltip: AppStrings.tr('switch_child'),
                onSelected: _switchChild,
                itemBuilder: (context) {
                  return widget.allChildren.map((c) {
                    return PopupMenuItem<ChildSummary>(
                      value: c,
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 12,
                            backgroundColor: c.studentId == _currentChild.studentId ? const Color(0xFF2563EB) : const Color(0xFF94A3B8),
                            child: Text(c.name[0], style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(c.name, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold), overflow: TextOverflow.ellipsis),
                                Text(c.institutionName, style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)), overflow: TextOverflow.ellipsis),
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList();
                },
              ),
            ],
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Digital Student ID Card
                _buildDigitalIdCard(),
                const SizedBox(height: 12),

                // Button to open Student Portal directly
                ElevatedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => StudentDashboardScreen(studentId: _currentChild.studentId),
                      ),
                    );
                  },
                  icon: const Icon(Icons.school_rounded, color: Colors.white, size: 20),
                  label: Text(
                    '${AppStrings.tr('student_portal')} (${_currentChild.name})',
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 18),

                // Live Attendance Status
                _buildAttendanceCard(),
                const SizedBox(height: 16),

                // Fee Dues & Pay with bKash
                _buildFeeCard(),
                const SizedBox(height: 16),

                // Results Card
                _buildResultsCard(),
                const SizedBox(height: 16),

                // Class Routine
                _buildRoutineCard(),
                const SizedBox(height: 16),

                // Teacher Messages
                _buildMessagesCard(),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildDigitalIdCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E3A8A), Color(0xFF2563EB), Color(0xFF0284C7)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF2563EB).withValues(alpha: 0.3),
            blurRadius: 15,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                _currentChild.institutionName.toUpperCase(),
                style: const TextStyle(color: Color(0xFFBFDBFE), fontSize: 11, letterSpacing: 1.0, fontWeight: FontWeight.bold),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  AppStrings.tr(_currentChild.relationship),
                  style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          Row(
            children: [
              CircleAvatar(
                radius: 30,
                backgroundColor: Colors.white,
                child: CircleAvatar(
                  radius: 28,
                  backgroundColor: const Color(0xFFDBEAFE),
                  child: Text(
                    _currentChild.name[0],
                    style: const TextStyle(color: Color(0xFF1E40AF), fontWeight: FontWeight.bold, fontSize: 24),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _currentChild.name,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 17),
                    ),
                    Text(
                      '${_currentChild.classOrSemester} • ${_currentChild.section}',
                      style: const TextStyle(color: Color(0xFFE0E7FF), fontSize: 13),
                    ),
                    Text(
                      '${AppStrings.tr('roll')}: #${_currentChild.rollNumber}',
                      style: const TextStyle(color: Color(0xFF93C5FD), fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const Divider(color: Colors.white24, height: 26),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('ID: ${_currentChild.admissionNumber}', style: const TextStyle(color: Color(0xFFDBEAFE), fontSize: 11, fontFamily: 'monospace')),
              const Text('Session: 2026', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceCard() {
    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => AttendanceDetailScreen(
              studentId: _currentChild.studentId,
              studentName: _currentChild.name,
            ),
          ),
        );
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFFECFDF5),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFA7F3D0)),
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: const BoxDecoration(
                color: Color(0xFF10B981),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check, color: Colors.white, size: 24),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${AppStrings.tr('attr_attendance')}: ${AppStrings.tr(_currentChild.todayAttendanceStatus)}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF065F46)),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _currentChild.todayAttendanceTime,
                    style: const TextStyle(fontSize: 12, color: Color(0xFF047857)),
                  ),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Color(0xFF059669)),
          ],
        ),
      ),
    );
  }

  Widget _buildFeeCard() {
    final hasDues = _currentChild.dueFeesCount > 0 && !_isFeePaid;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  AppStrings.tr('fee_payment'),
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: hasDues ? const Color(0xFFFFE4E6) : const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    hasDues ? AppStrings.tr('has_dues') : AppStrings.tr('no_dues'),
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: hasDues ? const Color(0xFFBE123C) : const Color(0xFF047857),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (hasDues) ...[
              Text(
                '৳ ${_currentChild.dueFeesAmount.toInt()}.00',
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 14),
              ElevatedButton.icon(
                onPressed: _showPaymentDialog,
                icon: const Icon(Icons.credit_card, size: 18),
                label: Text(AppStrings.tr('pay_with_bkash'), style: const TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFD81B60),
                  foregroundColor: Colors.white,
                  minimumSize: const Size.fromHeight(48),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ] else ...[
              const Text('All institutional fees are paid up to date.', style: TextStyle(color: Color(0xFF059669), fontSize: 13)),
            ],
          ],
        ),
      ),
    );
  }

  void _showPaymentDialog() {
    _showPaymentModal();
  }

  Widget _buildResultsCard() {
    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => AcademicDetailsScreen(
              studentId: _currentChild.studentId,
              studentName: _currentChild.name,
              isGuardian: true,
            ),
          ),
        );
      },
      borderRadius: BorderRadius.circular(16),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    AppStrings.tr('results_marksheet'),
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                  ),
                  Row(
                    children: [
                      Text(
                        'GPA ${_currentChild.latestGpa.toStringAsFixed(2)} (${_currentChild.latestGrade})',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                      ),
                      const SizedBox(width: 4),
                      const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Color(0xFF2563EB)),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(_currentChild.examTitle, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12)),
              const Divider(height: 20),
              _buildResultRow('Major Subject 1', 92, 'A+'),
              _buildResultRow('Major Subject 2', 88, 'A+'),
              _buildResultRow('General / Core Subject', 84, 'A'),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => AcademicDetailsScreen(
                          studentId: _currentChild.studentId,
                          studentName: _currentChild.name,
                          isGuardian: true,
                        ),
                      ),
                    );
                  },
                  icon: const Icon(Icons.analytics_outlined, size: 16, color: Color(0xFF2563EB)),
                  label: Text(
                    AppStrings.tr('academic_details_title'),
                    style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFFBFDBFE)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildResultRow(String subject, int marks, String grade) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(subject, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
          Row(
            children: [
              Text('$marks', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(grade, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF2563EB))),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRoutineCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(AppStrings.tr('class_routine'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 10),
            _buildRoutineItem('Period 1 (08:00 - 08:45 AM)', 'Mathematics / Core Subject', 'Room 301'),
            _buildRoutineItem('Period 2 (08:50 - 09:35 AM)', 'Science / Lab Work', 'Lab 2'),
            _buildRoutineItem('Period 3 (09:40 - 10:25 AM)', 'Language & Literature', 'Room 301'),
          ],
        ),
      ),
    );
  }

  Widget _buildRoutineItem(String time, String subject, String room) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Container(width: 3, height: 28, color: const Color(0xFF2563EB)),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(subject, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                Text(time, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
              ],
            ),
          ),
          Text(room, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
        ],
      ),
    );
  }

  Widget _buildMessagesCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(AppStrings.tr('teacher_messages'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Class Teacher Message', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      Text('Today', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8))),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${_currentChild.name} is performing excellently in class. Please attend the upcoming parent assembly.',
                    style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
