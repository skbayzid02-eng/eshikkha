import 'package:flutter/material.dart';

void main() {
  runApp(const EShikkhaTeacherApp());
}

class EShikkhaTeacherApp extends StatূelessWidget {
  const EShikkhaTeacherApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'eShikkha Teacher',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF059669),
          primary: const Color(0xFF059669),
          secondary: const Color(0xFF0D9488),
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8FAFC),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: Color(0xFF0F172A),
          elevation: 0,
          centerTitle: false,
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: Color(0xFFE2E8F0)),
          ),
          color: Colors.white,
        ),
      ),
      home: const TeacherLoginScreen(),
    );
  }
}

// ==========================================
// 1. TEACHER LOGIN SCREEN
// ==========================================
class TeacherLoginScreen extends StatefulWidget {
  const TeacherLoginScreen({super.key});

  @override
  State<TeacherLoginScreen> createState() => _TeacherLoginScreenState();
}

class _TeacherLoginScreenState extends State<TeacherLoginScreen> {
  final _emailController = TextEditingController(text: 'teacher@drmc.edu.bd');
  final _passwordController = TextEditingController(text: 'password');
  bool _isLoading = false;

  void _login() {
    setState(() => _isLoading = true);
    Future.delayed(const Duration(milliseconds: 600), () {
      if (mounted) {
        setState(() => _isLoading = false);
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const TeacherMainNavigation()),
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Logo & Header
                Center(
                  child: Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF059669), Color(0xFF0D9488)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF059669).withValues(alpha: 0.3),
                          blurRadius: 15,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: const Icon(Icons.school, color: Colors.white, size: 36),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'ই-শিক্ষা শিক্ষক অ্যাপ',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF0F172A),
                  ),
                ),
                const Text(
                  'eShikkha Teacher Portal • বাংলাদেশ',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                ),
                const SizedBox(height: 32),

                // Form fields
                TextField(
                  controller: _emailController,
                  decoration: InputDecoration(
                    labelText: 'শিক্ষকের ইমেইল বা আইডি',
                    prefixIcon: const Icon(Icons.person_outline),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: 'পাসওয়ার্ড',
                    prefixIcon: const Icon(Icons.lock_outline),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 24),

                ElevatedButton(
                  onPressed: _isLoading ? null : _login,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    elevation: 2,
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text(
                          'শিক্ষক প্যানেলে প্রবেশ করুন',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                ),

                const SizedBox(height: 24),
                // Demo Hint Card
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: const Column(
                    children: [
                      Text(
                        '💡 ডেমো শিক্ষক একাউন্ট',
                        style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF065F46), fontSize: 13),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'মুহাম্মদ রফিকুল ইসলাম (DRMC - গণিত বিভাগ)\nআইডি: teacher@drmc.edu.bd',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Color(0xFF047857), fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ==========================================
// 2. MAIN BOTTOM NAVIGATION
// ==========================================
class TeacherMainNavigation extends StatefulWidget {
  const TeacherMainNavigation({super.key});

  @override
  State<TeacherMainNavigation> createState() => _TeacherMainNavigationState();
}

class _TeacherMainNavigationState extends State<TeacherMainNavigation> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    TeacherDashboardTab(),
    TeacherAttendanceTab(),
    TeacherMarksTab(),
    TeacherNoticesTab(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_currentIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (idx) => setState(() => _currentIndex = idx),
        indicatorColor: const Color(0xFFD1FAE5),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined),
            selectedIcon: Icon(Icons.dashboard, color: Color(0xFF059669)),
            label: 'ড্যাশবোর্ড',
          ),
          NavigationDestination(
            icon: Icon(Icons.how_to_reg_outlined),
            selectedIcon: Icon(Icons.how_to_reg, color: Color(0xFF059669)),
            label: 'হাজিরা গ্রহণ',
          ),
          NavigationDestination(
            icon: Icon(Icons.grade_outlined),
            selectedIcon: Icon(Icons.grade, color: Color(0xFF059669)),
            label: 'নম্বর এন্ট্রি',
          ),
          NavigationDestination(
            icon: Icon(Icons.notifications_outlined),
            selectedIcon: Icon(Icons.notifications, color: Color(0xFF059669)),
            label: 'নোটিশ',
          ),
        ],
      ),
    );
  }
}

