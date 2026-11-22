import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/textbook_model.dart';
import 'live_classroom_screen.dart';

String toBanglaNumber(int n) {
  if (!AppLanguage.isBangla) return n.toString();
  const digits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
  return n.toString().split('').map((ch) {
    final d = int.tryParse(ch);
    return d != null ? digits[d] : ch;
  }).join('');
}

void openPdfReader(BuildContext context, TextbookModel book) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) {
      int currentPage = 1;
      double zoomLevel = 1.0;
      bool isDarkMode = false;

      return StatefulBuilder(
        builder: (context, setModalState) {
          return Container(
            height: MediaQuery.of(context).size.height * 0.92,
            decoration: BoxDecoration(
              color: isDarkMode ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              children: [
                // Reader Top Bar
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    color: isDarkMode ? const Color(0xFF1E293B) : Colors.white,
                    border: Border(bottom: BorderSide(color: isDarkMode ? const Color(0xFF334155) : const Color(0xFFE2E8F0))),
                  ),
                  child: Row(
                    children: [
                      IconButton(
                        icon: Icon(Icons.close_rounded, color: isDarkMode ? Colors.white70 : Colors.black87),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${book.title} (PDF)',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                                color: isDarkMode ? Colors.white : const Color(0xFF0F172A),
                              ),
                            ),
                            Text(
                              '${book.classNameBn} • ${AppStrings.tr('page_indicator')} $currentPage / ${book.totalPages}',
                              style: TextStyle(fontSize: 11, color: isDarkMode ? Colors.white60 : Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: Icon(isDarkMode ? Icons.light_mode_outlined : Icons.dark_mode_outlined, size: 20),
                        color: isDarkMode ? Colors.amber : Colors.grey.shade700,
                        onPressed: () => setModalState(() => isDarkMode = !isDarkMode),
                      ),
                      IconButton(
                        icon: const Icon(Icons.zoom_in_rounded, size: 20),
                        color: isDarkMode ? Colors.white70 : Colors.grey.shade700,
                        onPressed: () {
                          if (zoomLevel < 1.6) {
                            setModalState(() => zoomLevel += 0.2);
                          }
                        },
                      ),
                      IconButton(
                        icon: const Icon(Icons.download_rounded, size: 20, color: Color(0xFF2563EB)),
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('"${book.title} (PDF)" সফলভাবে ডাউনলোড হয়েছে!'),
                              backgroundColor: const Color(0xFF10B981),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),

                // PDF Content Canvas
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(20),
                    child: Center(
                      child: Transform.scale(
                        scale: zoomLevel,
                        alignment: Alignment.topCenter,
                        child: Container(
                          constraints: const BoxConstraints(maxWidth: 580),
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            color: isDarkMode ? const Color(0xFF1E293B) : Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.06),
                                blurRadius: 16,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // NCTB Title Page Header
                              Center(
                                child: Column(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFEFF6FF),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: const Text(
                                        'জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)',
                                        style: TextStyle(
                                          color: Color(0xFF2563EB),
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      book.title,
                                      style: TextStyle(
                                        fontSize: 24,
                                        fontWeight: FontWeight.bold,
                                        color: isDarkMode ? Colors.white : const Color(0xFF0F172A),
                                      ),
                                    ),
                                    Text(
                                      '${book.englishTitle} • ${book.classNameBn}',
                                      style: TextStyle(fontSize: 13, color: isDarkMode ? Colors.white60 : Colors.grey.shade600),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      'শিক্ষাবর্ষ: ${book.edition}',
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF10B981)),
                                    ),
                                  ],
                                ),
                              ),
                              const Divider(height: 32),

                              // Table of Contents Box
                              Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: isDarkMode ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: isDarkMode ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.menu_book_rounded, size: 16, color: Color(0xFF2563EB)),
                                        const SizedBox(width: 8),
                                        Text(
                                          AppStrings.tr('table_of_contents'),
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                            color: isDarkMode ? Colors.white : const Color(0xFF0F172A),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    ...book.chapters.map((ch) {
                                      return Padding(
                                        padding: const EdgeInsets.symmetric(vertical: 4),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Expanded(
                                              child: Text(
                                                ch.title,
                                                style: TextStyle(fontSize: 12, color: isDarkMode ? Colors.white70 : const Color(0xFF334155)),
                                              ),
                                            ),
                                            Text(
                                              'পৃষ্ঠা ${ch.pageStart}-${ch.pageEnd}',
                                              style: TextStyle(fontSize: 11, color: isDarkMode ? Colors.white60 : Colors.grey.shade600),
                                            ),
                                          ],
                                        ),
                                      );
                                    }).toList(),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 24),

                              // Sample First Chapter Content
                              if (book.chapters.isNotEmpty) ...[
                                Text(
                                  book.chapters.first.title,
                                  style: TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: isDarkMode ? Colors.white : const Color(0xFF0F172A),
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: isDarkMode ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                                    borderRadius: BorderRadius.circular(8),
                                    border: const Border(left: BorderSide(color: Color(0xFF2563EB), width: 3)),
                                  ),
                                  child: Text(
                                    book.chapters.first.summary,
                                    style: TextStyle(fontSize: 12, color: isDarkMode ? Colors.white70 : const Color(0xFF475569)),
                                  ),
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  book.chapters.first.content,
                                  style: TextStyle(fontSize: 13, height: 1.7, color: isDarkMode ? Colors.white70 : const Color(0xFF334155)),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),

                // Bottom Page Navigation Bar
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: isDarkMode ? const Color(0xFF1E293B) : Colors.white,
                    border: Border(top: BorderSide(color: isDarkMode ? const Color(0xFF334155) : const Color(0xFFE2E8F0))),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                        child: ElevatedButton.icon(
                          icon: const Icon(Icons.arrow_back_ios_rounded, size: 12),
                          label: const Text('পূর্ববর্তী', style: TextStyle(fontSize: 11)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: isDarkMode ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                            foregroundColor: isDarkMode ? Colors.white : const Color(0xFF334155),
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          ),
                          onPressed: currentPage > 1 ? () => setModalState(() => currentPage--) : null,
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: Text(
                          'পৃষ্ঠা $currentPage / ${book.totalPages}',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                            color: isDarkMode ? Colors.white : const Color(0xFF0F172A),
                          ),
                        ),
                      ),
                      Flexible(
                        child: ElevatedButton.icon(
                          icon: const Icon(Icons.arrow_forward_ios_rounded, size: 12),
                          label: const Text('পরবর্তী', style: TextStyle(fontSize: 11)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF2563EB),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          ),
                          onPressed: currentPage < book.totalPages ? () => setModalState(() => currentPage++) : null,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      );
    },
  );
}

