import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/student_model.dart';
import 'academic_details_screen.dart';

class StudentResultsTab extends StatefulWidget {
  final StudentProfile? profile;
  final int studentId;
  final double overallPercentage;
  final List<ExamTerm> examTerms;
  final List<ExamScheduleItem> examSchedules;

  const StudentResultsTab({
    super.key,
    required this.profile,
    required this.studentId,
    required this.overallPercentage,
    required this.examTerms,
    this.examSchedules = const [],
  });

  @override
  State<StudentResultsTab> createState() => _StudentResultsTabState();
}

class _StudentResultsTabState extends State<StudentResultsTab> {
  int _resultsSubTab = 0;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppStrings.tr('my_results'),
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
          ),
          const SizedBox(height: 16),

          // Student card with Overall percentage progress bar
          InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => AcademicDetailsScreen(
                    studentId: widget.profile?.id ?? widget.studentId,
                    studentName: widget.profile?.name ?? 'Tanvir Ahmed',
                  ),
                ),
              );
            },
            borderRadius: BorderRadius.circular(20),
            child: Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF5B4DF6), Color(0xFF7C6FF8)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF5B4DF6).withOpacity(0.3),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.person, color: Colors.white, size: 28),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.profile?.name ?? 'Tanvir Ahmed',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        Text(
                          widget.profile?.className ?? 'Class 11',
                          style: const TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '${widget.overallPercentage.toInt()}%',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 20),
                      ),
                      Text(
                        AppStrings.tr('overall_percentage'),
                        style: const TextStyle(color: Colors.white70, fontSize: 10),
                      ),
                      const SizedBox(height: 6),
                      SizedBox(
                        width: 80,
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: widget.overallPercentage / 100,
                            backgroundColor: Colors.white24,
                            valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
                            minHeight: 4,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),

          // Direct Button to 10-Point Academic Details & Marksheet
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => AcademicDetailsScreen(
                      studentId: widget.profile?.id ?? widget.studentId,
                      studentName: widget.profile?.name ?? 'Tanvir Ahmed',
                    ),
                  ),
                );
              },
              icon: const Icon(Icons.analytics_outlined, size: 18, color: Color(0xFF2563EB)),
              label: Text(
                '${AppStrings.tr('academic_details_title')} & Marksheet',
                style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold, fontSize: 14),
              ),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFFBFDBFE), width: 1.5),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Sub Tabs: [All Exams] [Quarterly] [Half yearly] [Annual]
          Text(
            AppStrings.tr('exams_results'),
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
          ),
          const SizedBox(height: 10),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildResultSubTab(0, AppStrings.tr('all_exams')),
                const SizedBox(width: 8),
                _buildResultSubTab(1, AppStrings.tr('quarterly')),
                const SizedBox(width: 8),
                _buildResultSubTab(2, AppStrings.tr('half_yearly')),
                const SizedBox(width: 8),
                _buildResultSubTab(3, AppStrings.tr('annual')),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Terms List matching reference image
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: widget.examTerms.length,
            itemBuilder: (ctx, i) {
              final term = widget.examTerms[i];
              return _buildTermResultCard(term);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildResultSubTab(int index, String label) {
    final isSelected = _resultsSubTab == index;
    return InkWell(
      onTap: () => setState(() => _resultsSubTab = index),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF2563EB) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? const Color(0xFF2563EB) : const Color(0xFFE2E8F0)),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF64748B),
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            fontSize: 13,
          ),
        ),
      ),
    );
  }

  Widget _buildTermResultCard(ExamTerm term) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          // Term header row
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        term.termName,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        term.termPeriod,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: term.isPromoted ? const Color(0xFFECFDF5) : const Color(0xFFFEE2E2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        term.isPromoted ? Icons.check_circle_rounded : Icons.cancel_rounded,
                        color: term.isPromoted ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                        size: 16,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        term.statusBadge,
                        style: TextStyle(
                          color: term.isPromoted ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Subject marks breakdown
          if (term.subjects.isNotEmpty) ...[
            const Divider(height: 1),
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: term.subjects.length,
              separatorBuilder: (_, __) => const Divider(height: 1, indent: 16, endIndent: 16),
              itemBuilder: (ctx, idx) {
                final sub = term.subjects[idx];
                final isPass = sub.status.toUpperCase() == 'PASS';
                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: isPass ? const Color(0xFFF59E0B) : const Color(0xFFEF4444),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(sub.date, style: TextStyle(color: Colors.grey.shade400, fontSize: 11)),
                            Text(
                              sub.subject,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            sub.marks.toInt().toString(),
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                          ),
                          Text(
                            sub.status,
                            style: TextStyle(
                              color: isPass ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                              fontWeight: FontWeight.bold,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ],
      ),
    );
  }
}
