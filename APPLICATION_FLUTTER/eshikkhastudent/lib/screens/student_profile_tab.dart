import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/student_model.dart';
import '../services/api_service.dart';
import 'student_login_screen.dart';
import 'guardian_home_screen.dart';

class StudentProfileTab extends StatefulWidget {
  final StudentProfile? profile;
  final Map<String, dynamic>? profileHub;
  final int studentId;
  final bool isClass1;
  final List<Map<String, dynamic>> notices;
  final List<Map<String, dynamic>> messages;
  final double dueFeesAmount;
  final List<Map<String, dynamic>> recentFees;
  final void Function(double due, Map<String, dynamic> newHistory)? onFeePaid;

  const StudentProfileTab({
    super.key,
    required this.profile,
    required this.profileHub,
    required this.studentId,
    this.isClass1 = false,
    this.notices = const [],
    this.messages = const [],
    this.dueFeesAmount = 3500.0,
    this.recentFees = const [],
    this.onFeePaid,
  });

  @override
  State<StudentProfileTab> createState() => _StudentProfileTabState();
}

class _StudentProfileTabState extends State<StudentProfileTab> {
  late double _dueFeesAmount;
  late List<Map<String, dynamic>> _profileRecentFees;
  bool _pushNotificationsEnabled = true;
  bool _smsAlertsEnabled = true;
  bool _emailReportsEnabled = false;
  bool _biometricEnabled = true;

  Map<String, dynamic>? get _profileHub => widget.profileHub;
  StudentProfile? get _profile => widget.profile;
  bool get _isClass1 => widget.isClass1;
  List<Map<String, dynamic>> get _notices => widget.notices;
  List<Map<String, dynamic>> get _messages => widget.messages;

  @override
  void initState() {
    super.initState();
    _dueFeesAmount = widget.dueFeesAmount;
    _profileRecentFees = List<Map<String, dynamic>>.from(widget.recentFees);
  }