void openChapterReader(BuildContext context, TextbookModel book, {int initialChapter = 1}) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) {
      int activeChapterIndex = book.chapters.isEmpty
          ? 0
          : (initialChapter - 1).clamp(0, book.chapters.length - 1);

      return StatefulBuilder(
        builder: (context, setChapterState) {
          final activeChapter = book.chapters.isNotEmpty
              ? book.chapters[activeChapterIndex]
              : BookChapterModel(
                  id: 1,
                  chapterNumber: 1,
                  title: 'অধ্যায় ১: ভূমিকা',
                  englishTitle: 'Chapter 1: Intro',
                  pageStart: 1,
                  pageEnd: 20,
                  summary: 'অধ্যায় সংক্ষেপ',
                  content: 'পাঠ্য বিষয়বস্তু',
                );

          return Container(
            height: MediaQuery.of(context).size.height * 0.90,
            decoration: const BoxDecoration(
              color: Color(0xFFF8FAFC),
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              children: [
                // Header
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
                  ),
                  child: Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              book.title,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                            ),
                            Text(
                              '${book.classNameBn} • অধ্যায় ${activeChapter.chapterNumber} / ${book.chaptersCount}',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                      ElevatedButton.icon(
                        icon: const Icon(Icons.picture_as_pdf_rounded, size: 14),
                        label: const Text('PDF', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFFEE2E2),
                          foregroundColor: const Color(0xFF991B1B),
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        ),
                        onPressed: () {
                          Navigator.pop(ctx);
                          openPdfReader(context, book);
                        },
                      ),
                    ],
                  ),
                ),

                // Horizontal Chapter Selector Bar
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: List.generate(book.chapters.length, (idx) {
                        final ch = book.chapters[idx];
                        final isSelected = idx == activeChapterIndex;
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            label: Text('অধ্যায় ${ch.chapterNumber}'),
                            selected: isSelected,
                            selectedColor: const Color(0xFF2563EB),
                            backgroundColor: const Color(0xFFF1F5F9),
                            labelStyle: TextStyle(
                              fontSize: 12,
                              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                              color: isSelected ? Colors.white : const Color(0xFF475569),
                            ),
                            onSelected: (val) {
                              if (val) {
                                setChapterState(() => activeChapterIndex = idx);
                              }
                            },
                          ),
                        );
                      }),
                    ),
                  ),
                ),

                // Chapter Reading Body
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Chapter Title Card
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 2)),
                            ],
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
                                      color: const Color(0xFFEFF6FF),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      'অধ্যায় ${activeChapter.chapterNumber}',
                                      style: const TextStyle(color: Color(0xFF2563EB), fontWeight: FontWeight.bold, fontSize: 12),
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF1F5F9),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      'পৃষ্ঠা: ${activeChapter.pageStart} - ${activeChapter.pageEnd}',
                                      style: TextStyle(color: Colors.grey.shade700, fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Text(
                                activeChapter.title,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF0F172A)),
                              ),
                              if (activeChapter.summary.isNotEmpty) ...[
                                const SizedBox(height: 10),
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.circular(10),
                                    border: const Border(left: BorderSide(color: Color(0xFF2563EB), width: 3)),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text('সারসংক্ষেপ:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF2563EB))),
                                      const SizedBox(height: 2),
                                      Text(activeChapter.summary, style: const TextStyle(fontSize: 12, color: Color(0xFF334155))),
                                    ],
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Chapter Content Box
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: const [
                                  Icon(Icons.article_rounded, size: 18, color: Color(0xFF10B981)),
                                  SizedBox(width: 8),
                                  Text('পাঠ্য বিষয়বস্তু ও মূল শিখন', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A))),
                                ],
                              ),
                              const Divider(height: 24),
                              Text(
                                activeChapter.content,
                                style: const TextStyle(fontSize: 14, height: 1.8, color: Color(0xFF334155)),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // Bottom Prev/Next navigation
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      OutlinedButton.icon(
                        icon: const Icon(Icons.arrow_back_rounded, size: 16),
                        label: Text(AppStrings.tr('prev_chapter')),
                        onPressed: activeChapterIndex > 0
                            ? () => setChapterState(() => activeChapterIndex--)
                            : null,
                      ),
                      ElevatedButton.icon(
                        icon: const Icon(Icons.arrow_forward_rounded, size: 16),
                        label: Text(AppStrings.tr('next_chapter')),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2563EB),
                          foregroundColor: Colors.white,
                          elevation: 0,
                        ),
                        onPressed: activeChapterIndex < book.chapters.length - 1
                            ? () => setChapterState(() => activeChapterIndex++)
                            : null,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      );
    },
  );
}

