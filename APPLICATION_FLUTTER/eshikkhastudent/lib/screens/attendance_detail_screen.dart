import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/attendance_model.dart';
import '../services/api_service.dart';

class AttendanceDetailScreen extends StatefulWidget {
  final int studentId;
  final String studentName;
  final bool showAppBar;

  const AttendanceDetailScreen({
    super.key,
    required this.studentId,
    required this.studentName,
    this.showAppBar = true,
  });

  @override
  State<AttendanceDetailScreen> createState() => _AttendanceDetailScreenState();
}

class _AttendanceDetailScreenState extends State<AttendanceDetailScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  AttendanceAnalytics? _analytics;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadAnalytics();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadAnalytics() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getAttendanceAnalytics(studentId: widget.studentId);
    if (mounted) {
      setState(() {
        _analytics = data;
        _isLoading = false;
      });
    }
  }

  void _showApplyLeaveModal() {
    final titleController = TextEditingController();
    final reasonController = TextEditingController();
    String selectedType = 'sick';
    DateTime startDate = DateTime.now();
    DateTime endDate = DateTime.now().add(const Duration(days: 1));

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
              ),
              padding: EdgeInsets.only(
                top: 24,
                left: 24,
                right: 24,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 44,
                        height: 4,
                        decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      AppStrings.tr('apply_leave_btn'),
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 16),

                    // Leave Type Dropdown
                    DropdownButtonFormField<String>(
                      value: selectedType,
                      decoration: InputDecoration(
                        labelText: AppStrings.tr('leave_type'),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'sick', child: Text('Sick Leave (অসুস্থতাজনিত)')),
                        DropdownMenuItem(value: 'medical', child: Text('Medical Leave (চিকিৎসাজনিত)')),
                        DropdownMenuItem(value: 'family', child: Text('Family Event (পারিবারিক)')),
                        DropdownMenuItem(value: 'holiday', child: Text('Special Holiday (বিশেষ ছুটি)')),
                      ],
                      onChanged: (val) {
                        if (val != null) setModalState(() => selectedType = val);
                      },
                    ),
                    const SizedBox(height: 14),

                    // Title Field
                    TextField(
                      controller: titleController,
                      decoration: InputDecoration(
                        labelText: 'Title / Subject',
                        hintText: 'e.g. Medical Rest with Prescription',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Reason Field
                    TextField(
                      controller: reasonController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        labelText: AppStrings.tr('leave_reason'),
                        hintText: 'Describe details of leave...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Date range picker preview
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.date_range, color: Color(0xFF2563EB), size: 20),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              '${startDate.toString().split(' ')[0]} to ${endDate.toString().split(' ')[0]}',
                              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                            ),
                          ),
                          TextButton(
                            onPressed: () async {
                              final range = await showDateRangePicker(
                                context: context,
                                firstDate: DateTime.now().subtract(const Duration(days: 30)),
                                lastDate: DateTime.now().add(const Duration(days: 90)),
                              );
                              if (range != null) {
                                setModalState(() {
                                  startDate = range.start;
                                  endDate = range.end;
                                });
                              }
                            },
                            child: const Text('Change Date'),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: () async {
                          final title = titleController.text.trim().isEmpty ? 'General Leave' : titleController.text.trim();
                          final sDate = startDate.toString().split(' ')[0];
                          final eDate = endDate.toString().split(' ')[0];

                          await ApiService.applyLeave(
                            studentId: widget.studentId,
                            leaveType: selectedType,
                            title: title,
                            startDate: sDate,
                            endDate: eDate,
                            reason: reasonController.text.trim(),
                          );

                          if (ctx.mounted) Navigator.pop(ctx);
                          _loadAnalytics();

                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(AppStrings.tr('leave_submitted_toast')),
                                backgroundColor: const Color(0xFF10B981),
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2563EB),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: Text(
                          AppStrings.tr('apply_leave_btn'),
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
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
          backgroundColor: const Color(0xFFF8FAFC),
          appBar: widget.showAppBar
              ? AppBar(
                  title: Text(
                    '${AppStrings.tr('attendance_analytics_title')} • ${widget.studentName}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  actions: [
                    IconButton(
                      icon: const Icon(Icons.add_circle_outline, color: Color(0xFF2563EB)),
                      tooltip: AppStrings.tr('apply_leave_btn'),
                      onPressed: _showApplyLeaveModal,
                    ),
                  ],
                )
              : null,
          body: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _loadAnalytics,
                  child: ListView(
                    padding: const EdgeInsets.all(16.0),
                    children: [
                      // 1. আজ উপস্থিত কি না (Card 1: Today's Attendance Status)
                      _buildTodayCard(),
                      const SizedBox(height: 16),

                      // 2. মোট উপস্থিতির শতাংশ (Card 2: Overall Attendance Percentage)
                      _buildOverallSummaryCard(),
                      const SizedBox(height: 20),

                      // Tabs for the other requirements
                      _buildTabBar(),
                      const SizedBox(height: 16),

                      // Tab View content
                      SizedBox(
                        height: 440,
                        child: TabBarView(
                          controller: _tabController,
                          children: [
                            _buildAbsentDaysTab(),
                            _buildLateDaysTab(),
                            _buildMonthlyTab(),
                            _buildLeaveHistoryTab(),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
        );
      },
    );
  }

  // ========================================================
  // 1. আজ উপস্থিত কি না (Today's Attendance Status)
  // ========================================================
  Widget _buildTodayCard() {
    final today = _analytics?.today;
    final isPresent = today?.isPresent ?? true;
    final status = today?.status ?? 'present';

    Color bgColor1 = const Color(0xFF059669);
    Color bgColor2 = const Color(0xFF10B981);
    IconData statusIcon = Icons.check_circle_rounded;
    String statusTitle = AppStrings.tr('is_present_today');

    if (status == 'absent') {
      bgColor1 = const Color(0xFFB91C1C);
      bgColor2 = const Color(0xFFEF4444);
      statusIcon = Icons.cancel_rounded;
      statusTitle = AppStrings.tr('is_absent_today');
    } else if (status == 'late') {
      bgColor1 = const Color(0xFFD97706);
      bgColor2 = const Color(0xFFF59E0B);
      statusIcon = Icons.watch_later_rounded;
      statusTitle = AppStrings.tr('late');
    }

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [bgColor1, bgColor2],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: bgColor2.withOpacity(0.35),
            blurRadius: 14,
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
              Expanded(
                child: Text(
                  AppStrings.tr('today_attendance_check').toUpperCase(),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.8),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  today?.date ?? '2026-11-18',
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.25),
                  shape: BoxShape.circle,
                ),
                child: Icon(statusIcon, color: Colors.white, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      statusTitle,
                      style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      today?.remarks ?? 'RFID Gate Entry: 07:54 AM',
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Text(
                  isPresent ? 'LIVE GATE' : 'NOTICE',
                  style: TextStyle(
                    color: bgColor1,
                    fontWeight: FontWeight.w800,
                    fontSize: 10,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ========================================================
  // 2. মোট উপস্থিতির শতাংশ (Overall Attendance Percentage)
  // ========================================================
  Widget _buildOverallSummaryCard() {
    final a = _analytics;
    final rate = a?.overallPercentage ?? 92.5;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      AppStrings.tr('overall_percentage_title'),
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '$rate%',
                      style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                    ),
                    Text(
                      '${a?.presentCount ?? 20} Attended / ${a?.totalDays ?? 31} ${AppStrings.tr('working_days')}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF2563EB), fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              Stack(
                alignment: Alignment.center,
                children: [
                  SizedBox(
                    width: 64,
                    height: 64,
                    child: CircularProgressIndicator(
                      value: rate / 100,
                      backgroundColor: const Color(0xFFE2E8F0),
                      valueColor: AlwaysStoppedAnimation<Color>(
                        rate >= 90 ? const Color(0xFF10B981) : (rate >= 80 ? const Color(0xFFF59E0B) : const Color(0xFFEF4444)),
                      ),
                      strokeWidth: 6,
                    ),
                  ),
                  Icon(
                    rate >= 90 ? Icons.emoji_events_rounded : Icons.trending_up_rounded,
                    color: rate >= 90 ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                    size: 26,
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(height: 1),
          const SizedBox(height: 12),

          // 5-Statistic Quick Chips
          Row(
            children: [
              Expanded(child: _buildStatChip('মোট দিন', '${a?.totalDays ?? 31}', const Color(0xFF64748B))),
              Expanded(child: _buildStatChip('উপস্থিত', '${a?.presentCount ?? 20}', const Color(0xFF10B981))),
              Expanded(child: _buildStatChip('দেরি', '${a?.lateCount ?? 3}', const Color(0xFFF59E0B))),
              Expanded(child: _buildStatChip('অনুপস্থিত', '${a?.absentCount ?? 2}', const Color(0xFFEF4444))),
              Expanded(child: _buildStatChip('ছুটি', '${a?.leaveCount ?? 6}', const Color(0xFF6366F1))),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatChip(String title, String value, Color color) {
    return Column(
      children: [
        Text(value, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: color)),
        const SizedBox(height: 2),
        Text(title, style: TextStyle(color: Colors.grey.shade600, fontSize: 11)),
      ],
    );
  }

  // ========================================================
  // TAB BAR FOR ABSENT, LATE, MONTHLY & LEAVE HISTORY
  // ========================================================
  Widget _buildTabBar() {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFE2E8F0),
        borderRadius: BorderRadius.circular(16),
      ),
      child: TabBar(
        controller: _tabController,
        isScrollable: true,
        tabAlignment: TabAlignment.start,
        indicator: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 4,
            ),
          ],
        ),
        labelColor: const Color(0xFF2563EB),
        unselectedLabelColor: const Color(0xFF64748B),
        labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
        dividerColor: Colors.transparent,
        tabs: [
          Tab(text: '${AppStrings.tr('absent_days_title')} (${_analytics?.absentDays.length ?? 0})'),
          Tab(text: '${AppStrings.tr('late_days_title')} (${_analytics?.lateDays.length ?? 0})'),
          Tab(text: AppStrings.tr('monthly_attendance_title')),
          Tab(text: '${AppStrings.tr('leave_history_title')} (${_analytics?.leavesHistory.length ?? 0})'),
        ],
      ),
    );
  }

  // ========================================================
  // 3. কোন দিন অনুপস্থিত ছিল (Absent Days History)
  // ========================================================
  Widget _buildAbsentDaysTab() {
    final list = _analytics?.absentDays ?? [];
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.check_circle_outline, color: Color(0xFF10B981), size: 48),
            const SizedBox(height: 10),
            Text(AppStrings.tr('no_absent_days'), style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
      );
    }

    return ListView.builder(
      physics: const BouncingScrollPhysics(),
      itemCount: list.length,
      itemBuilder: (ctx, i) {
        final abs = list[i];
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFFFEF2F2),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFFECACA)),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: const BoxDecoration(
                  color: Color(0xFFFEE2E2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.event_busy_rounded, color: Color(0xFFDC2626), size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${abs.formattedDate} (${abs.dayName})',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF991B1B)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      abs.remarks,
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFDC2626),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  AppStrings.tr('absent'),
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // ========================================================
  // 4. কোন দিন দেরিতে এসেছে (Late Arrival Days History)
  // ========================================================
  Widget _buildLateDaysTab() {
    final list = _analytics?.lateDays ?? [];
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.alarm_on_rounded, color: Color(0xFF10B981), size: 48),
            const SizedBox(height: 10),
            Text(AppStrings.tr('no_late_days'), style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
      );
    }

    return ListView.builder(
      physics: const BouncingScrollPhysics(),
      itemCount: list.length,
      itemBuilder: (ctx, i) {
        final late = list[i];
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFFFFFBEB),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFFDE68A)),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: const BoxDecoration(
                  color: Color(0xFFFEF3C7),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.access_time_filled_rounded, color: Color(0xFFD97706), size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${late.formattedDate} (${late.dayName})',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF92400E)),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      late.remarks,
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFD97706),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  AppStrings.tr('late'),
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // ========================================================
  // 5. মাসিক উপস্থিতি (Monthly Attendance Overview)
  // ========================================================
  Widget _buildMonthlyTab() {
    final list = _analytics?.monthlyHistory ?? [];

    return ListView.builder(
      physics: const BouncingScrollPhysics(),
      itemCount: list.length,
      itemBuilder: (ctx, i) {
        final m = list[i];
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    m.monthName,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${m.percentage}%',
                      style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.w800, fontSize: 13),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: m.percentage / 100,
                  backgroundColor: const Color(0xFFE2E8F0),
                  valueColor: AlwaysStoppedAnimation<Color>(
                    m.percentage >= 85 ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                  ),
                  minHeight: 6,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('মোট: ${m.totalDays} দিন', style: TextStyle(color: Colors.grey.shade600, fontSize: 11)),
                  Text('উপস্থিত: ${m.presentCount}', style: const TextStyle(color: Color(0xFF059669), fontSize: 11, fontWeight: FontWeight.bold)),
                  Text('দেরি: ${m.lateCount}', style: const TextStyle(color: Color(0xFFD97706), fontSize: 11, fontWeight: FontWeight.bold)),
                  Text('অনুপস্থিত: ${m.absentCount}', style: const TextStyle(color: Color(0xFFDC2626), fontSize: 11, fontWeight: FontWeight.bold)),
                  Text('ছুটি: ${m.leaveCount}', style: const TextStyle(color: Color(0xFF4F46E5), fontSize: 11, fontWeight: FontWeight.bold)),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  // ========================================================
  // 6. ছুটির ইতিহাস (Leave / Holiday History)
  // ========================================================
  Widget _buildLeaveHistoryTab() {
    final list = _analytics?.leavesHistory ?? [];

    return ListView.builder(
      physics: const BouncingScrollPhysics(),
      itemCount: list.length,
      itemBuilder: (ctx, i) {
        final l = list[i];
        Color badgeColor = const Color(0xFF6366F1);
        if (l.leaveType == 'medical' || l.leaveType == 'sick') badgeColor = const Color(0xFFEF4444);
        if (l.leaveType == 'holiday') badgeColor = const Color(0xFF10B981);

        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: badgeColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      l.leaveType.toUpperCase(),
                      style: TextStyle(color: badgeColor, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFF6EE7B7)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.verified, color: Color(0xFF059669), size: 14),
                        const SizedBox(width: 4),
                        Text(
                          AppStrings.tr('approved'),
                          style: const TextStyle(color: Color(0xFF065F46), fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                l.title,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 4),
              Row(
                children: [
                  Icon(Icons.calendar_today_rounded, size: 14, color: Colors.grey.shade600),
                  const SizedBox(width: 6),
                  Text(
                    '${l.startDate} to ${l.endDate} (${l.daysCount} ${AppStrings.tr('days_count')})',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                l.reason,
                style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
              ),
              const SizedBox(height: 6),
              Text(
                'Approved by: ${l.approvedBy}',
                style: const TextStyle(color: Color(0xFF2563EB), fontSize: 11, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        );
      },
    );
  }
}
