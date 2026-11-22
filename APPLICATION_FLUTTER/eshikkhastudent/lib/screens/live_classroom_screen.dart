import 'package:flutter/material.dart';
import '../localization/app_localizations.dart';
import '../models/textbook_model.dart';

class LiveClassroomScreen extends StatefulWidget {
  final TextbookModel book;
  final String teacherName;
  final String meetingId;
  final String passcode;

  const LiveClassroomScreen({
    super.key,
    required this.book,
    required this.teacherName,
    this.meetingId = '849 2304 9912',
    this.passcode = 'eshikkha2026',
  });

  @override
  State<LiveClassroomScreen> createState() => _LiveClassroomScreenState();
}

class _LiveClassroomScreenState extends State<LiveClassroomScreen> {
  bool _isMicMuted = false;
  bool _isCameraOff = false;
  bool _isHandRaised = false;
  final TextEditingController _chatController = TextEditingController();
  final List<Map<String, String>> _messages = [
    {
      'sender': 'ড. আহসান হাবীব (শিক্ষক)',
      'text': 'সকলকে স্বাগতম! আজকের ক্লাসে আমরা অধ্যায়ের গুরুত্বপূর্ণ প্রশ্ন ও সমাধান আলোচনা করব।',
      'isTeacher': 'true',
    },
    {
      'sender': 'তানভীর আহমেদ',
      'text': 'স্যার, স্লাইডের দ্বিতীয় সমস্যাটি স্পষ্ট দেখা যাচ্ছে।',
      'isTeacher': 'false',
    },
    {
      'sender': 'সাদিয়া ইসলাম',
      'text': 'আসসালামু আলাইকুম স্যার!',
      'isTeacher': 'false',
    },
  ];

  @override
  void dispose() {
    _chatController.dispose();
    super.dispose();
  }

