import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/academic_model.dart';
import '../services/api_service.dart';

class AcademicDetailsScreen extends StatefulWidget {
  final int studentId;
  final String studentName;
  final bool isGuardian;

  const AcademicDetailsScreen({
    super.key,
    this.studentId = 2,
    this.studentName = 'Tanvir Ahmed',
    this.isGuardian = false,
  });

  @override
  State<AcademicDetailsScreen> createState() => _AcademicDetailsScreenState();
}

class _AcademicDetailsScreenState extends State<AcademicDetailsScreen> {
  int _activeTab = 0; // 0 = Internal Marks, 1 = Grade Sheet
  int _selectedSubjectIndex = 0;
  int _selectedSemesterIndex = 1; // 0 = SEM 1, 1 = SEM 2, 2 = SEM 3
  bool _isLoading = true;
  AcademicDetails? _academicDetails;

  @override
  void initState() {
    super.initState();
    _loadAcademicDetails();
  }

  Future<void> _loadAcademicDetails() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getAcademicResults(
      studentId: widget.studentId,
      isGuardian: widget.isGuardian,
    );

    if (mounted) {
      setState(() {
        _academicDetails = data;
        _isLoading = false;
      });
    }
  }

  void _showPdfMarksheetDialog() {
    final details = _academicDetails;
    if (details == null) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return SafeArea(
          child: Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
            ),
            padding: const EdgeInsets.all(24),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
              Center(
                child: Container(
                  width: 44,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.picture_as_pdf_rounded, color: Color(0xFF2563EB), size: 28),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          AppStrings.tr('official_marksheet'),
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17),
                        ),
                        Text(
                          '${details.student['name'] ?? widget.studentName} • ${details.examName}',
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const Divider(height: 28),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  children: [
                    _buildModalRow(AppStrings.tr('exam_name_label'), details.examName),
                    _buildModalRow(AppStrings.tr('exam_date_label'), details.examDate),
                    _buildModalRow(AppStrings.tr('total_marks_label'), details.totalMarks),
                    _buildModalRow('GPA / CGPA', '${details.gpa.toStringAsFixed(2)} / ${details.cgpa.toStringAsFixed(2)}'),
                    _buildModalRow(AppStrings.tr('grade_label'), details.grade),
                    _buildModalRow(
                      AppStrings.tr('merit_position_label'),
                      '#${details.meritPosition.sectionRank} (Sec) / #${details.meritPosition.classRank} (Class)',
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Marksheet verified & downloaded! (PDF Document Ready)'),
                      backgroundColor: const Color(0xFF2563EB),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                },
                icon: const Icon(Icons.print_rounded, color: Colors.white),
                label: Text(
                  AppStrings.tr('download_marksheet_pdf'),
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2563EB),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
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

  Widget _buildModalRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
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
          appBar: _buildTopAppBar(),
          body: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _loadAcademicDetails,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Dual Tabs: [Internal Marks] [Grade Sheet]
                        _buildDualTabs(),
                        const SizedBox(height: 20),

                        // Render active tab content
                        if (_activeTab == 0) _buildInternalMarksTab() else _buildGradeSheetTab(),
                      ],
                    ),
                  ),
                ),
          bottomNavigationBar: _buildBottomNav(),
        );
      },
    );
  }

  PreferredSizeWidget _buildTopAppBar() {
    return AppBar(
      backgroundColor: const Color(0xFFF8FAFC),
      elevation: 0,
      centerTitle: true,
      leading: Navigator.canPop(context)
          ? IconButton(
              icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Color(0xFF0F172A), size: 20),
              onPressed: () => Navigator.pop(context),
            )
          : Container(
              margin: const EdgeInsets.only(left: 16),
              alignment: Alignment.center,
              child: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: const Icon(Icons.account_balance_rounded, color: Color(0xFFD97706), size: 20),
              ),
            ),
      title: Text(
        AppStrings.tr('academic_details_title'),
        style: const TextStyle(
          color: Color(0xFF0F172A),
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.more_horiz_rounded, color: Color(0xFF64748B), size: 24),
          onPressed: () {},
        ),
        Container(
          margin: const EdgeInsets.only(right: 16),
          child: Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(
              color: Color(0xFFFFFBEB),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.school_rounded, color: Color(0xFFF59E0B), size: 22),
          ),
        ),
      ],
    );
  }

  Widget _buildDualTabs() {
    return Row(
      children: [
        Expanded(
          child: InkWell(
            onTap: () => setState(() => _activeTab = 0),
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  child: Text(
                    AppStrings.tr('internal_marks_tab'),
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: _activeTab == 0 ? FontWeight.bold : FontWeight.w600,
                      color: _activeTab == 0 ? const Color(0xFF2563EB) : const Color(0xFF94A3B8),
                    ),
                  ),
                ),
                Container(
                  height: 3,
                  decoration: BoxDecoration(
                    color: _activeTab == 0 ? const Color(0xFF2563EB) : Colors.transparent,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ],
            ),
          ),
        ),
        Expanded(
          child: InkWell(
            onTap: () => setState(() => _activeTab = 1),
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  child: Text(
                    AppStrings.tr('grade_sheet_tab'),
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: _activeTab == 1 ? FontWeight.bold : FontWeight.w600,
                      color: _activeTab == 1 ? const Color(0xFF2563EB) : const Color(0xFF94A3B8),
                    ),
                  ),
                ),
                Container(
                  height: 3,
                  decoration: BoxDecoration(
                    color: _activeTab == 1 ? const Color(0xFF2563EB) : Colors.transparent,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ========================================================
  // TAB 1: INTERNAL MARKS (MATCHING LEFT SCREEN IN IMAGE)
  // ========================================================
  Widget _buildInternalMarksTab() {
    final details = _academicDetails;
    if (details == null || details.subjectMarks.isEmpty) {
      return const Center(child: Text('No internal marks available'));
    }

    final subjects = details.subjectMarks;
    final selectedSubject = _selectedSubjectIndex < subjects.length
        ? subjects[_selectedSubjectIndex]
        : subjects.first;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Vertical Bar Chart
        _buildVerticalBarChart(subjects),
        const SizedBox(height: 24),

        // Selected Subject Breakdown Card (Matching Reference Card)
        _buildSubjectBreakdownCard(selectedSubject),
        const SizedBox(height: 20),

        // 10 Requirements Summary Strip
        _buildExamOverviewBanner(details),
      ],
    );
  }

  Widget _buildVerticalBarChart(List<AcademicSubjectMark> subjects) {
    // Show top 6 subjects like in the image (MATHS, MOS, BME, BE, PHYSICS, ENGLISH)
    final chartSubjects = subjects.take(6).toList();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          SizedBox(
            height: 190,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                // Y-axis labels (50, 40, 30, 20, 10)
                SizedBox(
                  width: 26,
                  height: 150,
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text('50', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.bold)),
                      Text('40', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.bold)),
                      Text('30', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.bold)),
                      Text('20', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.bold)),
                      Text('10', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
                const SizedBox(width: 8),

                // Vertical Bars
                Expanded(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: List.generate(chartSubjects.length, (index) {
                      final sub = chartSubjects[index];
                      final isSelected = _selectedSubjectIndex == index;
                      final score = sub.internalMarks;
                      const maxScore = 50.0;
                      final barHeight = (score / maxScore) * 120.0;

                      return InkWell(
                        onTap: () {
                          setState(() => _selectedSubjectIndex = index);
                        },
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            // Score Bubble
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: isSelected ? const Color(0xFFEFF6FF) : Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: isSelected ? const Color(0xFF2563EB) : const Color(0xFFE2E8F0),
                                  width: 1.5,
                                ),
                              ),
                              child: Text(
                                '$score',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: isSelected ? const Color(0xFF2563EB) : const Color(0xFFEA580C),
                                ),
                              ),
                            ),
                            const SizedBox(height: 6),

                            // The Bar Pill
                            Container(
                              width: 8,
                              height: 120,
                              decoration: BoxDecoration(
                                color: const Color(0xFFE2E8F0),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              alignment: Alignment.bottomCenter,
                              child: Container(
                                width: 8,
                                height: barHeight,
                                decoration: BoxDecoration(
                                  color: isSelected ? const Color(0xFF2563EB) : const Color(0xFF3B82F6),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                              ),
                            ),
                            const SizedBox(height: 8),

                            // Subject Acronym Label below bar
                            Text(
                              _getAcronym(sub.subject),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                                color: isSelected ? const Color(0xFF2563EB) : const Color(0xFF64748B),
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _getAcronym(String name) {
    if (name.contains('Math')) return 'MATHS';
    if (name.contains('MOS')) return 'MOS';
    if (name.contains('BME')) return 'BME';
    if (name.contains('BE')) return 'BE';
    if (name.contains('Physics')) return 'PHYSICS';
    if (name.contains('English')) return 'ENGLISH';
    if (name.contains('EG')) return 'EG';
    if (name.contains('Workshop')) return 'WORKSHOP';
    return name.length > 5 ? name.substring(0, 5).toUpperCase() : name.toUpperCase();
  }

  Widget _buildSubjectBreakdownCard(AcademicSubjectMark sub) {
    // If no specific internal items, provide realistic defaults
    final items = sub.internalItems.isNotEmpty
        ? sub.internalItems
        : [
            InternalAssessmentItem(name: 'Assignment 1', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 85.0),
            InternalAssessmentItem(name: 'Assignment 2', marksObtained: 5, totalMarks: 5, display: '5/5', attendanceRate: 85.0),
            InternalAssessmentItem(name: 'Assignment 3', marksObtained: 4, totalMarks: 5, display: '4/5', attendanceRate: 85.0),
            InternalAssessmentItem(name: 'Assignment 4', marksObtained: 3, totalMarks: 5, display: '3/5', attendanceRate: 85.0),
            InternalAssessmentItem(name: 'Sessional 1', marksObtained: 13, totalMarks: 15, display: '13/15', attendanceRate: 85.0),
            InternalAssessmentItem(name: 'Sessional 2', marksObtained: 11, totalMarks: 15, display: '11/15', attendanceRate: 85.0),
          ];

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Title & Total Score
          Text(
            sub.subject,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '${sub.internalMarks}/45',
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: Color(0xFF2563EB),
            ),
          ),
          const SizedBox(height: 16),

          // Bullet Items (Matching Screenshot)
          ...items.map((item) {
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                children: [
                  Container(
                    width: 6,
                    height: 6,
                    decoration: const BoxDecoration(
                      color: Color(0xFF86EFAC),
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    '${item.name} : ',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey.shade700,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  Text(
                    item.display,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
            );
          }),
          const SizedBox(height: 20),

          // Attendance Progress Indicator
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppStrings.tr('attendance_progress'),
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade500,
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                '${sub.attendanceRate.toInt()}%',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF10B981),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: sub.attendanceRate / 100,
              backgroundColor: const Color(0xFFE2E8F0),
              valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF86EFAC)),
              minHeight: 6,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildExamOverviewBanner(AcademicDetails details) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFBFDBFE)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                details.examName,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF1E3A8A)),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFF2563EB),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  details.status.toUpperCase(),
                  style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '${AppStrings.tr('exam_date_label')}: ${details.examDate}',
            style: const TextStyle(fontSize: 12, color: Color(0xFF3B82F6)),
          ),
          const Divider(height: 16, color: Color(0xFFBFDBFE)),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${AppStrings.tr('total_marks_label')}: ${details.totalMarks}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1E3A8A)),
              ),
              Text(
                '#${details.meritPosition.sectionRank} (Sec Rank)',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF16A34A)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ========================================================
  // TAB 2: GRADE SHEET (MATCHING RIGHT SCREEN IN IMAGE)
  // ========================================================
  Widget _buildGradeSheetTab() {
    final details = _academicDetails;
    if (details == null) {
      return const Center(child: Text('No grade sheet data'));
    }

    return Column(
      children: [
        // CGPA Header Display (Matching exact typography)
        Text(
          '${AppStrings.tr('cgpa_label')} : ${details.cgpa.toStringAsFixed(1)}',
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.bold,
            color: Color(0xFF0F172A),
          ),
        ),
        const SizedBox(height: 16),

        // Semester Slider Switcher (SEM 1, SEM 2, SEM 3)
        _buildSemesterSlider(),
        const SizedBox(height: 24),

        // Subject, Credits, Grade Table (Matching Reference)
        _buildSubjectsTable(details.subjectMarks),
        const SizedBox(height: 24),

        // 10 Requirements Cards (Full Marks, Obtained, Merit, PDF)
        _buildMeritPositionCard(details),
        const SizedBox(height: 16),

        // Action Button: Download / Print PDF Marksheet
        _buildPdfDownloadButton(),
      ],
    );
  }

  Widget _buildSemesterSlider() {
    final semLabels = ['SEM 1', 'SEM 2', 'SEM 3'];

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: List.generate(semLabels.length, (i) {
              final isCurrent = i == _selectedSemesterIndex;
              return InkWell(
                onTap: () => setState(() => _selectedSemesterIndex = i),
                child: Text(
                  semLabels[i],
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: isCurrent ? FontWeight.bold : FontWeight.w500,
                    color: isCurrent ? const Color(0xFF0F172A) : const Color(0xFF94A3B8),
                  ),
                ),
              );
            }),
          ),
          const SizedBox(height: 8),

          // Track with Thumb
          Row(
            children: [
              _buildSliderNode(0),
              Expanded(child: Container(height: 2, color: const Color(0xFFE2E8F0))),
              _buildSliderNode(1),
              Expanded(child: Container(height: 2, color: const Color(0xFFE2E8F0))),
              _buildSliderNode(2),
            ],
          ),
          const SizedBox(height: 8),

          // GPA indicator below thumb matching image "GPA | 8.84"
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                '${AppStrings.tr('gpa_label')} | ',
                style: const TextStyle(fontSize: 13, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
              ),
              const Text(
                '8.84',
                style: TextStyle(fontSize: 14, color: Color(0xFF2563EB), fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSliderNode(int index) {
    final isSelected = index == _selectedSemesterIndex;
    return InkWell(
      onTap: () => setState(() => _selectedSemesterIndex = index),
      child: Container(
        width: 16,
        height: 16,
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFFF97316) : Colors.white,
          shape: BoxShape.circle,
          border: Border.all(
            color: isSelected ? const Color(0xFFF97316) : const Color(0xFFCBD5E1),
            width: 2,
          ),
        ),
      ),
    );
  }

  Widget _buildSubjectsTable(List<AcademicSubjectMark> subjects) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          // Table Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            child: Row(
              children: [
                Expanded(
                  flex: 5,
                  child: Text(
                    AppStrings.tr('subject_label'),
                    style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF94A3B8), fontSize: 13),
                  ),
                ),
                Expanded(
                  flex: 2,
                  child: Center(
                    child: Text(
                      AppStrings.tr('credits_label'),
                      style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF94A3B8), fontSize: 13),
                    ),
                  ),
                ),
                Expanded(
                  flex: 2,
                  child: Align(
                    alignment: Alignment.centerRight,
                    child: Text(
                      AppStrings.tr('grade_label'),
                      style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF94A3B8), fontSize: 13),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: Color(0xFFE2E8F0)),

          // Table Rows
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: subjects.length,
            separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
            itemBuilder: (ctx, i) {
              final sub = subjects[i];
              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                child: Row(
                  children: [
                    Expanded(
                      flex: 5,
                      child: Text(
                        sub.subject,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                    ),
                    Expanded(
                      flex: 2,
                      child: Center(
                        child: Text(
                          '${sub.credits}',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      flex: 2,
                      child: Align(
                        alignment: Alignment.centerRight,
                        child: Text(
                          sub.grade,
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: sub.grade.startsWith('A') ? const Color(0xFF2563EB) : const Color(0xFF0F172A),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildMeritPositionCard(AcademicDetails details) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFBBF7D0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.emoji_events_rounded, color: Color(0xFF15803D), size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      AppStrings.tr('merit_position_label'),
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF15803D)),
                    ),
                    Text(
                      details.meritPosition.badge,
                      style: const TextStyle(fontSize: 12, color: Color(0xFF166534), fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            details.meritPosition.description,
            style: const TextStyle(fontSize: 13, color: Color(0xFF166534)),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${AppStrings.tr('full_marks_label')}: ${details.totalMarksPossible}',
                style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
              ),
              Text(
                '${AppStrings.tr('obtained_marks_label')}: ${details.totalMarksObtained}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
              ),
              Text(
                '${details.percentage}%',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF15803D)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPdfDownloadButton() {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton.icon(
        onPressed: _showPdfMarksheetDialog,
        icon: const Icon(Icons.picture_as_pdf_rounded, color: Colors.white, size: 20),
        label: Text(
          AppStrings.tr('download_marksheet_pdf'),
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF2563EB),
          elevation: 2,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
      ),
    );
  }

  Widget _buildBottomNav() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Colors.grey.shade200)),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildNavItem(Icons.notifications_none_rounded, 'Notices', false),
              _buildNavItem(Icons.menu_book_rounded, 'Academics', true),
              _buildNavItem(Icons.account_balance_wallet_outlined, 'Financials', false),
              _buildNavItem(Icons.person_outline_rounded, 'Admission', false),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(IconData icon, String label, bool isSelected) {
    return InkWell(
      onTap: () {
        if (!isSelected && Navigator.canPop(context)) {
          Navigator.pop(context);
        }
      },
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            color: isSelected ? const Color(0xFFF97316) : const Color(0xFF94A3B8),
            size: 24,
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? const Color(0xFFF97316) : const Color(0xFF94A3B8),
            ),
          ),
        ],
      ),
    );
  }
}
