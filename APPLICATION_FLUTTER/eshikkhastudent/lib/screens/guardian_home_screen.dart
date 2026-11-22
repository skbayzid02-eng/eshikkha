import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/child_model.dart';
import '../services/api_service.dart';
import 'add_child_screen.dart';
import 'child_detail_screen.dart';
import 'settings_screen.dart';
import 'student_dashboard_screen.dart';

class GuardianHomeScreen extends StatefulWidget {
  const GuardianHomeScreen({super.key});

  @override
  State<GuardianHomeScreen> createState() => _GuardianHomeScreenState();
}

class _GuardianHomeScreenState extends State<GuardianHomeScreen> {
  List<ChildSummary> _children = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadChildren();
  }

  Future<void> _loadChildren() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getChildren();
    if (mounted) {
      setState(() {
        _children = data;
        _isLoading = false;
      });
    }
  }

  void _openChildDetail(ChildSummary child) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ChildDetailScreen(
          child: child,
          allChildren: _children,
        ),
      ),
    );
  }

  void _openAddChild() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const AddChildScreen()),
    );
    if (result == true) {
      _loadChildren();
    }
  }

  void _openSettings() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const SettingsScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, lang, child) {
        return Scaffold(
          appBar: AppBar(
            title: Text(
              AppStrings.tr('guardian_dashboard'),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            actions: [
              // Switch to Student Portal
              InkWell(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const StudentDashboardScreen(studentId: 2),
                    ),
                  );
                },
                borderRadius: BorderRadius.circular(20),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  margin: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFF86EFAC)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.school, size: 14, color: Color(0xFF16A34A)),
                      const SizedBox(width: 4),
                      Text(
                        AppStrings.tr('student_portal'),
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF16A34A)),
                      ),
                    ],
                  ),
                ),
              ),
              // Language Switcher Quick Pill
              InkWell(
                onTap: () {
                  AppLanguage.toggleLanguage();
                },
                borderRadius: BorderRadius.circular(20),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  margin: const EdgeInsets.symmetric(vertical: 10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.language, size: 14, color: Color(0xFF2563EB)),
                      const SizedBox(width: 4),
                      Text(
                        lang == 'en' ? 'EN' : 'বাং',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                      ),
                    ],
                  ),
                ),
              ),
              IconButton(
                icon: const Icon(Icons.settings_outlined),
                tooltip: AppStrings.tr('settings'),
                onPressed: _openSettings,
              ),
            ],
          ),
          body: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _loadChildren,
                  child: ListView(
                    padding: const EdgeInsets.all(16.0),
                    children: [
                      // Guardian Profile Header Card
                      _buildGuardianHeaderCard(),
                      const SizedBox(height: 20),

                      // Section Title & Add Child Button
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                AppStrings.tr('my_children'),
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              Text(
                                '${_children.length} ${AppStrings.tr('all_children')}',
                                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                          ElevatedButton.icon(
                            onPressed: _openAddChild,
                            icon: const Icon(Icons.person_add_alt_1, size: 16),
                            label: Text(
                              AppStrings.tr('add_child'),
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF2563EB),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // List of Child Cards with 9 Attributes
                      ..._children.map((child) => _buildChildCard(child)),
                    ],
                  ),
                ),
          floatingActionButton: FloatingActionButton.extended(
            onPressed: _openAddChild,
            backgroundColor: const Color(0xFF2563EB),
            foregroundColor: Colors.white,
            icon: const Icon(Icons.add),
            label: Text(AppStrings.tr('add_child')),
          ),
        );
      },
    );
  }

  // Guardian Top Profile Banner
  Widget _buildGuardianHeaderCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.1),
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
              CircleAvatar(
                radius: 24,
                backgroundColor: const Color(0xFF2563EB),
                child: const Icon(Icons.family_restroom, color: Colors.white, size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      AppStrings.tr('guardian_name'),
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    Text(
                      AppStrings.tr('guardian_subtitle'),
                      style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const Divider(color: Color(0xFF334155), height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Phone: 01711000000',
                style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11, fontFamily: 'monospace'),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'Verified Guardian',
                  style: TextStyle(color: Color(0xFF34D399), fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // The Child Card showing all 9 mandatory attributes
  Widget _buildChildCard(ChildSummary child) {
    Color typeColor;
    IconData typeIcon;
    switch (child.institutionType) {
      case 'madrasah':
        typeColor = const Color(0xFFD97706);
        typeIcon = Icons.mosque;
        break;
      case 'college':
        typeColor = const Color(0xFF0284C7);
        typeIcon = Icons.account_balance;
        break;
      case 'university':
        typeColor = const Color(0xFF7C3AED);
        typeIcon = Icons.school;
        break;
      default:
        typeColor = const Color(0xFF059669);
        typeIcon = Icons.location_city;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: InkWell(
        onTap: () => _openChildDetail(child),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Bar: Institution Name & Type Badge (Attribute 3)
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: typeColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(typeIcon, color: typeColor, size: 16),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          child.institutionName,
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: typeColor),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          '${AppStrings.tr(child.institutionType)} • ${child.institutionCode}',
                          style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),
                  ),
                  // Relationship Tag
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      AppStrings.tr(child.relationship),
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
                    ),
                  ),
                ],
              ),
              const Divider(height: 20),

              // Student Main Info: Photo (1), Name (2), Class/Semester (4)
              Row(
                children: [
                  // 1. Photo
                  CircleAvatar(
                    radius: 26,
                    backgroundColor: const Color(0xFFDBEAFE),
                    child: Text(
                      child.name.isNotEmpty ? child.name[0] : 'S',
                      style: const TextStyle(color: Color(0xFF1E40AF), fontWeight: FontWeight.bold, fontSize: 18),
                    ),
                  ),
                  const SizedBox(width: 14),

                  // 2. Name & 4. Class / Semester
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          child.name,
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${child.classOrSemester} • ${AppStrings.tr('roll')}: #${child.rollNumber}',
                          style: const TextStyle(fontSize: 12, color: Color(0xFF475569), fontWeight: FontWeight.w600),
                        ),
                        Text(
                          'ID: ${child.admissionNumber}',
                          style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontFamily: 'monospace'),
                        ),
                      ],
                    ),
                  ),

                  // Notification Badges (8 & 9)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      // 9. New Messages
                      if (child.newMessagesCount > 0)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          margin: const EdgeInsets.only(bottom: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEEF2FF),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFC7D2FE)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.chat_bubble_outline, size: 10, color: Color(0xFF4338CA)),
                              const SizedBox(width: 4),
                              Text(
                                '${child.newMessagesCount} ${AppStrings.tr('attr_messages')}',
                                style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFF4338CA)),
                              ),
                            ],
                          ),
                        ),

                      // 8. New Notices
                      if (child.newNoticesCount > 0)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFFDE68A)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.notifications_active_outlined, size: 10, color: Color(0xFFB45309)),
                              const SizedBox(width: 4),
                              Text(
                                '${child.newNoticesCount} ${AppStrings.tr('attr_notices')}',
                                style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 14),

              // KPI Grid: Attendance (5), Result (6), Fees (7)
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  children: [
                    // 5. Today's Attendance
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            AppStrings.tr('attr_attendance'),
                            style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                  color: Color(0xFF10B981),
                                  shape: BoxShape.circle,
                                ),
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  AppStrings.tr(child.todayAttendanceStatus),
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF065F46),
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          Text(
                            child.todayAttendanceTime,
                            style: const TextStyle(fontSize: 9, color: Color(0xFF94A3B8)),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    Container(height: 36, width: 1, color: const Color(0xFFCBD5E1)),
                    const SizedBox(width: 10),

                    // 6. Latest Result
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            AppStrings.tr('attr_result'),
                            style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'GPA ${child.latestGpa.toStringAsFixed(2)} (${child.latestGrade})',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                          ),
                          Text(
                            child.examTitle,
                            style: const TextStyle(fontSize: 9, color: Color(0xFF94A3B8)),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    Container(height: 36, width: 1, color: const Color(0xFFCBD5E1)),
                    const SizedBox(width: 10),

                    // 7. Due Fees
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            AppStrings.tr('attr_fees'),
                            style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                          ),
                          const SizedBox(height: 4),
                          if (child.dueFeesCount > 0)
                            Text(
                              '৳ ${child.dueFeesAmount.toInt()} ${AppStrings.tr('unpaid')}',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFFE11D48)),
                            )
                          else
                            Text(
                              AppStrings.tr('no_dues'),
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                            ),
                          Text(
                            child.dueFeesCount > 0 ? '${child.dueFeesCount} Invoice' : 'All Paid',
                            style: const TextStyle(fontSize: 9, color: Color(0xFF94A3B8)),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 10),
              // Card Click Action Hint
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    AppStrings.tr('view_full_dashboard'),
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                  ),
                  const SizedBox(width: 4),
                  const Icon(Icons.arrow_forward_ios, size: 10, color: Color(0xFF2563EB)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
