import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, currentLang, _) {
        return Scaffold(
          appBar: AppBar(
            title: Text(
              AppStrings.tr('settings'),
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
          ),
          body: ListView(
            padding: const EdgeInsets.all(16.0),
            children: [
              // Language Selection Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.language, color: Color(0xFF2563EB)),
                          const SizedBox(width: 10),
                          Text(
                            AppStrings.tr('language'),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        AppStrings.tr('select_language'),
                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                      ),
                      const Divider(height: 24),

                      // English Option
                      RadioListTile<String>(
                        title: const Text('English (International)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        subtitle: const Text('Default app language across all screens', style: TextStyle(fontSize: 11)),
                        value: 'en',
                        groupValue: currentLang,
                        activeColor: const Color(0xFF2563EB),
                        contentPadding: EdgeInsets.zero,
                        onChanged: (val) {
                          if (val != null) AppLanguage.changeLanguage(val);
                        },
                      ),

                      // Bengali Option
                      RadioListTile<String>(
                        title: const Text('বাংলা (Bengali)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        subtitle: const Text('সম্পূর্ণ অ্যাপ্লিকেশন বাংলা ভাষায় পরিবর্তিত হবে', style: TextStyle(fontSize: 11)),
                        value: 'bn',
                        groupValue: currentLang,
                        activeColor: const Color(0xFF2563EB),
                        contentPadding: EdgeInsets.zero,
                        onChanged: (val) {
                          if (val != null) AppLanguage.changeLanguage(val);
                        },
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Guardian Account Info Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.person_outline, color: Color(0xFF0F172A)),
                          SizedBox(width: 10),
                          Text('Guardian Account', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Text('Name: Md. Rafiqul Islam Chowdhury', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      const Text('Phone: 01711000000', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                      const Text('Email: guardian@eshikkha.com', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                      const Divider(height: 24),
                      const Text('Total Linked Children: 4 (School, College, Madrasah, University)', style: TextStyle(fontSize: 12, color: Color(0xFF059669), fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
