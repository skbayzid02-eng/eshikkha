import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/student_model.dart';
import '../models/textbook_model.dart';
import 'student_textbooks_section.dart';

class StudentHomeTab extends StatelessWidget {
  final StudentProfile? profile;
  final bool isClass1;
  final List<TextbookModel> textbooks;
  final List<HomeworkItem> homeworks;
  final List<Map<String, dynamic>> messages;
  final String todayAttendanceStatus;
  final String todayAttendanceRemarks;
  final double attendancePercentage;
  final Future<void> Function() onRefresh;
  final VoidCallback onOpenDrawer;
  final VoidCallback onOpenMessages;
  final void Function(int index) onNavigateToTab;
  final void Function(HomeworkItem hw)? onHomeworkTap;

  const StudentHomeTab({
    super.key,
    required this.profile,
    required this.isClass1,
    required this.textbooks,
    required this.homeworks,
    required this.messages,
    required this.todayAttendanceStatus,
    required this.todayAttendanceRemarks,
    required this.attendancePercentage,
    required this.onRefresh,
    required this.onOpenDrawer,
    required this.onOpenMessages,
    required this.onNavigateToTab,
    this.onHomeworkTap,
  });

  String _formatClassName(String? raw) {
    if (raw == null || raw.trim().isEmpty) {
      return AppLanguage.isBangla ? 'প্রথম শ্রেণি' : 'Class 1';
    }
    final lower = raw.trim().toLowerCase();
    if (lower == 'play' || lower == 'play group' || raw.contains('প্লে')) {
      return AppLanguage.isBangla ? 'প্লে গ্রুপ' : 'Play Group';
    }
    if (lower == 'nursery' || raw.contains('নার্সারি')) {
      return AppLanguage.isBangla ? 'নার্সারি' : 'Nursery';
    }
    if (lower == 'kg' || raw.contains('কেজি')) {
      return AppLanguage.isBangla ? 'কেজি' : 'KG';
    }
    if (lower == 'diploma' || raw.contains('ডিপ্লোমা')) {
      return AppLanguage.isBangla ? 'ডিপ্লোমা' : 'Diploma';
    }
    if (lower == 'honours' || raw.contains('অনার্স')) {
      return AppLanguage.isBangla ? 'অনার্স' : 'Honours';
    }
    if (lower == 'degree' || raw.contains('ডিগ্রি')) {
      return AppLanguage.isBangla ? 'ডিগ্রি' : 'Degree';
    }
    if (lower == 'masters' || raw.contains('মাস্টার্স')) {
      return AppLanguage.isBangla ? 'মাস্টার্স' : 'Masters';
    }
    return raw;
  }