String getTeacherForSubject(String subject) {
  final s = subject.toLowerCase();
  if (s.contains('bangla') || s.contains('বাংলা')) {
    return AppLanguage.isBangla ? 'মিসেস ফারহানা আক্তার' : 'Mrs. Farhana Akter';
  }
  if (s.contains('eng') || s.contains('ইংরেজি')) {
    return AppLanguage.isBangla ? 'ড. নাসরিন জাহান' : 'Dr. Nasrin Jahan';
  }
  if (s.contains('math') || s.contains('গণিত')) {
    return AppLanguage.isBangla ? 'মুহাম্মদ রফিকুল ইসলাম' : 'Md. Rafiqul Islam';
  }
  if (s.contains('phys') || s.contains('পদার্থ')) {
    return AppLanguage.isBangla ? 'ড. আহসান হাবীব' : 'Dr. Ahsan Habib';
  }
  if (s.contains('chem') || s.contains('রসায়ন')) {
    return AppLanguage.isBangla ? 'মোঃ মোখলেসুর রহমান' : 'Md. Mokhlesur Rahman';
  }
  if (s.contains('bio') || s.contains('জীব')) {
    return AppLanguage.isBangla ? 'ড. মাহবুবা হক' : 'Dr. Mahbuba Haque';
  }
  if (s.contains('ict') || s.contains('তথ্য')) {
    return AppLanguage.isBangla ? 'প্রকৌশলী তানভীর আহমেদ' : 'Engr. Tanvir Ahmed';
  }
  if (s.contains('busi') || s.contains('ব্যবসায়') || s.contains('account') || s.contains('হিসাব') || s.contains('finance')) {
    return AppLanguage.isBangla ? 'অধ্যাপক কামাল হোসেন' : 'Prof. Kamal Hossain';
  }
  if (s.contains('civic') || s.contains('পৌরনীতি') || s.contains('hist') || s.contains('ইতিহাস') || s.contains('socio') || s.contains('সমাজ')) {
    return AppLanguage.isBangla ? 'মোছাঃ নাজমুন নাহার' : 'Most. Nazmun Nahar';
  }
  return AppLanguage.isBangla ? 'বিষয়ভিত্তিক সিনিয়র শিক্ষক' : 'Senior Subject Teacher';
}

