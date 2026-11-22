import 'package:flutter/material.dart';
import 'localization/app_localizations.dart';
import 'screens/student_login_screen.dart';

void main() {
  runApp(const EShikkhaStudentApp());
}

class EShikkhaStudentApp extends StatelessWidget {
  const EShikkhaStudentApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<String>(
      valueListenable: AppLanguage.current,
      builder: (context, lang, _) {
        return MaterialApp(
          title: 'eShikkha Student & Guardian',
          debugShowCheckedModeBanner: false,
          theme: ThemeData(
            useMaterial3: true,
            colorScheme: ColorScheme.fromSeed(
              seedColor: const Color(0xFF5B4DF6),
              primary: const Color(0xFF5B4DF6),
              secondary: const Color(0xFF0284C7),
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
          home: const StudentLoginScreen(),
        );
      },
    );
  }
}