  @override
  Widget build(BuildContext context) {
    final studentName = profile?.name ?? 'Tanvir Ahmed';
    final formattedClass = _formatClassName(profile?.className ?? (isClass1 ? 'Class 1' : 'Student'));

    return RefreshIndicator(
      onRefresh: onRefresh,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top App Bar row
            Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.menu_rounded, size: 28, color: Color(0xFF1E293B)),
                  onPressed: onOpenDrawer,
                ),
                Expanded(
                  child: Center(
                    child: Text(
                      AppStrings.tr('home_tab'),
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                  ),
                ),
                Stack(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.notifications_none_rounded, size: 26, color: Color(0xFF1E293B)),
                      onPressed: onOpenMessages,
                    ),
                    if (messages.isNotEmpty)
                      Positioned(
                        right: 8,
                        top: 8,
                        child: Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: Color(0xFFEF4444),
                            shape: BoxShape.circle,
                          ),
                        ),
                      ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Greeting + Student Memoji
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        AppStrings.tr('hello_student'),
                        style: TextStyle(fontSize: 14, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        studentName,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        '$formattedClass • ${AppStrings.tr('roll')}: ${profile?.rollNumber ?? 1}',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 13, color: Color(0xFF2563EB), fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: const Color(0xFFDBEAFE),
                    shape: BoxShape.circle,
                    border: Border.all(color: const Color(0xFFBFDBFE), width: 2),
                  ),
                  child: Center(
                    child: Text(
                      studentName.isNotEmpty ? studentName[0] : 'T',
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1E40AF),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Banner Card: "FRENCH Coaching Class Started!"
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF5B4DF6), Color(0xFF867BFD)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF5B4DF6).withOpacity(0.3),
                    blurRadius: 16,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Stack(
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isClass1
                            ? AppStrings.tr('welcome_class_1_home')
                            : (AppLanguage.isBangla
                                ? 'স্বাগতম $formattedClass পোর্টালে!'
                                : 'Welcome to $formattedClass Portal!'),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          height: 1.3,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${profile?.institutionName ?? "শিক্ষা প্রতিষ্ঠান"} • $formattedClass',
                        style: const TextStyle(color: Colors.white70, fontSize: 13),
                      ),
                      const SizedBox(height: 14),
                      ElevatedButton(
                        onPressed: () => onNavigateToTab(1),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF5B4DF6),
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        ),
                        child: Text(
                          AppStrings.tr('get_started'),
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                  Positioned(
                    right: 0,
                    bottom: 0,
                    child: Opacity(
                      opacity: 0.9,
                      child: Icon(Icons.school_rounded, size: 70, color: Colors.white.withOpacity(0.25)),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // Dots indicator
            Center(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(width: 8, height: 8, decoration: const BoxDecoration(color: Color(0xFF5B4DF6), shape: BoxShape.circle)),
                  const SizedBox(width: 6),
                  Container(width: 6, height: 6, decoration: BoxDecoration(color: Colors.grey.shade300, shape: BoxShape.circle)),
                  const SizedBox(width: 6),
                  Container(width: 6, height: 6, decoration: BoxDecoration(color: Colors.grey.shade300, shape: BoxShape.circle)),
                ],
              ),
            ),
            const SizedBox(height: 22),

            // ==========================================
            // HOMEPAGE TEXTBOOKS SECTION (বইয়ের তালিকা)
            // ==========================================
            StudentTextbooksSection(
              textbooks: textbooks,
              className: profile?.className,
              group: profile?.group,
            ),
            const SizedBox(height: 24),

            // Pickup where you left!
            Text(
              AppStrings.tr('pickup_where_you_left'),
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 140,
              child: ListView(
                scrollDirection: Axis.horizontal,
                children: isClass1
                    ? [
                        _buildVideoLessonCard(
                          title: AppLanguage.isBangla ? 'বাংলা' : 'Bangla',
                          subtitle: AppLanguage.isBangla ? 'ছড়া ও স্বরবর্ণ পাঠ' : 'Rhymes & Alphabets',
                          color: const Color(0xFFECFDF5),
                          icon: Icons.menu_book_rounded,
                          iconColor: const Color(0xFF10B981),
                        ),
                        const SizedBox(width: 14),
                        _buildVideoLessonCard(
                          title: AppLanguage.isBangla ? 'ইংরেজি' : 'English',
                          subtitle: AppLanguage.isBangla ? 'Alphabet & Phonics Fun' : 'Alphabet & Phonics',
                          color: const Color(0xFFEEF2FF),
                          icon: Icons.school_rounded,
                          iconColor: const Color(0xFF3B82F6),
                        ),
                        const SizedBox(width: 14),
                        _buildVideoLessonCard(
                          title: AppLanguage.isBangla ? 'গণিত' : 'Maths',
                          subtitle: AppLanguage.isBangla ? '১ থেকে ১০ সংখ্যা গণনা' : 'Counting 1 to 10',
                          color: const Color(0xFFFEF2F2),
                          icon: Icons.calculate_rounded,
                          iconColor: const Color(0xFFEF4444),
                        ),
                      ]
                    : [
                        _buildVideoLessonCard(
                          title: 'Science',
                          subtitle: 'Organic Chemistry Lab',
                          color: const Color(0xFFEEF2FF),
                          icon: Icons.science_rounded,
                          iconColor: const Color(0xFF4F46E5),
                        ),
                        const SizedBox(width: 14),
                        _buildVideoLessonCard(
                          title: 'Maths',
                          subtitle: 'Algebra & Matrices',
                          color: const Color(0xFFFEF2F2),
                          icon: Icons.calculate_rounded,
                          iconColor: const Color(0xFFEF4444),
                        ),
                        const SizedBox(width: 14),
                        _buildVideoLessonCard(
                          title: 'ICT',
                          subtitle: 'SQL & Database',
                          color: const Color(0xFFECFDF5),
                          icon: Icons.computer_rounded,
                          iconColor: const Color(0xFF10B981),
                        ),
                      ],
              ),
            ),
            const SizedBox(height: 24),

            // Assignment Status Section Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    AppStrings.tr('assignment_status'),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  '${homeworks.length} ${AppStrings.tr('due')}',
                  style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ],
            ),
            const SizedBox(height: 14),

            // 2x2 Grid: English, Maths, Science, ICT
            _buildAssignmentGrid(),
            const SizedBox(height: 24),

            // Activities / Attendance Live Card
            Text(
              AppStrings.tr('activities'),
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 12),
            _buildAttendanceActivityCard(),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildVideoLessonCard({
    required String title,
    required String subtitle,
    required Color color,
    required IconData icon,
    required Color iconColor,
  }) {
    return Container(
      width: 165,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            height: 48,
            width: double.infinity,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Icon(icon, size: 26, color: iconColor.withOpacity(0.4)),
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.9),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(Icons.play_arrow_rounded, color: iconColor, size: 16),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            title,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
          ),
        ],
      ),
    );
  }

  Widget _buildAssignmentGrid() {
    if (homeworks.isEmpty) {
      return const Center(child: Text('No assignments due.'));
    }

    return GridView.builder(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemCount: homeworks.length > 4 ? 4 : homeworks.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 14,
        mainAxisSpacing: 14,
        childAspectRatio: 1.35,
      ),
      itemBuilder: (context, index) {
        final hw = homeworks[index];
        return _buildAssignmentTile(hw);
      },
    );
  }

  Widget _buildAssignmentTile(HomeworkItem hw) {
    Color bgGradient1;
    Color bgGradient2;

    switch (hw.colorTheme) {
      case 'blue':
        bgGradient1 = const Color(0xFF384E77);
        bgGradient2 = const Color(0xFF283B5E);
        break;
      case 'amber':
        bgGradient1 = const Color(0xFFFB923C);
        bgGradient2 = const Color(0xFFF97316);
        break;
      case 'teal':
        bgGradient1 = const Color(0xFF34D399);
        bgGradient2 = const Color(0xFF059669);
        break;
      case 'orange':
      default:
        bgGradient1 = const Color(0xFFF87171);
        bgGradient2 = const Color(0xFFEF4444);
        break;
    }

    IconData iconData = Icons.menu_book_rounded;
    if (hw.subject.toLowerCase().contains('math')) iconData = Icons.calculate_outlined;
    if (hw.subject.toLowerCase().contains('sci')) iconData = Icons.science_outlined;
    if (hw.subject.toLowerCase().contains('ict') || hw.subject.toLowerCase().contains('hindi')) {
      iconData = Icons.terminal_rounded;
    }

    return InkWell(
      onTap: onHomeworkTap != null ? () => onHomeworkTap!(hw) : null,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [bgGradient1, bgGradient2],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: bgGradient2.withOpacity(0.3),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                if (hw.status == 'overdue')
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEF4444),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      AppStrings.tr('overdue'),
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  )
                else if (hw.status == 'submitted')
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFF10B981),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      AppStrings.tr('submitted'),
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  )
                else
                  const SizedBox.shrink(),
                Icon(iconData, color: Colors.white.withOpacity(0.85), size: 22),
              ],
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  hw.subject,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${AppStrings.tr('submission_date')}: ${hw.dueDate}',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.85),
                    fontSize: 10,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAttendanceActivityCard() {
    return InkWell(
      onTap: () => onNavigateToTab(2),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: const Color(0xFFECFDF5),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.verified_user_rounded, color: Color(0xFF10B981), size: 24),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Wrap(
                    crossAxisAlignment: WrapCrossAlignment.center,
                    spacing: 6,
                    runSpacing: 4,
                    children: [
                      Text(
                        AppStrings.tr('attr_attendance'),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD1FAE5),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          todayAttendanceStatus.toLowerCase() == 'present'
                              ? AppStrings.tr('present')
                              : todayAttendanceStatus.toUpperCase(),
                          style: const TextStyle(color: Color(0xFF065F46), fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(
                    todayAttendanceRemarks,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        '$attendancePercentage%',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF10B981)),
                      ),
                      const SizedBox(width: 2),
                      const Icon(Icons.chevron_right_rounded, size: 16, color: Colors.grey),
                    ],
                  ),
                  Text(
                    AppStrings.tr('monthly_rate'),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 10),
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
