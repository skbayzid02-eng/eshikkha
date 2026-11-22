import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../services/api_service.dart';
import 'student_login_screen.dart';

class StudentRegisterScreen extends StatefulWidget {
  const StudentRegisterScreen({super.key});

  @override
  State<StudentRegisterScreen> createState() => _StudentRegisterScreenState();
}

class _StudentRegisterScreenState extends State<StudentRegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController(text: 'আরিয়ান হাসান (Aaryan Hasan)');
  final _fatherNameController = TextEditingController(text: 'মাহমুদুল হাসান (Mahmudul Hasan)');
  final _motherNameController = TextEditingController(text: 'আমিনা বেগম (Amina Begum)');
  final _emailController = TextEditingController(text: 'aaryan.class1@eshikkha.edu.bd');
  final _rollController = TextEditingController(text: '01');
  final _passwordController = TextEditingController(text: 'secret123');
  final _confirmPasswordController = TextEditingController(text: 'secret123');

  String _selectedInstitutionType = 'primary_school';
  late final TextEditingController _institutionController;

  String? _selectedClass;
  String? _selectedGroup;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _isLoading = false;
  String? _errorMessage;

  bool _needsGroupSelection(String? cls) {
    if (cls == null) return false;
    final lower = cls.toLowerCase();
    return lower.contains('class 9') ||
        lower.contains('class 10') ||
        lower.contains('class 11') ||
        lower.contains('class 12') ||
        lower.contains('hsc') ||
        lower.contains('ssc') ||
        cls.contains('নবম') ||
        cls.contains('দশম') ||
        cls.contains('একাদশ') ||
        cls.contains('দ্বাদশ');
  }

  List<Map<String, dynamic>> _classList = ApiService.classDegreeOptions.map((c) => Map<String, dynamic>.from(c)).toList();
  bool _isLoadingClasses = false;

  @override
  void initState() {
    super.initState();
    _institutionController = TextEditingController(
      text: ApiService.userInstitutionTypes.firstWhere(
        (t) => t['id'] == _selectedInstitutionType,
        orElse: () => ApiService.userInstitutionTypes.first,
      )['default_inst'],
    );
    _loadDynamicClasses();
  }

  Future<void> _loadDynamicClasses() async {
    setState(() => _isLoadingClasses = true);
    final dynamicClasses = await ApiService.getAvailableClasses();
    if (mounted && dynamicClasses.isNotEmpty) {
      setState(() {
        _classList = dynamicClasses;
        _isLoadingClasses = false;
      });
    } else if (mounted) {
      setState(() => _isLoadingClasses = false);
    }
  }

  void _showAddCustomClassDialog() {
    final customController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.add_circle_outline, color: Color(0xFF5B4DF6)),
            const SizedBox(width: 8),
            Text(
              AppLanguage.isBangla ? 'নতুন শ্রেণি যুক্ত করুন' : 'Add Custom Class',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              AppLanguage.isBangla
                  ? 'আপনার পছন্দমতো যেকোনো শ্রেণির নাম লিখুন (যেমন: ষষ্ঠ শ্রেণি, নবম শ্রেণি, ইত্যাদি)'
                  : 'Enter custom class name (e.g. Class 6, Class 9):',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: customController,
              autofocus: true,
              decoration: InputDecoration(
                hintText: AppLanguage.isBangla ? 'শ্রেণির নাম...' : 'Class name...',
                filled: true,
                fillColor: const Color(0xFFF8FAFC),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(AppLanguage.isBangla ? 'বাতিল' : 'Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              final newName = customController.text.trim();
              if (newName.isNotEmpty) {
                Navigator.pop(ctx);
                await ApiService.createCustomClass(name: newName);
                if (mounted) {
                  setState(() {
                    if (!_classList.any((c) => c['name'] == newName || c['id'] == newName)) {
                      _classList.insert(0, {
                        'id': newName,
                        'name': newName,
                        'name_bn': newName,
                        'name_en': newName,
                      });
                    }
                    _selectedClass = newName;
                    if (_needsGroupSelection(newName)) {
                      _selectedGroup ??= 'science';
                    }
                  });
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF5B4DF6),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: Text(AppLanguage.isBangla ? 'যুক্ত করুন' : 'Add Class'),
          ),
        ],
      ),
    );
  }

  void _onInstitutionTypeChanged(String? newType) {
    if (newType == null || newType == _selectedInstitutionType) return;
    setState(() {
      _selectedInstitutionType = newType;
      final typeData = ApiService.userInstitutionTypes.firstWhere(
        (t) => t['id'] == newType,
        orElse: () => ApiService.userInstitutionTypes.first,
      );
      _institutionController.text = typeData['default_inst'] ?? 'শিক্ষা প্রতিষ্ঠান';
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _fatherNameController.dispose();
    _motherNameController.dispose();
    _emailController.dispose();
    _rollController.dispose();
    _institutionController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    final name = _nameController.text.trim();
    final fatherName = _fatherNameController.text.trim();
    final motherName = _motherNameController.text.trim();
    final email = _emailController.text.trim();
    final roll = _rollController.text.trim();
    final institutionName = _institutionController.text.trim();
    final password = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;

    if (name.isEmpty || email.isEmpty || roll.isEmpty || password.isEmpty) {
      setState(() {
        _errorMessage = AppLanguage.isBangla
            ? 'দয়া করে সকল প্রয়োজনীয় তথ্য পূরণ করুন।'
            : 'Please fill in all required fields.';
      });
      return;
    }

    if (_selectedClass == null || _selectedClass!.isEmpty) {
      setState(() {
        _errorMessage = AppLanguage.isBangla
            ? 'দয়া করে আপনার শ্রেণি নির্বাচন করুন।'
            : 'Please select your class.';
      });
      return;
    }

    if (_needsGroupSelection(_selectedClass) && (_selectedGroup == null || _selectedGroup!.isEmpty)) {
      setState(() {
        _errorMessage = AppLanguage.isBangla
            ? 'দয়া করে আপনার বিভাগ (বিজ্ঞান / মানবিক / ব্যবসায় শিক্ষা) নির্বাচন করুন।'
            : 'Please select your group (Science / Humanities / Business Studies).';
      });
      return;
    }

    if (!email.contains('@')) {
      setState(() {
        _errorMessage = AppLanguage.isBangla
            ? 'সঠিক ইমেইল ঠিকানা প্রদান করুন।'
            : 'Please enter a valid email address.';
      });
      return;
    }

    if (password.length < 6) {
      setState(() {
        _errorMessage = AppLanguage.isBangla
            ? 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।'
            : 'Password must be at least 6 characters.';
      });
      return;
    }

    if (password != confirmPassword) {
      setState(() {
        _errorMessage = AppLanguage.isBangla
            ? 'পাসওয়ার্ড দুটি মেলেনি। আবার যাচাই করুন।'
            : 'Passwords do not match. Please verify.';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await ApiService.studentRegister(
      name: name,
      fatherName: fatherName.isNotEmpty ? fatherName : null,
      motherName: motherName.isNotEmpty ? motherName : null,
      email: email,
      password: password,
      rollNumber: roll,
      className: _selectedClass!,
      group: _selectedGroup,
      institutionType: _selectedInstitutionType,
      institutionName: institutionName,
    );

    if (!mounted) return;

    if (result['success'] == true) {
      // Navigate to Login Page with email pre-filled
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => StudentLoginScreen(prefilledEmail: email),
        ),
      );
    } else {
      setState(() {
        _isLoading = false;
        _errorMessage = result['message'] ??
            (AppLanguage.isBangla
                ? 'রেজিস্ট্রেশন ব্যর্থ হয়েছে। পুনরায় চেষ্টা করুন।'
                : 'Registration failed. Please try again.');
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, lang, _) {
        return Scaffold(
          backgroundColor: const Color(0xFFF8FAFC),
          appBar: AppBar(
            backgroundColor: Colors.transparent,
            elevation: 0,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF0F172A)),
              onPressed: () {
                if (Navigator.canPop(context)) {
                  Navigator.pop(context);
                } else {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(builder: (_) => const StudentLoginScreen()),
                  );
                }
              },
            ),
            actions: [
              TextButton.icon(
                onPressed: () {
                  AppLanguage.changeLanguage(AppLanguage.isBangla ? 'en' : 'bn');
                },
                icon: const Icon(Icons.language, size: 16, color: Color(0xFF5B4DF6)),
                label: Text(
                  AppLanguage.isBangla ? 'English' : 'বাংলা',
                  style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF5B4DF6)),
                ),
              ),
              const SizedBox(width: 8),
            ],
          ),
          body: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 480),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Logo Badge
                      Center(
                        child: Container(
                          width: 64,
                          height: 64,
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF5B4DF6), Color(0xFF0284C7)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF5B4DF6).withValues(alpha: 0.3),
                                blurRadius: 14,
                                offset: const Offset(0, 6),
                              ),
                            ],
                          ),
                          child: const Icon(Icons.person_add_alt_1_rounded, color: Colors.white, size: 34),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Title & Subtitle
                      Text(
                        AppStrings.tr('student_registration_title'),
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        AppLanguage.isBangla
                            ? 'শ্রেণি নির্বাচন ও তথ্য পূরণ করে রেজিস্ট্রেশন সম্পন্ন করুন'
                            : 'Select your class and enter details to register',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      ),
                      const SizedBox(height: 20),

                      // Error message alert
                      if (_errorMessage != null) ...[
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEE2E2),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFFCA5A5)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.error_outline, color: Color(0xFFDC2626), size: 18),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  _errorMessage!,
                                  style: const TextStyle(color: Color(0xFF991B1B), fontSize: 12),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 14),
                      ],

                      // Form Input Container
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(22),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.03),
                              blurRadius: 12,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // 1. Student Name (শিক্ষার্থীর নাম)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'শিক্ষার্থীর নাম (Student Name)' : 'Student Full Name',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _nameController,
                              decoration: InputDecoration(
                                hintText: AppStrings.tr('student_name_hint'),
                                prefixIcon: const Icon(Icons.person_outline_rounded, size: 20),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 1.1 Father's Name (পিতার নাম)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('1.1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'পিতার নাম (Father\'s Name)' : 'Father\'s Name',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _fatherNameController,
                              decoration: InputDecoration(
                                hintText: AppLanguage.isBangla ? 'পিতার পূর্ণ নাম লিখুন' : 'Enter Father\'s Full Name',
                                prefixIcon: const Icon(Icons.person_outline, size: 20, color: Color(0xFF0284C7)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 1.2 Mother's Name (মাতার নাম)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('1.2', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'মাতার নাম (Mother\'s Name)' : 'Mother\'s Name',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _motherNameController,
                              decoration: InputDecoration(
                                hintText: AppLanguage.isBangla ? 'মাতার পূর্ণ নাম লিখুন' : 'Enter Mother\'s Full Name',
                                prefixIcon: const Icon(Icons.person_outline, size: 20, color: Color(0xFFEC4899)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 2. Email Address (ইমেইল ঠিকানা)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('2', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'ইমেইল ঠিকানা (Email Address)' : 'Email Address',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _emailController,
                              keyboardType: TextInputType.emailAddress,
                              decoration: InputDecoration(
                                hintText: AppStrings.tr('email_hint'),
                                prefixIcon: const Icon(Icons.email_outlined, size: 20),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 3. Institution Type (প্রতিষ্ঠানের ধরন নির্বাচন - ১৩টি ধরন)
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                      decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                      child: const Text('3', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      AppLanguage.isBangla ? 'প্রতিষ্ঠানের ধরন নির্বাচন (Institution Type)' : 'Institution Type',
                                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFECFDF5),
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: const Color(0xFFA7F3D0)),
                                  ),
                                  child: const Text('১৩টি ধরন', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF065F46))),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            DropdownButtonFormField<String>(
                              value: _selectedInstitutionType,
                              isExpanded: true,
                              decoration: InputDecoration(
                                prefixIcon: const Icon(Icons.domain_rounded, size: 20, color: Color(0xFF5B4DF6)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                              items: ApiService.userInstitutionTypes.map((t) {
                                return DropdownMenuItem<String>(
                                  value: t['id'] as String,
                                  child: Text(
                                    AppLanguage.isBangla ? t['name_bn'] : t['name_en'],
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                                  ),
                                );
                              }).toList(),
                              onChanged: _onInstitutionTypeChanged,
                            ),
                            const SizedBox(height: 14),

                            // 4. Institution Name (প্রতিষ্ঠানের নাম)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('4', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'প্রতিষ্ঠানের নাম (Institution Name)' : 'Institution Name',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _institutionController,
                              decoration: InputDecoration(
                                hintText: AppStrings.tr('institution_name_hint'),
                                prefixIcon: const Icon(Icons.account_balance_outlined, size: 20, color: Color(0xFF0284C7)),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 5. Roll Number (রোল নম্বর)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('5', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'রোল নম্বর (Roll Number)' : 'Roll Number',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _rollController,
                              keyboardType: TextInputType.number,
                              decoration: InputDecoration(
                                hintText: AppStrings.tr('roll_number_hint'),
                                prefixIcon: const Icon(Icons.format_list_numbered_rounded, size: 20),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 6. Class / Degree (Class / Degree নির্বাচন - ২০টি অপশন)
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                      decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                      child: const Text('6', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      AppLanguage.isBangla ? 'Class / Degree নির্বাচন' : 'Select Class',
                                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                    ),
                                  ],
                                ),
                                if (_selectedClass != null)
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFEFF6FF),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: const Color(0xFFBFDBFE)),
                                    ),
                                    child: Text(
                                      _selectedClass!,
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF2563EB)),
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 6),

                            // 20 Class / Degree Dropdown Picker
                            DropdownButtonFormField<String>(
                              value: _selectedClass,
                              isExpanded: true,
                              hint: Text(
                                AppLanguage.isBangla ? 'শ্রেণি নির্বাচন করুন' : 'Select Class',
                                style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                              ),
                              decoration: InputDecoration(
                                prefixIcon: const Icon(Icons.class_outlined, size: 20),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                              items: ApiService.classDegreeOptions.map((c) {
                                return DropdownMenuItem<String>(
                                  value: c['id'],
                                  child: Text(
                                    AppLanguage.isBangla ? c['name_bn']! : c['name']!,
                                    style: const TextStyle(fontSize: 13),
                                  ),
                                );
                              }).toList(),
                              onChanged: (val) {
                                setState(() {
                                  _selectedClass = val;
                                  if (_needsGroupSelection(val)) {
                                    _selectedGroup ??= 'science';
                                  } else {
                                    _selectedGroup = null;
                                  }
                                });
                              },
                            ),

                            // Dynamic Department/Group Selector for Class 9, 10, 11 & 12
                            if (_needsGroupSelection(_selectedClass)) ...[
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                    decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                    child: const Text('6.1', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    AppLanguage.isBangla ? 'বিভাগ নির্বাচন (Select Group / Department)' : 'Select Group / Department',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              DropdownButtonFormField<String>(
                                value: _selectedGroup,
                                isExpanded: true,
                                decoration: InputDecoration(
                                  prefixIcon: const Icon(Icons.account_tree_outlined, size: 20, color: Color(0xFF2563EB)),
                                  filled: true,
                                  fillColor: const Color(0xFFF8FAFC),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                ),
                                hint: Text(
                                  AppLanguage.isBangla ? 'বিভাগ নির্বাচন করুন (যেমন: বিজ্ঞান)' : 'Select Group (e.g. Science)',
                                  style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                                ),
                                items: const [
                                  DropdownMenuItem(
                                    value: 'science',
                                    child: Text('বিজ্ঞান বিভাগ (Science)'),
                                  ),
                                  DropdownMenuItem(
                                    value: 'humanities',
                                    child: Text('মানবিক বিভাগ (Humanities)'),
                                  ),
                                  DropdownMenuItem(
                                    value: 'business',
                                    child: Text('ব্যবসায় শিক্ষা বিভাগ (Business Studies)'),
                                  ),
                                ],
                                onChanged: (val) {
                                  setState(() => _selectedGroup = val);
                                },
                              ),
                            ],
                            const SizedBox(height: 14),

                            // 7. Password (পাসওয়ার্ড)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('7', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'পাসওয়ার্ড (Password)' : 'Password',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              decoration: InputDecoration(
                                hintText: AppStrings.tr('password_hint'),
                                prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20),
                                suffixIcon: IconButton(
                                  icon: Icon(_obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
                                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                                ),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 14),

                            // 8. Confirm Password (পাসওয়ার্ড নিশ্চিতকরণ)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('8', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'কনফার্ম পাসওয়ার্ড (Confirm Password)' : 'Confirm Password',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF1E293B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _confirmPasswordController,
                              obscureText: _obscureConfirmPassword,
                              decoration: InputDecoration(
                                hintText: AppStrings.tr('confirm_password_label'),
                                prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20),
                                suffixIcon: IconButton(
                                  icon: Icon(_obscureConfirmPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 20),
                                  onPressed: () => setState(() => _obscureConfirmPassword = !_obscureConfirmPassword),
                                ),
                                filled: true,
                                fillColor: const Color(0xFFF8FAFC),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // 9. Register Button (রেজিস্ট্রেশন বাটন)
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(6)),
                                  child: const Text('9', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  AppLanguage.isBangla ? 'রেজিস্ট্রেশন বাটন (Register Button)' : 'Register Button',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: Color(0xFF64748B)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),

                            SizedBox(
                              width: double.infinity,
                              height: 50,
                              child: ElevatedButton(
                                onPressed: _isLoading ? null : _handleRegister,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF5B4DF6),
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                ),
                                child: _isLoading
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                      )
                                    : Text(
                                        AppStrings.tr('register_btn'),
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                      ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),

                      // Back to Login Link
                      Center(
                        child: TextButton.icon(
                          onPressed: () {
                            Navigator.pushReplacement(
                              context,
                              MaterialPageRoute(builder: (_) => const StudentLoginScreen()),
                            );
                          },
                          icon: const Icon(Icons.login_rounded, size: 18, color: Color(0xFF5B4DF6)),
                          label: Text(
                            AppStrings.tr('already_registered_login'),
                            style: const TextStyle(color: Color(0xFF5B4DF6), fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