void openLiveClassModal(BuildContext context, TextbookModel book) {
  final teacher = getTeacherForSubject(book.subject);
  const meetingId = '849 2304 9912';
  const passcode = 'eshikkha2026';

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
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Drag handle
                Center(
                  child: Container(
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Top Header Row
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.video_camera_front_rounded, color: Color(0xFF2563EB), size: 22),
                        ),
                        const SizedBox(width: 10),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              AppStrings.tr('teacher_live_class'),
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                            ),
                            Text(
                              '${book.title} • ${AppLanguage.isBangla ? book.classNameBn : book.className}',
                              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFFECACA)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Icon(Icons.fiber_manual_record, color: Color(0xFFDC2626), size: 10),
                          SizedBox(width: 4),
                          Text(
                            'LIVE NOW',
                            style: TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.bold, fontSize: 11),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Teacher Profile Card
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: [
                      Stack(
                        children: [
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: const Color(0xFFDBEAFE),
                            child: Text(
                              teacher.isNotEmpty ? teacher[0] : 'T',
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1D4ED8)),
                            ),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              width: 12,
                              height: 12,
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981),
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              teacher,
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              AppLanguage.isBangla
                                  ? 'সিনিয়র বিষয়ভিত্তিক শিক্ষক • ই-শিক্ষা ডিজিটাল ক্লাসরুম'
                                  : 'Senior Subject Faculty • E-Shikkha Live Academy',
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),

                // Live Topic & Meeting Details Card
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                    boxShadow: [
                      BoxShadow(color: const Color(0xFF2563EB).withOpacity(0.04), blurRadius: 10, offset: const Offset(0, 4)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.menu_book_rounded, size: 16, color: Color(0xFF2563EB)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              AppLanguage.isBangla
                                  ? 'আজকের পাঠ: অধ্যায় ১ ও লাইভ প্রশ্নোত্তর পর্ব'
                                  : 'Today\'s Topic: Chapter 1 & Interactive Q&A',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E3A8A)),
                            ),
                          ),
                        ],
                      ),
                      const Divider(height: 16),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('${AppStrings.tr('meeting_id')}:', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                          Row(
                            children: [
                              const Text(meetingId, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                              const SizedBox(width: 4),
                              IconButton(
                                icon: const Icon(Icons.copy_rounded, size: 16, color: Color(0xFF2563EB)),
                                padding: EdgeInsets.zero,
                                constraints: const BoxConstraints(),
                                onPressed: () {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(content: Text('${AppStrings.tr('meeting_id')} কপি হয়েছে: $meetingId')),
                                  );
                                },
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('${AppStrings.tr('passcode')}:', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                          Row(
                            children: [
                              const Text(passcode, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                              const SizedBox(width: 4),
                              IconButton(
                                icon: const Icon(Icons.copy_rounded, size: 16, color: Color(0xFF2563EB)),
                                padding: EdgeInsets.zero,
                                constraints: const BoxConstraints(),
                                onPressed: () {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(content: Text('${AppStrings.tr('passcode')} কপি হয়েছে: $passcode')),
                                  );
                                },
                              ),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),

                // Primary Action Button 1: Join Zoom Class Directly
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.videocam_rounded, size: 20),
                    label: Text(
                      AppStrings.tr('join_zoom_class'),
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF2563EB),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: () {
                      Navigator.pop(ctx);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            AppLanguage.isBangla
                                ? '"${book.title}" এর লাইভ জুম ক্লাসে সরাসরি সংযুক্ত করা হচ্ছে... (Meeting ID: $meetingId)'
                                : 'Connecting to Live Zoom Class for "${book.title}" (Meeting ID: $meetingId)...',
                          ),
                          backgroundColor: const Color(0xFF2563EB),
                        ),
                      );
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => LiveClassroomScreen(
                            book: book,
                            teacherName: teacher,
                            meetingId: meetingId,
                            passcode: passcode,
                          ),
                        ),
                      );
                    },
                  ),
                ),
                const SizedBox(height: 10),

                // Primary Action Button 2: Enter Interactive In-App Live Classroom
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: OutlinedButton.icon(
                    icon: const Icon(Icons.laptop_chromebook_rounded, size: 18, color: Color(0xFF10B981)),
                    label: Text(
                      AppLanguage.isBangla
                          ? 'ইন-অ্যাপ ক্লাসরুমে প্রবেশ করুন (Live Class)'
                          : 'Enter In-App Live Classroom',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFF6EE7B7), width: 1.5),
                      backgroundColor: const Color(0xFFECFDF5),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: () {
                      Navigator.pop(ctx);
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => LiveClassroomScreen(
                            book: book,
                            teacherName: teacher,
                            meetingId: meetingId,
                            passcode: passcode,
                          ),
                        ),
                      );
                    },
                  ),
                ),
                const SizedBox(height: 12),
              ],
            ),
          ),
        ),
      );
    },
  );
}