  void _sendMessage() {
    final text = _chatController.text.trim();
    if (text.isEmpty) return;
    setState(() {
      _messages.add({
        'sender': AppLanguage.isBangla ? 'আমি (শিক্ষার্থী)' : 'Me (Student)',
        'text': text,
        'isTeacher': 'false',
      });
      _chatController.clear();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1E293B),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${widget.book.title} • ${AppStrings.tr('live_class')}',
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white),
            ),
            Text(
              '${widget.teacherName} • Zoom ID: ${widget.meetingId}',
              style: const TextStyle(fontSize: 11, color: Colors.white70),
            ),
          ],
        ),
        actions: [
          Container(
            margin: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFDC2626),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: const [
                Icon(Icons.fiber_manual_record, size: 10, color: Colors.white),
                SizedBox(width: 4),
                Text(
                  'LIVE',
                  style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Video / Whiteboard Canvas Area
            Expanded(
              flex: 5,
              child: Container(
                margin: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: const Color(0xFF334155)),
                ),
                child: Stack(
                  children: [
                    // Simulated Teacher Presentation Canvas
                    Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 80,
                            height: 80,
                            decoration: BoxDecoration(
                              color: const Color(0xFF2563EB).withOpacity(0.2),
                              shape: BoxShape.circle,
                              border: Border.all(color: const Color(0xFF3B82F6), width: 2),
                            ),
                            child: const Icon(Icons.person_rounded, size: 48, color: Color(0xFF60A5FA)),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            widget.teacherName,
                            style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981).withOpacity(0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              AppLanguage.isBangla
                                  ? 'অনলাইন স্ক্রিন শেয়ার ও লেকচার চলছে'
                                  : 'Screen Sharing & Live Lecture Active',
                              style: const TextStyle(color: Color(0xFF34D399), fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Top Left Info Pill
                    Positioned(
                      top: 12,
                      left: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.black54,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.people_alt_rounded, size: 14, color: Colors.white70),
                            const SizedBox(width: 5),
                            Text(
                              AppLanguage.isBangla ? '৪২ জন যুক্ত' : '42 connected',
                              style: const TextStyle(color: Colors.white, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Top Right Switch to Zoom App Pill
                    Positioned(
                      top: 12,
                      right: 12,
                      child: InkWell(
                        onTap: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                AppLanguage.isBangla
                                    ? 'Zoom অ্যাপে মিটিং লিঙ্ক চালু করা হয়েছে (ID: ${widget.meetingId})'
                                    : 'Launching Zoom Meeting (ID: ${widget.meetingId})',
                              ),
                              backgroundColor: const Color(0xFF2563EB),
                            ),
                          );
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: const Color(0xFF2563EB),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: const [
                              Icon(Icons.open_in_new_rounded, size: 13, color: Colors.white),
                              SizedBox(width: 4),
                              Text(
                                'Open Zoom',
                                style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),

                    // Bottom Floating In-call Controls
                    Positioned(
                      bottom: 12,
                      left: 0,
                      right: 0,
                      child: Center(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.7),
                            borderRadius: BorderRadius.circular(30),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: Icon(_isMicMuted ? Icons.mic_off_rounded : Icons.mic_rounded),
                                color: _isMicMuted ? Colors.redAccent : Colors.white,
                                onPressed: () => setState(() => _isMicMuted = !_isMicMuted),
                                tooltip: 'Microphone',
                              ),
                              IconButton(
                                icon: Icon(_isCameraOff ? Icons.videocam_off_rounded : Icons.videocam_rounded),
                                color: _isCameraOff ? Colors.redAccent : Colors.white,
                                onPressed: () => setState(() => _isCameraOff = !_isCameraOff),
                                tooltip: 'Camera',
                              ),
                              IconButton(
                                icon: Icon(_isHandRaised ? Icons.pan_tool_rounded : Icons.pan_tool_outlined),
                                color: _isHandRaised ? Colors.amber : Colors.white,
                                onPressed: () {
                                  setState(() => _isHandRaised = !_isHandRaised);
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        _isHandRaised
                                            ? (AppLanguage.isBangla ? 'হাত তোলা হয়েছে ✋ শিক্ষককে জানানো হয়েছে!' : 'Hand raised ✋')
                                            : (AppLanguage.isBangla ? 'হাত নামানো হয়েছে' : 'Hand lowered'),
                                      ),
                                      duration: const Duration(seconds: 1),
                                    ),
                                  );
                                },
                                tooltip: 'Raise Hand',
                              ),
                              const SizedBox(width: 6),
                              ElevatedButton.icon(
                                icon: const Icon(Icons.call_end_rounded, size: 16),
                                label: Text(AppLanguage.isBangla ? 'লিভ' : 'Leave'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFFEF4444),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                ),
                                onPressed: () => Navigator.pop(context),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Live Class Q&A & Student Chat Panel
            Expanded(
              flex: 4,
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
                  border: Border.all(color: const Color(0xFF334155)),
                ),
                child: Column(
                  children: [
                    // Chat Header
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.chat_bubble_outline_rounded, size: 16, color: Color(0xFF60A5FA)),
                            const SizedBox(width: 6),
                            Text(
                              AppLanguage.isBangla ? 'লাইভ প্রশ্নোত্তর ও চ্যাট' : 'Live Q&A & Chat',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                            ),
                          ],
                        ),
                        Text(
                          'Passcode: ${widget.passcode}',
                          style: const TextStyle(color: Colors.white60, fontSize: 11),
                        ),
                      ],
                    ),
                    const Divider(color: Color(0xFF334155), height: 16),

                    // Chat Messages List
                    Expanded(
                      child: ListView.builder(
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final msg = _messages[index];
                          final isTeacher = msg['isTeacher'] == 'true';
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                CircleAvatar(
                                  radius: 12,
                                  backgroundColor: isTeacher ? const Color(0xFF10B981) : const Color(0xFF2563EB),
                                  child: Text(
                                    msg['sender']![0],
                                    style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: isTeacher ? const Color(0xFF064E3B) : const Color(0xFF334155),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          msg['sender']!,
                                          style: TextStyle(
                                            color: isTeacher ? const Color(0xFF6EE7B7) : const Color(0xFF93C5FD),
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          msg['text']!,
                                          style: const TextStyle(color: Colors.white, fontSize: 12),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),

                    // Message Input Field
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _chatController,
                            style: const TextStyle(color: Colors.white, fontSize: 13),
                            decoration: InputDecoration(
                              hintText: AppLanguage.isBangla ? 'শিক্ষককে প্রশ্ন করুন...' : 'Ask teacher a question...',
                              hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
                              isDense: true,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                              filled: true,
                              fillColor: const Color(0xFF0F172A),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(20),
                                borderSide: const BorderSide(color: Color(0xFF334155)),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(20),
                                borderSide: const BorderSide(color: Color(0xFF334155)),
                              ),
                            ),
                            onSubmitted: (_) => _sendMessage(),
                          ),
                        ),
                        const SizedBox(width: 6),
                        IconButton(
                          icon: const Icon(Icons.send_rounded, color: Color(0xFF3B82F6), size: 20),
                          onPressed: _sendMessage,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
