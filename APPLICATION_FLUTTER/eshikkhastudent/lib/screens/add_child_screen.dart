import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../services/api_service.dart';

class AddChildScreen extends StatefulWidget {
  const AddChildScreen({super.key});

  @override
  State<AddChildScreen> createState() => _AddChildScreenState();
}

class _AddChildScreenState extends State<AddChildScreen> {
  int _currentStep = 1;
  final _studentIdController = TextEditingController(text: 'RCS-ADM-2024-008');
  final _otpController = TextEditingController(text: '123456');

  bool _isLoading = false;
  Map<String, dynamic>? _previewData;
  String _selectedRelationship = 'father';

  // Step 2: Lookup Student & Institution Info
  Future<void> _lookupStudent() async {
    final q = _studentIdController.text.trim();
    if (q.isEmpty) return;

    setState(() => _isLoading = true);
    final data = await ApiService.lookupStudent(q);
    setState(() {
      _isLoading = false;
      if (data != null) {
        _previewData = data;
        _currentStep = 2; // Move to preview & relationship step
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Student ID not found. Please verify and try again.')),
        );
      }
    });
  }

  // Step 4: Request Link & Send OTP
  Future<void> _sendOtp() async {
    if (_previewData == null) return;
    setState(() => _isLoading = true);
    await ApiService.requestLink(_previewData!['id'], _selectedRelationship);
    setState(() {
      _isLoading = false;
      _currentStep = 3; // Move to OTP step
    });
  }

  // Step 5: Verify OTP & Add Child to Account
  Future<void> _verifyOtpAndConnect() async {
    final otp = _otpController.text.trim();
    if (otp.isEmpty || _previewData == null) return;

    setState(() => _isLoading = true);
    final success = await ApiService.verifyOtp(_previewData!['id'], otp, _previewData!, _selectedRelationship);
    setState(() => _isLoading = false);

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppStrings.tr('child_added_success')),
          backgroundColor: const Color(0xFF059669),
        ),
      );
      Navigator.pop(context, true); // Return to home and reload
    }
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, lang, _) {
        return Scaffold(
          appBar: AppBar(
            title: Text(
              AppStrings.tr('add_child_title'),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Step Progress Indicator
                _buildProgressIndicator(),
                const SizedBox(height: 24),

                // Step 1: Write Student ID
                if (_currentStep == 1) _buildStep1(),

                // Step 2 & 3: Preview Institution & Select Relationship
                if (_currentStep == 2) _buildStep2(),

                // Step 4 & 5: OTP Verification & Approval
                if (_currentStep == 3) _buildStep3(),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildProgressIndicator() {
    return Row(
      children: [
        _buildStepDot(1, 'ID'),
        Expanded(child: Container(height: 2, color: _currentStep >= 2 ? const Color(0xFF2563EB) : const Color(0xFFE2E8F0))),
        _buildStepDot(2, 'Preview'),
        Expanded(child: Container(height: 2, color: _currentStep >= 3 ? const Color(0xFF2563EB) : const Color(0xFFE2E8F0))),
        _buildStepDot(3, 'OTP'),
      ],
    );
  }

  Widget _buildStepDot(int step, String label) {
    final isActive = _currentStep >= step;
    return Column(
      children: [
        CircleAvatar(
          radius: 14,
          backgroundColor: isActive ? const Color(0xFF2563EB) : const Color(0xFFE2E8F0),
          child: Text(
            '$step',
            style: TextStyle(color: isActive ? Colors.white : const Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold),
          ),
        ),
        const SizedBox(height: 4),
        Text(label, style: TextStyle(fontSize: 10, color: isActive ? const Color(0xFF2563EB) : const Color(0xFF94A3B8), fontWeight: FontWeight.w600)),
      ],
    );
  }

  Widget _buildStep1() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              AppStrings.tr('add_child_step1'),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const SizedBox(height: 6),
            Text(
              AppStrings.tr('add_child_step1_desc'),
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 20),

            TextField(
              controller: _studentIdController,
              decoration: InputDecoration(
                labelText: AppStrings.tr('enter_student_id'),
                prefixIcon: const Icon(Icons.badge_outlined),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                hintText: 'e.g. RCS-ADM-2024-008',
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              '💡 Demo IDs: RCS-ADM-2024-008 (School), DRMC-ADM-2024-001 (College), TMKM-ADM-2024-006 (Madrasah), SUST-ADM-2024-202 (University)',
              style: TextStyle(fontSize: 11, color: Color(0xFF2563EB)),
            ),
            const SizedBox(height: 24),

            ElevatedButton(
              onPressed: _isLoading ? null : _lookupStudent,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2563EB),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _isLoading
                  ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : Text(AppStrings.tr('lookup_button'), style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStep2() {
    final st = _previewData!;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Institution & Student Preview Card (Step 3)
        Card(
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      AppStrings.tr('add_child_step2'),
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Text('Found', style: TextStyle(color: Color(0xFF059669), fontSize: 10, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                _buildPreviewRow(AppStrings.tr('attr_institution'), st['institution_name']),
                _buildPreviewRow('Type', (st['institution_type'] as String).toUpperCase()),
                _buildPreviewRow(AppStrings.tr('attr_name'), st['name']),
                _buildPreviewRow(AppStrings.tr('attr_class_semester'), '${st['class_name']} • ${st['section_name']}'),
                _buildPreviewRow(AppStrings.tr('roll'), '#${st['roll_number']}'),
                _buildPreviewRow('Registered Phone', st['masked_guardian_phone']),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Select Relationship Card (Step 4)
        Card(
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppStrings.tr('add_child_step3'),
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                ),
                const SizedBox(height: 6),
                Text(
                  AppStrings.tr('relationship_prompt'),
                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                ),
                const SizedBox(height: 14),

                DropdownButtonFormField<String>(
                  value: _selectedRelationship,
                  decoration: InputDecoration(
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                  items: [
                    DropdownMenuItem(value: 'father', child: Text(AppStrings.tr('father'))),
                    DropdownMenuItem(value: 'mother', child: Text(AppStrings.tr('mother'))),
                    DropdownMenuItem(value: 'legal_guardian', child: Text(AppStrings.tr('legal_guardian'))),
                  ],
                  onChanged: (val) => setState(() => _selectedRelationship = val!),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => setState(() => _currentStep = 1),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text(AppStrings.tr('back')),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton(
                onPressed: _isLoading ? null : _sendOtp,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2563EB),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _isLoading
                    ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : Text(AppStrings.tr('next'), style: const TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStep3() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              AppStrings.tr('add_child_step4'),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const SizedBox(height: 6),
            Text(
              '${AppStrings.tr('otp_desc')} ${_previewData?['masked_guardian_phone']}',
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 20),

            TextField(
              controller: _otpController,
              keyboardType: TextInputType.number,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 22, letterSpacing: 8, fontWeight: FontWeight.bold),
              decoration: InputDecoration(
                labelText: AppStrings.tr('enter_otp'),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              '💡 Demo OTP code: 123456',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 11, color: Color(0xFF059669), fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 24),

            ElevatedButton(
              onPressed: _isLoading ? null : _verifyOtpAndConnect,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _isLoading
                  ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : Text(AppStrings.tr('verify_and_link'), style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 10),
            TextButton(
              onPressed: () => setState(() => _currentStep = 2),
              child: Text(AppStrings.tr('back')),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPreviewRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
          ),
          Expanded(
            child: Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
          ),
        ],
      ),
    );
  }
}
