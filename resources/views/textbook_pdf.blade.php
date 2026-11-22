<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} - {{ $book->class_name_bn }} | NCTB ডিজিটাল ই-বুক</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, {{ $book->cover_color_start ?? '#2563EB' }}, {{ $book->cover_color_end ?? '#1D4ED8' }});
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Hind Siliguri', 'Inter', -apple-system, sans-serif;
            background-color: #F1F5F9;
            color: #0F172A;
            line-height: 1.6;
        }
        .container {
            max-width: 860px;
            margin: 24px auto;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            overflow: hidden;
            border: 1px solid #E2E8F0;
        }
        .header-bar {
            background: var(--primary-gradient);
            color: #FFFFFF;
            padding: 32px 30px;
            text-align: center;
            position: relative;
        }
        .nctb-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .book-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .book-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 500;
        }
        .meta-strip {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 18px;
            flex-wrap: wrap;
        }
        .meta-item {
            background: rgba(0, 0, 0, 0.15);
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }
        .action-bar {
            background: #F8FAFC;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #E2E8F0;
        }
        .btn {
            background: #2563EB;
            color: #FFFFFF;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn:hover {
            background: #1D4ED8;
        }
        .btn-outline {
            background: #FFFFFF;
            color: #475569;
            border: 1px solid #CBD5E1;
        }
        .btn-outline:hover {
            background: #F1F5F9;
        }
        .content-body {
            padding: 36px 32px;
        }
        .toc-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 36px;
        }
        .toc-title {
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toc-list {
            list-style: none;
        }
        .toc-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px dashed #E2E8F0;
            font-size: 14px;
        }
        .toc-item:last-child {
            border-bottom: none;
        }
        .chapter-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            page-break-inside: avoid;
        }
        .chapter-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 12px;
        }
        .chapter-title {
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
        }
        .page-badge {
            background: #EFF6FF;
            color: #2563EB;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .chapter-summary {
            background: #F8FAFC;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 14px;
            border-left: 4px solid #2563EB;
            font-size: 14px;
            color: #334155;
        }
        .chapter-body {
            font-size: 15px;
            color: #475569;
            line-height: 1.8;
            white-space: pre-line;
        }
        .footer {
            text-align: center;
            padding: 24px;
            font-size: 13px;
            color: #64748B;
            border-top: 1px solid #E2E8F0;
            background: #F8FAFC;
        }
        @media print {
            body {
                background: #FFFFFF;
            }
            .container {
                box-shadow: none;
                border: none;
                margin: 0;
                max-width: 100%;
            }
            .action-bar {
                display: none;
            }
            .chapter-card {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-bar">
            <span class="nctb-badge">জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)</span>
            <h1 class="book-title">{{ $book->title }}</h1>
            <p class="book-subtitle">{{ $book->english_title }} • {{ $book->class_name_bn }} ({{ $book->class_name }})</p>
            <div class="meta-strip">
                <span class="meta-item">শিক্ষাবর্ষ: {{ $book->edition }}</span>
                <span class="meta-item">মোট পৃষ্ঠা: {{ $book->total_pages }}</span>
                <span class="meta-item">মোট অধ্যায়: {{ $book->chapters->count() }}টি</span>
                <span class="meta-item">বিষয় কোড: {{ strtoupper($book->subject) }}</span>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div>
                <strong>ডিজিটাল সংস্করণ ২০২৬</strong> | অফিশিয়াল পাঠ্যবই
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-outline" onclick="window.print()">🖨️ প্রিন্ট / PDF সংরক্ষণ</button>
                <button class="btn" onclick="alert('বইটি সম্পূর্ণ ডাউনলোড হয়েছে!')">📥 ডাউনলোড</button>
            </div>
        </div>

        <!-- Content -->
        <div class="content-body">
            <!-- Table of Contents -->
            <div class="toc-box">
                <h2 class="toc-title">📖 সূচিপত্র (Table of Contents)</h2>
                <ul class="toc-list">
                    @foreach($book->chapters as $ch)
                        <li class="toc-item">
                            <span><strong>{{ $ch->title }}</strong></span>
                            <span style="color: #64748B;">পৃষ্ঠা {{ $ch->page_start }} - {{ $ch->page_end }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chapters Reading View -->
            @foreach($book->chapters as $ch)
                <div class="chapter-card" id="chapter-{{ $ch->chapter_number }}">
                    <div class="chapter-header">
                        <div>
                            <span style="font-size: 12px; font-weight: 700; color: #2563EB; text-transform: uppercase;">অধ্যায় {{ $ch->chapter_number }}</span>
                            <h3 class="chapter-title">{{ $ch->title }}</h3>
                        </div>
                        <span class="page-badge">পৃষ্ঠা: {{ $ch->page_start }} - {{ $ch->page_end }}</span>
                    </div>

                    @if($ch->summary)
                        <div class="chapter-summary">
                            <strong>সারসংক্ষেপ:</strong> {{ $ch->summary }}
                        </div>
                    @endif

                    <div class="chapter-body">
                        {!! nl2br(e($ch->content)) !!}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© ২০২৬ জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB), বাংলাদেশ। সর্বস্বত্ব সংরক্ষিত।</p>
            <p style="margin-top: 4px; font-size: 11px;">eShikkha Smart Education Platform দ্বারা ডিজিটাল পাঠের জন্য সংরক্ষিত।</p>
        </div>
    </div>
</body>
</html>