  @override
  void didUpdateWidget(covariant StudentProfileTab oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.dueFeesAmount != widget.dueFeesAmount) {
      _dueFeesAmount = widget.dueFeesAmount;
    }
    if (oldWidget.recentFees != widget.recentFees) {
      _profileRecentFees = List<Map<String, dynamic>>.from(widget.recentFees);
    }
  }

  @override
  Widget build(BuildContext context) {
    final studentData = (_profileHub?['student'] as Map<String, dynamic>?) ?? {};
    final idCardData = (_profileHub?['student_id_card'] as Map<String, dynamic>?) ?? {};
    final instData = (_profileHub?['institution'] as Map<String, dynamic>?) ?? {};
    final guardianData = (_profileHub?['guardian'] as Map<String, dynamic>?) ?? {};
    final feesData = (_profileHub?['fees_and_payments'] as Map<String, dynamic>?) ?? {};
    final notifData = (_profileHub?['notifications'] as Map<String, dynamic>?) ?? {};
    final msgData = (_profileHub?['messages'] as Map<String, dynamic>?) ?? {};
    final supportData = (_profileHub?['support'] as Map<String, dynamic>?) ?? {};

    final studentName = studentData['name'] ?? _profile?.name ?? 'তানভীর আহমেদ (Tanvir Ahmed)';
    final studentFather = studentData['father_name'] ?? guardianData['father_name'] ?? _profile?.fatherName ?? (_isClass1 ? 'মাহমুদুল হাসান (Mahmudul Hasan)' : 'মোঃ রফিকুল ইসলাম (Md. Rafiqul Islam)');
    final studentMother = studentData['mother_name'] ?? guardianData['mother_name'] ?? _profile?.motherName ?? (_isClass1 ? 'আমিনা বেগম (Amina Begum)' : 'আমিনা বেগম (Amina Begum)');
    final studentAdm = studentData['admission_number'] ?? _profile?.admissionNumber ?? 'DRMC-2024-0801';
    final studentRoll = studentData['roll_number'] ?? (_profile != null ? '${_profile!.rollNumber}' : '05');
    final studentClass = studentData['class_name'] ?? _profile?.className ?? 'দশম শ্রেণি (Class 10)';
    final studentSection = studentData['section_name'] ?? _profile?.sectionName ?? 'বিজ্ঞান - ক (Science A)';
    final studentBlood = studentData['blood_group'] ?? _profile?.bloodGroup ?? 'B+';
    final totalFees = (feesData['total_fees'] as num?)?.toDouble() ?? 12500.0;
    final paidFees = (feesData['paid_amount'] as num?)?.toDouble() ?? 9000.0;
    final notificationsList = (notifData['items'] as List?) ?? _notices;
    final teacherMessagesList = (msgData['items'] as List?) ?? _messages;
    final faqList = (supportData['faqs'] as List?) ?? [
      {
        'q': 'বকেয়া ফি কীভাবে bKash বা Nagad দিয়ে পরিশোধ করব?',
        'a': 'ফি ও পেমেন্ট সেকশনে গিয়ে "অনলাইনে ফি পরিশোধ করুন" বাটনে ট্যাপ করুন। এরপর bKash বা Nagad নির্বাচন করে আপনার একাউন্ট নম্বর দিন এবং নিশ্চিত করুন। সাথে সাথে ডিজিটাল ইনভয়েস জেনারেট হবে।'
      },
      {
        'q': 'উপস্থিতিতে কোনো ভুল তথ্য দেখালে কী করব?',
        'a': 'প্রতিষ্ঠান হটলাইন ১৬৩০০ অথবা আপনার শ্রেণি শিক্ষকের কাছে অ্যাপের মেসেজ সেকশন থেকে সরাসরি বার্তা পাঠিয়ে সংশোধনের আবেদন করতে পারেন।'
      },
      {
        'q': 'অফিসিয়াল মার্কশিট বা প্রশংসাপত্র কীভাবে ডাউনলোড করব?',
        'a': 'ফলাফল ট্যাব বা একাডেমিক ডিটেইলস সেকশনে গিয়ে "অফিসিয়াল মার্কশিট PDF ডাউনলোড" বাটনে ট্যাপ করে প্রিন্ট ও ডাউনলোড করতে পারবেন।'
      },
      {
        'q': 'ছুটির জন্য কীভাবে আবেদন করতে হয়?',
        'a': 'উপস্থিতি ট্যাবের নিচে "ছুটির আবেদন করুন" বাটনে ক্লিক করে ছুটির ধরন, শুরুর তারিখ, শেষের তারিখ ও কারণ লিখে সাবমিট করুন।'
      },
    ];

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Profile Title
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  AppLanguage.isBangla ? 'শিক্ষার্থীর পূর্ণাঙ্গ প্রোফাইল' : AppStrings.tr('student_profile'),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.verified_rounded, size: 14, color: Color(0xFF059669)),
                    const SizedBox(width: 4),
                    Text(
                      AppLanguage.isBangla ? 'ভেরিফাইড' : 'Verified',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Top Header Summary Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF4F46E5), Color(0xFF6366F1)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF4F46E5).withValues(alpha: 0.25),
                  blurRadius: 16,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Row(
              children: [
                Stack(
                  children: [
                    CircleAvatar(
                      radius: 36,
                      backgroundColor: Colors.white,
                      child: CircleAvatar(
                        radius: 33,
                        backgroundColor: const Color(0xFFEEF2FF),
                        child: Text(
                          studentName.isNotEmpty ? studentName[0] : 'T',
                          style: const TextStyle(
                            fontSize: 28,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF4F46E5),
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: Color(0xFF10B981),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.check, size: 12, color: Colors.white),
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        studentName,
                        style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${AppLanguage.isBangla ? 'ভর্তি নম্বর' : 'Admission ID'}: $studentAdm',
                        style: const TextStyle(color: Color(0xFFE0E7FF), fontSize: 12),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$studentClass ($studentSection) • ${AppLanguage.isBangla ? 'রোল' : 'Roll'}: $studentRoll',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 12),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${AppLanguage.isBangla ? 'রক্তের গ্রুপ' : 'Blood Group'}: $studentBlood',
                        style: const TextStyle(color: Color(0xFFFEF08A), fontWeight: FontWeight.bold, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // --------------------------------------------------------
          // 1. শিক্ষার্থীর ব্যক্তিগত তথ্য (Personal Info Card)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.person_outline_rounded,
            title: AppStrings.tr('personal_info_section'),
            iconColor: const Color(0xFF2563EB),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow(AppLanguage.isBangla ? 'পূর্ণ নাম' : 'Full Name', studentName),
                _buildInfoRow(AppLanguage.isBangla ? 'পিতার নাম' : "Father's Name", studentFather),
                _buildInfoRow(AppLanguage.isBangla ? 'মাতার নাম' : "Mother's Name", studentMother),
                _buildInfoRow(AppLanguage.isBangla ? 'জন্ম তারিখ' : 'Date of Birth', studentData['date_of_birth'] ?? studentData['dob'] ?? '2019-01-15'),
                _buildInfoRow(AppLanguage.isBangla ? 'রক্তের গ্রুপ' : 'Blood Group', studentBlood),
                _buildInfoRow(AppLanguage.isBangla ? 'লিঙ্গ' : 'Gender', studentData['gender'] ?? 'ছেলে (Male)'),
                _buildInfoRow(AppLanguage.isBangla ? 'ধর্ম' : 'Religion', studentData['religion'] ?? 'ইসলাম (Islam)'),
                _buildInfoRow(AppLanguage.isBangla ? 'বর্তমান ঠিকানা' : 'Address', studentData['address'] ?? _profile?.address ?? 'বাড়ি ১২, রোড ৪, মোহাম্মদপুর, ঢাকা'),
                _buildInfoRow(AppLanguage.isBangla ? 'মোবাইল' : 'Mobile Phone', studentData['phone'] ?? '+880 1711-234567'),
                _buildInfoRow(AppLanguage.isBangla ? 'ইমেইল' : 'Email', studentData['email'] ?? 'tanvir.student@drmc.edu.bd'),
                _buildInfoRow(AppLanguage.isBangla ? 'জরুরি যোগাযোগ' : 'Emergency Contact', studentData['emergency_contact'] ?? '+880 1812-987654'),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 2. Student ID (ডিজিটাল স্টুডেন্ট আইডি কার্ড)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.badge_rounded,
            title: AppStrings.tr('student_id_section'),
            iconColor: const Color(0xFF0D9488),
            trailing: TextButton.icon(
              onPressed: () => _showDigitalIdModal(idCardData, studentData, instData),
              icon: const Icon(Icons.fullscreen_rounded, size: 18, color: Color(0xFF0D9488)),
              label: Text(
                AppLanguage.isBangla ? 'পূর্ণাঙ্গ কার্ড' : 'Full Card',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0D9488)),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Plastic ID Card Preview
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.15),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(6),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.1),
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(Icons.school, size: 16, color: Color(0xFF38BDF8)),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    instData['institution_code'] ?? 'DRMC-01',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0284C7).withValues(alpha: 0.3),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.4)),
                            ),
                            child: const Text(
                              'SMART PVC ID',
                              style: TextStyle(color: Color(0xFF38BDF8), fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.8),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Container(
                            width: 60,
                            height: 60,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                            ),
                            child: const Icon(Icons.person, size: 38, color: Colors.white70),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  studentName,
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'ID: ${idCardData['card_no'] ?? 'SID-8801202405'}',
                                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                                ),
                                Text(
                                  '$studentClass | Roll: $studentRoll',
                                  style: const TextStyle(color: Color(0xFFE2E8F0), fontSize: 11),
                                ),
                                Text(
                                  'Session: ${idCardData['academic_session'] ?? '2024-2025'}',
                                  style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 10, fontWeight: FontWeight.w600),
                                ),
                              ],
                            ),
                          ),
                          // Simulated QR icon
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(Icons.qr_code_2_rounded, size: 36, color: Color(0xFF0F172A)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      // Simulated Barcode
                      Container(
                        width: double.infinity,
                        height: 24,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: List.generate(
                            28,
                            (index) => Container(
                              width: (index % 3 == 0) ? 3 : (index % 2 == 0) ? 2 : 1,
                              color: Colors.black87,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _showDigitalIdModal(idCardData, studentData, instData),
                    icon: const Icon(Icons.visibility_outlined, size: 18),
                    label: Text(AppLanguage.isBangla ? 'ডিজিটাল আইডি কার্ড ও QR কোড দেখুন' : 'View Digital ID & QR Code'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0F172A),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 3. প্রতিষ্ঠানের তথ্য (Institution Info Card)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.account_balance_rounded,
            title: AppStrings.tr('institution_info_section'),
            iconColor: const Color(0xFF4338CA),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow(AppLanguage.isBangla ? 'প্রতিষ্ঠানের নাম' : 'Institution Name', instData['name'] ?? _profile?.institutionName ?? 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ (DRMC)'),
                _buildInfoRow(AppLanguage.isBangla ? 'EIIN নম্বর' : 'EIIN Number', instData['eiin'] ?? _profile?.institutionEiin ?? '107980'),
                _buildInfoRow(AppLanguage.isBangla ? 'প্রতিষ্ঠান কোড' : 'Institute Code', instData['institution_code'] ?? _profile?.institutionCode ?? 'DRMC-01'),
                _buildInfoRow(AppLanguage.isBangla ? 'ক্যাম্পাস ও অবস্থান' : 'Campus', instData['campus'] ?? 'মেইন ক্যাম্পাস, মিরপুর রোড, মোহাম্মদপুর, ঢাকা'),
                _buildInfoRow(AppLanguage.isBangla ? 'প্রধান শিক্ষক / অধ্যক্ষ' : 'Principal / Head', instData['principal'] ?? 'ব্রিগেডিয়ার জেনারেল ড. কাজী শামীম'),
                _buildInfoRow(AppLanguage.isBangla ? 'হেল্পলাইন ফোন' : 'Contact Phone', instData['phone'] ?? '+880 2-9114705'),
                _buildInfoRow(AppLanguage.isBangla ? 'অফিসিয়াল ইমেইল' : 'Official Email', instData['email'] ?? 'info@drmc.edu.bd'),
                _buildInfoRow(AppLanguage.isBangla ? 'ওয়েবসাইট' : 'Website', instData['website'] ?? 'https://drmc.edu.bd'),
                _buildInfoRow(AppLanguage.isBangla ? 'শ্রেণিকক্ষ' : 'Classroom', _profile?.roomNumber ?? 'Room 301, Academic Building 2'),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 4. অভিভাবকের তথ্য (Guardian Info Card)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.family_restroom_rounded,
            title: AppStrings.tr('guardian_info_section'),
            iconColor: const Color(0xFF059669),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow(AppLanguage.isBangla ? 'নিবন্ধিত অভিভাবক' : 'Guardian Name', guardianData['name'] ?? _profile?.guardianName ?? 'মো. রফিকুল ইসলাম চৌধুরী'),
                _buildInfoRow(AppLanguage.isBangla ? 'পিতার নাম' : "Father's Name", guardianData['father_name'] ?? 'মো. রফিকুল ইসলাম চৌধুরী'),
                _buildInfoRow(AppLanguage.isBangla ? 'মাতার নাম' : "Mother's Name", guardianData['mother_name'] ?? 'রোকেয়া বেগম (Rokeya Begum)'),
                _buildInfoRow(AppLanguage.isBangla ? 'সম্পর্ক' : 'Relation', guardianData['relation'] ?? _profile?.guardianRelation ?? 'পিতা (Father)'),
                _buildInfoRow(AppLanguage.isBangla ? 'পেশা' : 'Occupation', guardianData['occupation'] ?? 'সরকারি চাকরিজীবী (Govt. Official)'),
                _buildInfoRow(AppLanguage.isBangla ? 'যোগাযোগ ফোন' : 'Guardian Mobile', guardianData['phone'] ?? _profile?.guardianPhone ?? '+880 1812-987654'),
                _buildInfoRow(AppLanguage.isBangla ? 'ঠিকানা' : 'Address', guardianData['address'] ?? 'মোহাম্মদপুর, ঢাকা - ১২০৭'),
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            AppLanguage.isBangla
                                ? 'জরুরি কল ডায়াল করা হচ্ছে: ${guardianData['phone'] ?? '+880 1812-987654'}'
                                : 'Calling guardian emergency: ${guardianData['phone'] ?? '+880 1812-987654'}',
                          ),
                          backgroundColor: const Color(0xFF059669),
                        ),
                      );
                    },
                    icon: const Icon(Icons.phone_in_talk_rounded, size: 18, color: Color(0xFF059669)),
                    label: Text(
                      AppLanguage.isBangla ? 'অভিভাবককে সরাসরি কল করুন' : 'Call Guardian Emergency',
                      style: const TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.bold),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFF059669)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 5. ফি ও পেমেন্ট (Fees & Payment Card)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.account_balance_wallet_rounded,
            title: AppStrings.tr('fees_payment_section'),
            iconColor: const Color(0xFFD97706),
            trailing: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: _dueFeesAmount > 0 ? const Color(0xFFFEF2F2) : const Color(0xFFECFDF5),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _dueFeesAmount > 0 ? const Color(0xFFFECACA) : const Color(0xFFA7F3D0),
                ),
              ),
              child: Text(
                _dueFeesAmount > 0
                    ? (AppLanguage.isBangla ? 'বকেয়া আছে' : 'Due')
                    : (AppLanguage.isBangla ? 'পরিশোধিত' : 'Paid'),
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: _dueFeesAmount > 0 ? const Color(0xFFDC2626) : const Color(0xFF059669),
                ),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              AppLanguage.isBangla ? 'মোট ফি' : 'Total Fees',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '৳${totalFees.toStringAsFixed(0)}',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFECFDF5),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              AppLanguage.isBangla ? 'পরিশোধিত' : 'Paid',
                              style: const TextStyle(fontSize: 11, color: Color(0xFF059669)),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '৳${paidFees.toStringAsFixed(0)}',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: _dueFeesAmount > 0 ? const Color(0xFFFEF2F2) : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              AppLanguage.isBangla ? 'বকেয়া' : 'Due Balance',
                              style: TextStyle(
                                fontSize: 11,
                                color: _dueFeesAmount > 0 ? const Color(0xFFDC2626) : Colors.grey.shade600,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '৳${_dueFeesAmount.toStringAsFixed(0)}',
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.bold,
                                color: _dueFeesAmount > 0 ? const Color(0xFFDC2626) : const Color(0xFF0F172A),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Pay Fee Action Button
                if (_dueFeesAmount > 0)
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () => _showFeePaymentModal(),
                      icon: const Icon(Icons.payment_rounded, size: 18),
                      label: Text(
                        AppLanguage.isBangla
                            ? 'অনলাইনে ফি পরিশোধ করুন (bKash / Nagad)'
                            : 'Pay Due Fees Online (bKash / Nagad)',
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFD97706),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                    ),
                  ),

                const SizedBox(height: 14),
                Text(
                  AppLanguage.isBangla ? 'সাম্প্রতিক পেমেন্ট ইতিহাস:' : 'Recent Payment History:',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                ),
                const SizedBox(height: 8),

                ..._profileRecentFees.map((fee) {
                  final isPaid = fee['status'] == 'paid';
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: isPaid ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(
                            isPaid ? Icons.check_circle_outline_rounded : Icons.pending_actions_rounded,
                            size: 18,
                            color: isPaid ? const Color(0xFF059669) : const Color(0xFFDC2626),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                fee['title'] ?? (AppLanguage.isBangla ? 'মাসিক বেতন' : 'Tuition Fee'),
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                              Text(
                                '${fee['date']} • ${fee['invoice_no']}',
                                style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              '৳${fee['amount']}',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                            ),
                            Text(
                              fee['method'] ?? 'bKash',
                              style: const TextStyle(fontSize: 10, color: Color(0xFF2563EB), fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 6. নোটিফিকেশন (Notifications & Preferences Card)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.notifications_active_rounded,
            title: AppStrings.tr('notifications_section'),
            iconColor: const Color(0xFF8B5CF6),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Preferences switches
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                const Icon(Icons.phone_android_rounded, size: 18, color: Color(0xFF8B5CF6)),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    AppLanguage.isBangla ? 'অ্যাপ পুশ নোটিফিকেশন' : 'Push Notifications',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Switch(
                            value: _pushNotificationsEnabled,
                            onChanged: (val) {
                              setState(() => _pushNotificationsEnabled = val);
                            },
                          ),
                        ],
                      ),
                      const Divider(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                const Icon(Icons.sms_outlined, size: 18, color: Color(0xFF10B981)),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    AppLanguage.isBangla ? 'জরুরি SMS অ্যালার্ট' : 'SMS Alerts',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Switch(
                            value: _smsAlertsEnabled,
                            onChanged: (val) {
                              setState(() => _smsAlertsEnabled = val);
                            },
                          ),
                        ],
                      ),
                      const Divider(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                const Icon(Icons.mark_email_unread_outlined, size: 18, color: Color(0xFF2563EB)),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    AppLanguage.isBangla ? 'ইমেইল রিপোর্ট ও রেজাল্ট' : 'Email Reports & Results',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Switch(
                            value: _emailReportsEnabled,
                            onChanged: (val) {
                              setState(() => _emailReportsEnabled = val);
                            },
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  AppLanguage.isBangla ? 'সাম্প্রতিক বিজ্ঞপ্তি ও নোটিশ:' : 'Recent Notices:',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                ),
                const SizedBox(height: 8),

                ...notificationsList.take(3).map((notice) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEEF2FF),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  notice['category'] ?? (AppLanguage.isBangla ? 'সাধারণ নোটিশ' : 'Notice'),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF4F46E5)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              notice['published_at'] ?? notice['date'] ?? 'আজ',
                              style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          notice['title'] ?? '',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                        if (notice['content'] != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            notice['content'],
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                          ),
                        ],
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 7. মেসেজ (Teacher Messages Card)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.chat_bubble_outline_rounded,
            title: AppStrings.tr('messages_section'),
            iconColor: const Color(0xFF0284C7),
            trailing: teacherMessagesList.isNotEmpty
                ? Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0284C7),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '${teacherMessagesList.length} ${AppLanguage.isBangla ? 'টি নতুন' : 'New'}',
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  )
                : null,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (teacherMessagesList.isEmpty)
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    child: Center(
                      child: Text(
                        AppLanguage.isBangla ? 'কোনো নতুন শিক্ষকের মেসেজ নেই।' : 'No teacher messages at this time.',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                      ),
                    ),
                  )
                else
                  ...teacherMessagesList.map((msg) {
                    final isRead = msg['read'] == true;
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      decoration: BoxDecoration(
                        color: isRead ? Colors.white : const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: isRead ? const Color(0xFFE2E8F0) : const Color(0xFFBBF7D0)),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        leading: CircleAvatar(
                          backgroundColor: const Color(0xFFDBEAFE),
                          child: const Icon(Icons.person, color: Color(0xFF2563EB), size: 20),
                        ),
                        title: Text(
                          msg['sender_name'] ?? (AppLanguage.isBangla ? 'শ্রেণি শিক্ষক' : 'Teacher'),
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              msg['subject'] ?? (AppLanguage.isBangla ? 'পড়াশোনার অগ্রগতি' : 'Academic update'),
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF2563EB)),
                            ),
                            Text(
                              msg['message'] ?? msg['preview'] ?? '',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                            ),
                          ],
                        ),
                        trailing: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              msg['time'] ?? '10:30 AM',
                              style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
                            ),
                            const SizedBox(height: 4),
                            Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Colors.grey.shade400),
                          ],
                        ),
                        onTap: () => _showTeacherMessageDialog(Map<String, dynamic>.from(msg)),
                      ),
                    );
                  }),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 8. সেটিংস ও ভাষা পরিবর্তন (Settings & Language Switcher)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.settings_outlined,
            title: AppStrings.tr('settings_language_section'),
            iconColor: const Color(0xFF64748B),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Language Switch Row
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.language_rounded, size: 20, color: Color(0xFF6366F1)),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                AppLanguage.isBangla ? 'ভাষা নির্বাচন (Language)' : 'Select Language',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                              Text(
                                AppLanguage.isBangla ? 'বর্তমান ভাষা: বাংলা' : 'Current: English',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        ChoiceChip(
                          label: const Text('বাংলা'),
                          selected: AppLanguage.isBangla,
                          onSelected: (selected) {
                            if (selected) {
                              AppLanguage.changeLanguage('bn');
                              setState(() {});
                            }
                          },
                          selectedColor: const Color(0xFF6366F1),
                          labelStyle: TextStyle(
                            color: AppLanguage.isBangla ? Colors.white : Colors.black87,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(width: 8),
                        ChoiceChip(
                          label: const Text('English'),
                          selected: AppLanguage.isEnglish,
                          onSelected: (selected) {
                            if (selected) {
                              AppLanguage.changeLanguage('en');
                              setState(() {});
                            }
                          },
                          selectedColor: const Color(0xFF6366F1),
                          labelStyle: TextStyle(
                            color: AppLanguage.isEnglish ? Colors.white : Colors.black87,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const Divider(height: 20),

                // Biometric Login Switch
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          const Icon(Icons.fingerprint_rounded, size: 20, color: Color(0xFF059669)),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  AppLanguage.isBangla ? 'বায়োমেট্রিক লগইন (Face ID / Fingerprint)' : 'Biometric Auth Login',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                Text(
                                  AppLanguage.isBangla ? 'দ্রুত ও নিরাপদ লগইন সুবিধা' : 'Fast and secured access',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Switch(
                      value: _biometricEnabled,
                      onChanged: (val) {
                        setState(() => _biometricEnabled = val);
                      },
                    ),
                  ],
                ),
                const Divider(height: 20),

                // Change Password Button
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () => _showChangePasswordDialog(),
                    icon: const Icon(Icons.lock_reset_rounded, size: 18, color: Color(0xFF64748B)),
                    label: Text(
                      AppLanguage.isBangla ? 'পাসওয়ার্ড পরিবর্তন করুন' : 'Change Password',
                      style: const TextStyle(color: Color(0xFF334155), fontWeight: FontWeight.bold),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // --------------------------------------------------------
          // 9. Help & Support (সহায়তা ও সাপোর্ট)
          // --------------------------------------------------------
          _buildProfileSectionContainer(
            icon: Icons.support_agent_rounded,
            title: AppStrings.tr('help_support_section'),
            iconColor: const Color(0xFFEC4899),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // National Helpline 16300 Banner
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFBE185D), Color(0xFFDB2777)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.phone_in_talk_rounded, color: Colors.white, size: 22),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              AppLanguage.isBangla ? 'জাতীয় শিক্ষা হেল্পলাইন' : 'National Education Helpline',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            Text(
                              '${AppLanguage.isBangla ? 'টোল-ফ্রি নম্বর:' : 'Toll Free:'} ${supportData['national_helpline'] ?? '16300'}',
                              style: const TextStyle(color: Color(0xFFFCE7F3), fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                      ElevatedButton(
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                AppLanguage.isBangla
                                    ? 'জাতীয় হেল্পলাইন ১৬৩০০-এ ডায়াল করা হচ্ছে...'
                                    : 'Calling national education helpline 16300...',
                              ),
                              backgroundColor: const Color(0xFFBE185D),
                            ),
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFFBE185D),
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        ),
                        child: Text(
                          AppLanguage.isBangla ? 'কল করুন' : 'Call',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                _buildInfoRow(AppLanguage.isBangla ? 'সাপোর্ট ইমেইল' : 'Support Email', supportData['support_email'] ?? 'support@eshikkha.gov.bd'),
                _buildInfoRow(AppLanguage.isBangla ? 'হেল্পডেস্ক ফোন' : 'Helpdesk Phone', supportData['helpdesk_phone'] ?? '+880 9612-163000'),
                const Divider(height: 18),

                Text(
                  AppLanguage.isBangla ? 'সাধারণ প্রশ্নোত্তর (FAQs):' : 'Frequently Asked Questions (FAQs):',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                ),
                const SizedBox(height: 8),

                ...faqList.map((faq) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: ExpansionTile(
                      shape: const Border(),
                      tilePadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                      childrenPadding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
                      title: Text(
                        faq['q'] ?? '',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF0F172A)),
                      ),
                      children: [
                        Text(
                          faq['a'] ?? '',
                          style: TextStyle(fontSize: 11, color: Colors.grey.shade700, height: 1.4),
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // Switch to Guardian Portal Button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: OutlinedButton.icon(
              onPressed: () {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (_) => const GuardianHomeScreen()),
                );
              },
              icon: const Icon(Icons.switch_account_rounded, color: Color(0xFF2563EB)),
              label: Text(
                AppStrings.tr('guardian_view'),
                style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold),
              ),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFF2563EB)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
          ),
          const SizedBox(height: 14),

          // --------------------------------------------------------
          // 10. Logout (লগআউট)
          // --------------------------------------------------------
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFFEE2E2)),
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: const BoxDecoration(
                  color: Color(0xFFFEF2F2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.logout_rounded, color: Color(0xFFDC2626), size: 22),
              ),
              title: Text(
                AppStrings.tr('logout_section'),
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFFDC2626)),
              ),
              subtitle: Text(
                AppLanguage.isBangla
                    ? 'শিক্ষার্থী অ্যাকাউন্ট থেকে সুরক্ষিতভাবে বের হন'
                    : 'Safely sign out of your student account',
                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
              ),
              trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Color(0xFFDC2626)),
              onTap: () => _showLogoutDialog(),
            ),
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  // ========================================================
  // SUPPORTING PROFILE CARD & DIALOG METHODS
  // ========================================================

  Widget _buildProfileSectionContainer({
    required IconData icon,
    required String title,
    required Color iconColor,
    required Widget child,
    Widget? trailing,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                ),
              ),
              if (trailing != null) trailing,
            ],
          ),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }

  void _showDigitalIdModal(
    Map<String, dynamic> idCardData,
    Map<String, dynamic> studentData,
    Map<String, dynamic> instData,
  ) {
    final guardianData = (_profileHub?['guardian'] as Map<String, dynamic>?) ?? {};
    final studentName = studentData['name'] ?? _profile?.name ?? 'তানভীর আহমেদ (Tanvir Ahmed)';
    final studentFather = studentData['father_name'] ?? guardianData['father_name'] ?? _profile?.fatherName ?? (_isClass1 ? 'মাহমুদুল হাসান (Mahmudul Hasan)' : 'মোঃ রফিকুল ইসলাম (Md. Rafiqul Islam)');
    final studentMother = studentData['mother_name'] ?? guardianData['mother_name'] ?? _profile?.motherName ?? (_isClass1 ? 'আমিনা বেগম (Amina Begum)' : 'আমিনা বেগম (Amina Begum)');
    final studentAdm = studentData['admission_number'] ?? _profile?.admissionNumber ?? 'DRMC-2024-0801';
    final studentRoll = studentData['roll_number'] ?? (_profile != null ? '${_profile!.rollNumber}' : '05');
    final studentClass = studentData['class_name'] ?? _profile?.className ?? 'দশম শ্রেণি';
    final studentSection = studentData['section_name'] ?? _profile?.sectionName ?? 'বিজ্ঞান - ক';
    final cardNo = idCardData['card_no'] ?? 'SID-8801202405';
    final session = idCardData['academic_session'] ?? '2024-2025';
    final validUntil = idCardData['valid_until'] ?? '31 Dec 2025';

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
                children: [
              Container(width: 44, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
              const SizedBox(height: 18),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    AppLanguage.isBangla ? 'ডিজিটাল স্মার্ট স্টুডেন্ট আইডি' : 'Digital Smart Student ID',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              // Smart PVC ID Card Full Visual
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0F172A), Color(0xFF1E3A8A)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF3B82F6).withValues(alpha: 0.3)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.25),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Card Top Bar
                    Row(
                      children: [
                        const Icon(Icons.school, size: 28, color: Color(0xFF38BDF8)),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                instData['name'] ?? 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ',
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                              Text(
                                'EIIN: ${instData['eiin'] ?? '107980'} | Code: ${instData['institution_code'] ?? 'DRMC-01'}',
                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFF10B981)),
                          ),
                          child: const Text('ACTIVE', style: TextStyle(color: Color(0xFF10B981), fontSize: 10, fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ),
                    const Divider(color: Colors.white24, height: 24),

                    Row(
                      children: [
                        Container(
                          width: 80,
                          height: 90,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white30),
                          ),
                          child: const Icon(Icons.person, size: 50, color: Colors.white70),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                studentName,
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                              ),
                              const SizedBox(height: 4),
                              Text('Card No: $cardNo', style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 11, fontWeight: FontWeight.bold)),
                              Text('Admission: $studentAdm', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                              Text('Class: $studentClass ($studentSection)', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                              Text('${AppLanguage.isBangla ? "পিতা" : "Father"}: $studentFather', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                              Text('${AppLanguage.isBangla ? "মাতা" : "Mother"}: $studentMother', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                              Text('Roll: $studentRoll | Blood: ${_profile?.bloodGroup ?? 'B+'}', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('SESSION', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, letterSpacing: 0.8)),
                            Text(session, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                          ],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('VALID UNTIL', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, letterSpacing: 0.8)),
                            Text(validUntil, style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 11, fontWeight: FontWeight.bold)),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.qr_code_2_rounded, size: 36, color: Color(0xFF0F172A)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Barcode
                    Container(
                      width: double.infinity,
                      height: 28,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: List.generate(
                          36,
                          (index) => Container(
                            width: (index % 4 == 0) ? 3 : (index % 2 == 0) ? 2 : 1,
                            color: Colors.black87,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              AppLanguage.isBangla
                                  ? 'ডিজিটাল আইডি কার্ড PDF ডাউনলোড সম্পন্ন হয়েছে।'
                                  : 'Digital ID Card PDF downloaded successfully.',
                            ),
                            backgroundColor: const Color(0xFF059669),
                          ),
                        );
                      },
                      icon: const Icon(Icons.download_rounded),
                      label: Text(AppLanguage.isBangla ? 'PDF ডাউনলোড' : 'Download PDF'),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.pop(ctx),
                      icon: const Icon(Icons.check_rounded),
                      label: Text(AppLanguage.isBangla ? 'ঠিক আছে' : 'Done'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F172A),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  },
    );
  }

  void _showFeePaymentModal() {
    String selectedGateway = 'bkash';
    final phoneController = TextEditingController(text: '01711234567');
    bool isProcessing = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return SafeArea(
              child: Padding(
                padding: EdgeInsets.only(bottom: MediaQuery.of(modalCtx).viewInsets.bottom),
                child: Container(
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
                  ),
                  padding: const EdgeInsets.all(24),
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Center(
                      child: Container(width: 44, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
                    ),
                    const SizedBox(height: 18),
                    Text(
                      AppLanguage.isBangla ? 'অনলাইনে বকেয়া ফি পরিশোধ' : 'Online Tuition Fee Payment',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${AppLanguage.isBangla ? 'পরিশোধযোগ্য বকেয়া পরিমাণ:' : 'Payable Due Amount:'} ৳${_dueFeesAmount.toStringAsFixed(0)}',
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFFDC2626)),
                    ),
                    const SizedBox(height: 16),

                    Text(
                      AppLanguage.isBangla ? 'পেমেন্ট গেটওয়ে নির্বাচন করুন:' : 'Select Payment Method:',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                    ),
                    const SizedBox(height: 8),

                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => selectedGateway = 'bkash'),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: selectedGateway == 'bkash' ? const Color(0xFFFDF2F8) : Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: selectedGateway == 'bkash' ? const Color(0xFFDB2777) : const Color(0xFFE2E8F0),
                                  width: 2,
                                ),
                              ),
                              child: Column(
                                children: [
                                  const Icon(Icons.account_balance_wallet_rounded, color: Color(0xFFDB2777)),
                                  const SizedBox(height: 4),
                                  const Text('bKash', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFFDB2777))),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => selectedGateway = 'nagad'),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: selectedGateway == 'nagad' ? const Color(0xFFFFF7ED) : Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: selectedGateway == 'nagad' ? const Color(0xFFEA580C) : const Color(0xFFE2E8F0),
                                  width: 2,
                                ),
                              ),
                              child: Column(
                                children: [
                                  const Icon(Icons.credit_card_rounded, color: Color(0xFFEA580C)),
                                  const SizedBox(height: 4),
                                  const Text('Nagad', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFFEA580C))),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => selectedGateway = 'rocket'),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: selectedGateway == 'rocket' ? const Color(0xFFFAF5FF) : Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: selectedGateway == 'rocket' ? const Color(0xFF7E22CE) : const Color(0xFFE2E8F0),
                                  width: 2,
                                ),
                              ),
                              child: Column(
                                children: [
                                  const Icon(Icons.send_to_mobile_rounded, color: Color(0xFF7E22CE)),
                                  const SizedBox(height: 4),
                                  const Text('Rocket', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF7E22CE))),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    TextField(
                      controller: phoneController,
                      keyboardType: TextInputType.phone,
                      decoration: InputDecoration(
                        labelText: AppLanguage.isBangla ? 'মোবাইল অ্যাকাউন্ট নম্বর' : 'Account Mobile Number',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        prefixIcon: const Icon(Icons.phone_iphone_rounded),
                      ),
                    ),
                    const SizedBox(height: 20),

                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: isProcessing
                            ? null
                            : () async {
                                setModalState(() => isProcessing = true);
                                final res = await ApiService.payStudentFee(
                                  feeId: 1,
                                  method: selectedGateway,
                                  accountNumber: phoneController.text,
                                  studentId: widget.studentId,
                                );
                                if (!mounted) return;
                                if (ctx.mounted) {
                                  Navigator.pop(ctx);
                                }

                                if (res['success'] == true) {
                                  setState(() {
                                    _dueFeesAmount = 0.0;
                                    _profileRecentFees.insert(0, {
                                      'invoice_no': res['transaction_id'] ?? 'TRX-BK99120',
                                      'title': AppLanguage.isBangla ? 'মাসিক বেতন ও সেশন ফি' : 'Tuition & Session Fee',
                                      'amount': 3500.0,
                                      'date': 'আজ (Today)',
                                      'method': selectedGateway.toUpperCase(),
                                      'status': 'paid',
                                    });
                                  });
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        AppLanguage.isBangla
                                            ? 'ফি সফলভাবে পরিশোধ হয়েছে! ট্রানজ্যাকশন আইডি: ${res['transaction_id'] ?? 'TRX-BK99120'}'
                                            : 'Payment successful! Transaction ID: ${res['transaction_id'] ?? 'TRX-BK99120'}',
                                      ),
                                      backgroundColor: const Color(0xFF059669),
                                    ),
                                  );
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: isProcessing
                            ? const CircularProgressIndicator(color: Colors.white)
                            : Text(
                                '${AppLanguage.isBangla ? 'নিশ্চিত করুন ও পে করুন' : 'Confirm & Pay'} ৳${_dueFeesAmount.toStringAsFixed(0)}',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                              ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
        );
      },
    );
  }

  void _showTeacherMessageDialog(Map<String, dynamic> msg) {
    ApiService.markMessageAsRead(msg['id'] ?? 1, studentId: widget.studentId);
    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              const CircleAvatar(
                backgroundColor: Color(0xFFDBEAFE),
                child: Icon(Icons.person, color: Color(0xFF2563EB)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      msg['sender_name'] ?? (AppLanguage.isBangla ? 'শ্রেণি শিক্ষক' : 'Class Teacher'),
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      msg['subject'] ?? '',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF2563EB), fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                msg['message'] ?? msg['preview'] ?? '',
                style: const TextStyle(fontSize: 14, height: 1.5, color: Color(0xFF1E293B)),
              ),
              const SizedBox(height: 12),
              Text(
                '${AppLanguage.isBangla ? 'পাঠানোর সময়:' : 'Sent at:'} ${msg['time'] ?? '10:30 AM'}',
                style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(AppLanguage.isBangla ? 'বন্ধ করুন' : 'Close'),
            ),
          ],
        );
      },
    );
  }

  void _showChangePasswordDialog() {
    final oldPassController = TextEditingController();
    final newPassController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Text(
            AppLanguage.isBangla ? 'পাসওয়ার্ড পরিবর্তন' : 'Change Password',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: oldPassController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: AppLanguage.isBangla ? 'বর্তমান পাসওয়ার্ড' : 'Current Password',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: newPassController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: AppLanguage.isBangla ? 'নতুন পাসওয়ার্ড' : 'New Password',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(AppLanguage.isBangla ? 'বাতিল' : 'Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      AppLanguage.isBangla
                          ? 'পাসওয়ার্ড সফলভাবে আপডেট করা হয়েছে।'
                          : 'Password updated successfully.',
                    ),
                    backgroundColor: const Color(0xFF059669),
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0F172A),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: Text(AppLanguage.isBangla ? 'সংরক্ষণ করুন' : 'Save'),
            ),
          ],
        );
      },
    );
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              const Icon(Icons.logout_rounded, color: Color(0xFFDC2626)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  AppStrings.tr('logout_confirm_title'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          content: Text(
            AppStrings.tr('logout_confirm_msg'),
            style: const TextStyle(fontSize: 14, height: 1.4),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(AppStrings.tr('cancel_btn')),
            ),
            ElevatedButton(
              onPressed: () async {
                Navigator.pop(ctx);
                await ApiService.studentLogout();
                if (!mounted) return;
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const StudentLoginScreen()),
                  (route) => false,
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFDC2626),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: Text(AppStrings.tr('confirm_logout_btn')),
            ),
          ],
        );
      },
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(
              label,
              style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF0F172A)),
            ),
          ),
        ],
      ),
    );
  }

}
