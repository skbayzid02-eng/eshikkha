import 'package:flutter/material.dart';

class BookChapterModel {
  final int id;
  final int chapterNumber;
  final String title;
  final String englishTitle;
  final int pageStart;
  final int pageEnd;
  final String summary;
  final String content;

  BookChapterModel({
    required this.id,
    required this.chapterNumber,
    required this.title,
    required this.englishTitle,
    required this.pageStart,
    required this.pageEnd,
    required this.summary,
    required this.content,
  });

  factory BookChapterModel.fromJson(Map<String, dynamic> json) {
    return BookChapterModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 1,
      chapterNumber: json['chapter_number'] is int
          ? json['chapter_number']
          : int.tryParse(json['chapter_number'].toString()) ?? 1,
      title: json['title'] ?? '',
      englishTitle: json['english_title'] ?? '',
      pageStart: json['page_start'] is int
          ? json['page_start']
          : int.tryParse(json['page_start'].toString()) ?? 1,
      pageEnd: json['page_end'] is int
          ? json['page_end']
          : int.tryParse(json['page_end'].toString()) ?? 10,
      summary: json['summary'] ?? '',
      content: json['content'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'chapter_number': chapterNumber,
      'title': title,
      'english_title': englishTitle,
      'page_start': pageStart,
      'page_end': pageEnd,
      'summary': summary,
      'content': content,
    };
  }
}

class TextbookModel {
  final int id;
  final int classLevel;
  final String className;
  final String classNameBn;
  final String title;
  final String englishTitle;
  final String subject;
  final String group;
  final String? coverImage;
  final Color coverColorStart;
  final Color coverColorEnd;
  final IconData icon;
  final String author;
  final String edition;
  final int totalPages;
  final bool hasPdf;
  final String? pdfUrl;
  final int chaptersCount;
  final List<BookChapterModel> chapters;

  TextbookModel({
    required this.id,
    required this.classLevel,
    required this.className,
    required this.classNameBn,
    required this.title,
    required this.englishTitle,
    required this.subject,
    this.group = 'general',
    this.coverImage,
    required this.coverColorStart,
    required this.coverColorEnd,
    required this.icon,
    required this.author,
    required this.edition,
    required this.totalPages,
    required this.hasPdf,
    this.pdfUrl,
    required this.chaptersCount,
    required this.chapters,
  });

  static Color _parseHexColor(dynamic hexStr, Color fallback) {
    if (hexStr == null) return fallback;
    final clean = hexStr.toString().replaceAll('#', '').trim();
    if (clean.length == 6) {
      final val = int.tryParse('FF$clean', radix: 16);
      if (val != null) return Color(val);
    } else if (clean.length == 8) {
      final val = int.tryParse(clean, radix: 16);
      if (val != null) return Color(val);
    }
    return fallback;
  }

  static IconData _parseIcon(dynamic iconStr) {
    switch (iconStr?.toString()) {
      case 'calculate_rounded':
        return Icons.calculate_rounded;
      case 'school_rounded':
        return Icons.school_rounded;
      case 'science_rounded':
        return Icons.science_rounded;
      case 'public_rounded':
        return Icons.public_rounded;
      case 'palette_rounded':
        return Icons.palette_rounded;
      case 'computer_rounded':
        return Icons.computer_rounded;
      case 'bolt_rounded':
        return Icons.bolt_rounded;
      case 'biotech_rounded':
        return Icons.biotech_rounded;
      case 'eco_rounded':
        return Icons.eco_rounded;
      case 'favorite_rounded':
        return Icons.favorite_rounded;
      case 'work_outline_rounded':
        return Icons.work_outline_rounded;
      case 'account_balance_rounded':
        return Icons.account_balance_rounded;
      case 'auto_stories_rounded':
        return Icons.auto_stories_rounded;
      case 'sports_soccer_rounded':
        return Icons.sports_soccer_rounded;
      case 'music_note_rounded':
        return Icons.music_note_rounded;
      case 'translate_rounded':
        return Icons.translate_rounded;
      case 'functions_rounded':
        return Icons.functions_rounded;
      case 'terrain_rounded':
        return Icons.terrain_rounded;
      case 'trending_up_rounded':
        return Icons.trending_up_rounded;
      case 'gavel_rounded':
        return Icons.gavel_rounded;
      case 'receipt_long_rounded':
        return Icons.receipt_long_rounded;
      case 'account_balance_wallet_rounded':
        return Icons.account_balance_wallet_rounded;
      case 'business_center_rounded':
        return Icons.business_center_rounded;
      case 'edit_note_rounded':
        return Icons.edit_note_rounded;
      case 'spellcheck_rounded':
        return Icons.spellcheck_rounded;
      case 'menu_book_rounded':
      default:
        return Icons.menu_book_rounded;
    }
  }

  factory TextbookModel.fromJson(Map<String, dynamic> json) {
    final chaptersList = (json['chapters'] as List?)
            ?.map((c) => BookChapterModel.fromJson(c as Map<String, dynamic>))
            .toList() ??
        [];

    return TextbookModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 1,
      classLevel: json['class_level'] is int
          ? json['class_level']
          : int.tryParse(json['class_level'].toString()) ?? 1,
      className: json['class_name'] ?? 'Class 1',
      classNameBn: json['class_name_bn'] ?? 'প্রথম শ্রেণি',
      title: json['title'] ?? '',
      englishTitle: json['english_title'] ?? '',
      subject: json['subject'] ?? 'General',
      group: json['group']?.toString() ?? 'general',
      coverImage: json['cover_image'] as String?,
      coverColorStart: _parseHexColor(json['cover_color_start'], const Color(0xFF2563EB)),
      coverColorEnd: _parseHexColor(json['cover_color_end'], const Color(0xFF1D4ED8)),
      icon: _parseIcon(json['icon']),
      author: json['author'] ?? 'জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)',
      edition: json['edition'] ?? '২০২৬ শিক্ষাবর্ষ',
      totalPages: json['total_pages'] is int
          ? json['total_pages']
          : int.tryParse(json['total_pages'].toString()) ?? 100,
      hasPdf: json['has_pdf'] == null || json['has_pdf'] == true || json['has_pdf'] == 1 || json['has_pdf'] == 'true',
      pdfUrl: (json['pdf_url'] as String?) ?? 'https://nctb.portal.gov.bd',
      chaptersCount: json['chapters_count'] is int
          ? json['chapters_count']
          : (chaptersList.isNotEmpty ? chaptersList.length : 4),
      chapters: chaptersList,
    );
  }
}