Widget buildTextbookCard(BuildContext context, TextbookModel book) {
  return Container(
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      border: Border.all(color: const Color(0xFFE2E8F0)),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withOpacity(0.03),
          blurRadius: 10,
          offset: const Offset(0, 4),
        ),
      ],
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. বইয়ের প্রচ্ছদ (Cover)
        Container(
          width: double.infinity,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [book.coverColorStart, book.coverColorEnd],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          child: Stack(
            clipBehavior: Clip.antiAlias,
            children: [
              Positioned(
                right: -10,
                bottom: -15,
                child: Opacity(
                  opacity: 0.18,
                  child: Icon(book.icon, size: 90, color: Colors.white),
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.white.withOpacity(0.3)),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: const [
                            Icon(Icons.verified_rounded, size: 12, color: Colors.white),
                            SizedBox(width: 4),
                            Text(
                              'NCTB ২০২৬',
                              style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.25),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          AppLanguage.isBangla ? book.classNameBn : book.className,
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(book.icon, color: Colors.white, size: 20),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              book.title,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            Text(
                              book.englishTitle,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: Colors.white.withOpacity(0.85),
                                fontSize: 11,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 4,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      Text(
                        AppLanguage.isBangla
                            ? '${toBanglaNumber(book.totalPages)} ${AppStrings.tr('page_indicator')}'
                            : '${book.totalPages} ${AppStrings.tr('page_indicator')}',
                        style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 10, fontWeight: FontWeight.w600),
                      ),
                      Text(
                        AppLanguage.isBangla
                            ? '•  ${toBanglaNumber(book.chaptersCount)}${AppStrings.tr('chapters_label')}'
                            : '•  ${book.chaptersCount} ${AppStrings.tr('chapters_label')}',
                        style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 10, fontWeight: FontWeight.w600),
                      ),
                      Text(
                        '•  ${book.edition}',
                        style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 10, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),

        // 2. বইয়ের বিবরণ ও নাম
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          book.title,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          book.author,
                          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                        ),
                      ],
                    ),
                  ),
                  if (book.hasPdf)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF2F2),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFFECACA)),
                      ),
                      child: Row(
                        children: const [
                          Icon(Icons.picture_as_pdf_rounded, size: 12, color: Color(0xFFEF4444)),
                          SizedBox(width: 4),
                          Text('PDF', style: TextStyle(color: Color(0xFFEF4444), fontSize: 10, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                ],
              ),
              // 2.1 শিক্ষকদের অনলাইন / জুম লাইভ ক্লাস স্ট্রিপ (Live Zoom Class System)
              Container(
                margin: const EdgeInsets.only(top: 10, bottom: 12),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFEFF6FF), Color(0xFFF0FDF4)],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  ),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFDC2626),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Icon(Icons.fiber_manual_record, size: 8, color: Colors.white),
                          SizedBox(width: 3),
                          Text(
                            'LIVE',
                            style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${getTeacherForSubject(book.subject)} • Zoom Class',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF1E3A8A)),
                          ),
                          Text(
                            AppLanguage.isBangla ? 'অনলাইন লাইভ ক্লাসে সরাসরি যুক্ত হোন' : 'Join teacher live online class',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontSize: 10, color: Colors.grey.shade700),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 6),
                    ElevatedButton.icon(
                      icon: const Icon(Icons.video_call_rounded, size: 16),
                      label: Text(
                        AppLanguage.isBangla ? 'যুক্ত হোন' : 'Join',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2563EB),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        visualDensity: VisualDensity.compact,
                      ),
                      onPressed: () => openLiveClassModal(context, book),
                    ),
                  ],
                ),
              ),

              // 3. বাটনসমূহ
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.menu_book_rounded, size: 15),
                      label: Text(
                        AppStrings.tr('read_book'),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2563EB),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 8),
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () => openChapterReader(context, book),
                    ),
                  ),
                  const SizedBox(width: 6),
                  if (book.hasPdf) ...[
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.picture_as_pdf_rounded, size: 15, color: Color(0xFFDC2626)),
                        label: Text(
                          AppStrings.tr('read_pdf'),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF991B1B)),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFFEE2E2),
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        onPressed: () => openPdfReader(context, book),
                      ),
                    ),
                    const SizedBox(width: 6),
                  ],
                  Expanded(
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.format_list_bulleted_rounded, size: 15, color: Color(0xFF475569)),
                      label: Text(
                        AppStrings.tr('read_chapters'),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                      ),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 8),
                        side: const BorderSide(color: Color(0xFFCBD5E1)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      onPressed: () => openChapterReader(context, book),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    ),
  );
}