// ==========================================
// 3. TAB 1: TEACHER DASHBOARD
// ==========================================
class TeacherDashboardTab extends StatelessWidget {
  const TeacherDashboardTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('শিক্ষক ড্যাশবোর্ড', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, size: 20, color: Color(0xFFE11D48)),
            onPressed: () {
              Navigator.of(context).pushReplacement(
                MaterialPageRoute(builder: (_) => const TeacherLoginScreen()),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Teacher Profile Card
            Container(
              padding: const EdgeInsets.all(20),
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
                        radius: 26,
                        backgroundColor: const Color(0xFF059669),
                        child: const Text('MR', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'মুহাম্মদ রফিকুল ইসলাম',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                            const Text(
                              'সিনিয়র সহকারী শিক্ষক (গণিত)',
                              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                            ),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFF059669).withValues(alpha: 0.3),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: const Color(0xFF059669).withValues(alpha: 0.5)),
                              ),
                              child: const Text(
                                'ঢাকা রেসিডেনসিয়াল মডেল কলেজ',
                                style: TextStyle(color: Color(0xFF34D399), fontSize: 10, fontWeight: FontWeight.w600),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const Divider(color: Color(0xFF334155), height: 28),
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('আইডি: EMP-DRMC-01', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                      Text('আজকের তারিখ: ১৮ নভেম্বর ২০২৬', style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Quick Stats
            Row(
              children: [
                Expanded(
                  child: _buildMetricCard(
                    title: 'আজকের ক্লাস',
                    value: '৪ টি',
                    icon: Icons.calendar_today,
                    color: const Color(0xFF0284C7),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildMetricCard(
                    title: 'মোট শিক্ষার্থী',
                    value: '১২০ জন',
                    icon: Icons.groups,
                    color: const Color(0xFF059669),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildMetricCard(
                    title: 'হাজিরা সম্পন্ন',
                    value: '৩/৪',
                    icon: Icons.check_circle,
                    color: const Color(0xFFD97706),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Today's Routine Schedule
            const Text(
              'আজকের ক্লাস রুটিন (Today Schedule)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 12),

            _buildRoutineItem(
              time: '০৮:০০ - ০৮:৪৫',
              className: 'দশম শ্রেণি (পদ্মা শাখা)',
              subject: 'উচ্চতর গণিত',
              room: 'রুম ৩০১',
              status: 'সম্পন্ন (Done)',
              isDone: true,
            ),
            _buildRoutineItem(
              time: '০৯:৪০ - ১০:২৫',
              className: 'নবম শ্রেণি (মেঘনা শাখা)',
              subject: 'সাধারণ গণিত',
              room: 'রুম ২০৪',
              status: 'চলমান (Running)',
              isDone: false,
            ),
            _buildRoutineItem(
              time: '১১:০০ - ১১:৪৫',
              className: 'একাদশ শ্রেণি (Science A)',
              subject: 'ক্যালকুলাস ও জ্যামিতি',
              room: 'রুম ৫০১',
              status: 'আসন্ন (Upcoming)',
              isDone: false,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetricCard({required String title, required String value, required IconData icon, required Color color}) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 8),
          Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 2),
          Text(title, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
        ],
      ),
    );
  }

  Widget _buildRoutineItem({
    required String time,
    required String className,
    required String subject,
    required String room,
    required String status,
    required bool isDone,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            decoration: BoxDecoration(
              color: isDone ? const Color(0xFFECFDF5) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              time,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: isDone ? const Color(0xFF059669) : const Color(0xFF475569),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(subject, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                Text('$className • $room', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: isDone ? const Color(0xFFD1FAE5) : const Color(0xFFFEF3C7),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              status,
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: isDone ? const Color(0xFF047857) : const Color(0xFFB45309),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// 4. TAB 2: TAKE ATTENDANCE TAB
// ==========================================
class TeacherAttendanceTab extends StatefulWidget {
  const TeacherAttendanceTab({super.key});

  @override
  State<TeacherAttendanceTab> createState() => _TeacherAttendanceTabState();
}

class _TeacherAttendanceTabState extends State<TeacherAttendanceTab> {
  String _selectedClass = 'দশম শ্রেণি (পদ্মা শাখা)';
  final List<Map<String, dynamic>> _students = [
    {'roll': 1, 'name': 'তানভীর আহমেদ', 'status': 'present'},
    {'roll': 2, 'name': 'মাহমুদুল হাসান', 'status': 'present'},
    {'roll': 3, 'name': 'সাদিয়া ইসলাম রিমা', 'status': 'absent'},
    {'roll': 4, 'name': 'আরিফুল ইসলাম', 'status': 'present'},
    {'roll': 5, 'name': 'নুসরাত জাহান', 'status': 'present'},
    {'roll': 6, 'name': 'সাইফুল হক', 'status': 'late'},
  ];

  void _saveAttendance() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('✅ উপস্থিতি সফলভাবে সংরক্ষিত হয়েছে এবং অনুপস্থিত শিক্ষার্থীদের অভিভাবককে SMS পাঠানো হয়েছে!'),
        backgroundColor: Color(0xFF059669),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('হাজিরা গ্রহণ (Take Attendance)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
      ),
      body: Column(
        children: [
          // Filter Card
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String>(
                    value: _selectedClass,
                    decoration: InputDecoration(
                      labelText: 'শ্রেণি ও শাখা',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'দশম শ্রেণি (পদ্মা শাখা)', child: Text('দশম শ্রেণি (পদ্মা শাখা)')),
                      DropdownMenuItem(value: 'নবম শ্রেণি (মেঘনা শাখা)', child: Text('নবম শ্রেণি (মেঘনা শাখা)')),
                    ],
                    onChanged: (val) => setState(() => _selectedClass = val!),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          // Students List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _students.length,
              itemBuilder: (context, index) {
                final st = _students[index];
                final status = st['status'] as String;

                return Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: const Color(0xFFF1F5F9),
                          child: Text('#${st['roll']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF334155))),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(st['name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        ),
                        // Status Buttons
                        _buildStatusBtn(index, 'present', 'P', Colors.green, status == 'present'),
                        const SizedBox(width: 6),
                        _buildStatusBtn(index, 'absent', 'A', Colors.red, status == 'absent'),
                        const SizedBox(width: 6),
                        _buildStatusBtn(index, 'late', 'L', Colors.amber, status == 'late'),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),

          // Submit Bottom Bar
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: ElevatedButton.icon(
              onPressed: _saveAttendance,
              icon: const Icon(Icons.cloud_upload_outlined),
              label: const Text('হাজিরা সম্পন্ন করুন', style: TextStyle(fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                minimumSize: const Size.fromHeight(50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBtn(int index, String statusKey, String label, Color color, bool isSelected) {
    return InkWell(
      onTap: () {
        setState(() {
          _students[index]['status'] = statusKey;
        });
      },
      borderRadius: BorderRadius.circular(8),
      child: Container(
        width: 34,
        height: 34,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: isSelected ? color : color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 12,
            color: isSelected ? Colors.white : color,
          ),
        ),
      ),
    );
  }
}

// ==========================================
// 5. TAB 3: ENTER MARKS TAB
// ==========================================
class TeacherMarksTab extends StatefulWidget {
  const TeacherMarksTab({super.key});

  @override
  State<TeacherMarksTab> createState() => _TeacherMarksTabState();
}

class _TeacherMarksTabState extends State<TeacherMarksTab> {
  final List<Map<String, dynamic>> _marksData = [
    {'name': 'তানভীর আহমেদ', 'roll': 1, 'marks': '86', 'grade': 'A+', 'gpa': '5.00'},
    {'name': 'মাহমুদুল হাসান', 'roll': 2, 'marks': '78', 'grade': 'A', 'gpa': '4.00'},
    {'name': 'সাদিয়া ইসলাম রিমা', 'roll': 3, 'marks': '92', 'grade': 'A+', 'gpa': '5.00'},
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('পরীক্ষার নম্বর এন্ট্রি', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDF4),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFBBF7D0)),
            ),
            child: const Row(
              children: [
                Icon(Icons.assignment, color: Color(0xFF16A34A)),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'অর্ধবার্ষিক মূল্যায়ন ২০২৬ • উচ্চতর গণিত (১০০ নম্বর)',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF15803D)),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          ..._marksData.map((item) {
            return Card(
              margin: const EdgeInsets.only(bottom: 10),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 16,
                      backgroundColor: const Color(0xFFF1F5F9),
                      child: Text('#${item['roll']}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item['name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                          Text('GPA: ${item['gpa']}', style: const TextStyle(color: Color(0xFF64748B), fontSize: 12)),
                        ],
                      ),
                    ),
                    SizedBox(
                      width: 60,
                      child: TextFormField(
                        initialValue: item['marks'],
                        keyboardType: TextInputType.number,
                        textAlign: TextAlign.center,
                        decoration: InputDecoration(
                          contentPadding: const EdgeInsets.symmetric(vertical: 8),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFD1FAE5),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        item['grade'],
                        style: const TextStyle(color: Color(0xFF065F46), fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                    ),
                  ],
                ),
              ),
            );
          }),

          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('✅ নম্বর ও জিপিএ সফলভাবে সংরক্ষিত হয়েছে!'), backgroundColor: Color(0xFF059669)),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('নম্বর সাবমিট করুন', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// 6. TAB 4: TEACHER NOTICES TAB
// ==========================================
class TeacherNoticesTab extends StatelessWidget {
  const TeacherNoticesTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('নোটিশ ও বার্তা', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildNoticeCard(
            title: 'জাতীয় শিক্ষা সপ্তাহ ২০২৬ নিবন্ধন শুরু',
            content: 'শিক্ষা অধিদপ্তর কর্তৃক আয়োজিত জাতীয় শিক্ষা সপ্তাহে প্রতিষ্ঠানের শিক্ষকদের সক্রিয় অংশগ্রহণের জন্য অনুরোধ জানানো হচ্ছে।',
            date: '১৮ নভেম্বর ২০২৬',
            isGlobal: true,
          ),
          _buildNoticeCard(
            title: 'বার্ষিক বিজ্ঞান মেলা ও রোবোটিক্স উৎসব',
            content: 'বিজ্ঞান বিভাগের সকল শিক্ষককে শিক্ষার্থীদের প্রজেক্ট প্রস্তুত করার জন্য গাইডলাইন প্রদানের নির্দেশ দেওয়া হলো।',
            date: '১৬ নভেম্বর ২০২৬',
            isGlobal: false,
          ),
        ],
      ),
    );
  }

  Widget _buildNoticeCard({required String title, required String content, required String date, required bool isGlobal}) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: isGlobal ? const Color(0xFFFEF3C7) : const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    isGlobal ? 'সুপার অ্যাডমিন জাতীয় বার্তা' : 'ক্যাম্পাস নোটিশ',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: isGlobal ? const Color(0xFF92400E) : const Color(0xFF065F46),
                    ),
                  ),
                ),
                Text(date, style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
              ],
            ),
            const SizedBox(height: 10),
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A))),
            const SizedBox(height: 6),
            Text(content, style: const TextStyle(fontSize: 13, color: Color(0xFF475569), height: 1.4)),
          ],
        ),
      ),
    );
  }
}