class StudentTextbooksSection extends StatelessWidget {
  final List<TextbookModel> textbooks;
  final String? className;
  final String? group;

  const StudentTextbooksSection({
    super.key,
    required this.textbooks,
    this.className,
    this.group,
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

  String _getTextbooksSubtitle() {
    final formattedName = _formatClassName(className);
    final cName = AppLanguage.isBangla
        ? formattedName
        : formattedName;
    String grpText = '';
    if (group == 'science') {
      grpText = AppLanguage.isBangla ? ' (বিজ্ঞান বিভাগ)' : ' (Science Group)';
    } else if (group == 'humanities') {
      grpText = AppLanguage.isBangla ? ' (মানবিক বিভাগ)' : ' (Humanities Group)';
    } else if (group == 'business') {
      grpText = AppLanguage.isBangla ? ' (ব্যবসায় শিক্ষা বিভাগ)' : ' (Business Studies Group)';
    }
    return AppLanguage.isBangla
        ? '$cName$grpText এর নির্ধারিত পাঠ্যবইসমূহ'
        : 'Prescribed textbooks for $cName$grpText';
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Header Row
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.menu_book_rounded, color: Color(0xFF2563EB), size: 20),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      AppStrings.tr('my_textbooks'),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                  ),
                ],
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
              child: Text(
                AppLanguage.isBangla
                    ? '${toBanglaNumber(textbooks.length)}টি বই'
                    : '${textbooks.length} Books',
                style: const TextStyle(color: Color(0xFF065F46), fontWeight: FontWeight.bold, fontSize: 12),
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        Text(
          _getTextbooksSubtitle(),
          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
        ),
        const SizedBox(height: 16),

        // List of Book Cards
        if (textbooks.isEmpty)
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Center(
              child: Text(
                AppLanguage.isBangla ? 'কোনো পাঠ্যবই পাওয়া যায়নি।' : 'No textbooks found.',
                style: const TextStyle(color: Colors.grey),
              ),
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: textbooks.length,
            separatorBuilder: (_, __) => const SizedBox(height: 16),
            itemBuilder: (context, index) {
              final book = textbooks[index];
              return buildTextbookCard(context, book);
            },
          ),
      ],
    );
  }
}
