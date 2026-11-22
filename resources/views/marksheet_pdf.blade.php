<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Marksheet - {{ $details['student']['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #eff6ff;
            --secondary: #0d9488;
            --accent: #f59e0b;
            --dark: #0f172a;
            --gray: #64748b;
            --border: #cbd5e1;
            --success: #16a34a;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', 'Hind Siliguri', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            padding: 24px 12px;
            -webkit-font-smoothing: antialiased;
        }
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .btn-print {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }
        .marksheet-card {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 2px solid #e2e8f0;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            font-weight: 800;
            color: rgba(30, 58, 138, 0.03);
            pointer-events: none;
            white-space: nowrap;
            text-transform: uppercase;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 24px;
            position: relative;
        }
        .crest-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
        }
        .inst-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .inst-meta {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 12px;
        }
        .doc-badge {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a, #0284c7);
            color: #ffffff;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .exam-info {
            margin-top: 8px;
            font-size: 15px;
            font-weight: 600;
            color: #334155;
        }
        .student-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .info-col table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-col table td {
            padding: 5px 8px;
            font-size: 13px;
        }
        .info-col table td.lbl {
            color: var(--gray);
            font-weight: 500;
            width: 130px;
        }
        .info-col table td.val {
            font-weight: 600;
            color: var(--dark);
        }
        .rank-box {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .rank-box .lbl {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #1e40af;
            letter-spacing: 0.5px;
        }
        .rank-box .pos {
            font-size: 26px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 4px 0;
        }
        .rank-box .sub {
            font-size: 12px;
            color: #475569;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .marks-table th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 600;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #1e3a8a;
        }
        .marks-table th.left {
            text-align: left;
            padding-left: 12px;
        }
        .marks-table td {
            padding: 9px 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            color: #1e293b;
        }
        .marks-table td.left {
            text-align: left;
            padding-left: 12px;
            font-weight: 600;
        }
        .marks-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .badge-grade {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            background: #dcfce7;
            color: #15803d;
        }
        .badge-fail {
            background: #fee2e2;
            color: #b91c1c;
        }
        .summary-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }
        .summary-card.highlight {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .summary-card .title {
            font-size: 11px;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
        }
        .summary-card .val {
            font-size: 20px;
            font-weight: 800;
            color: var(--dark);
            margin-top: 4px;
        }
        .summary-card.highlight .val {
            color: #15803d;
        }
        .grading-system {
            font-size: 11px;
            color: var(--gray);
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 32px;
            background: #fafafa;
        }
        .grading-system span {
            display: inline-block;
            margin-right: 14px;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            padding-top: 10px;
        }
        .sig-col {
            text-align: center;
        }
        .sig-line {
            width: 80%;
            height: 1px;
            background: #64748b;
            margin: 0 auto 8px auto;
        }
        .sig-title {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }
        .sig-subtitle {
            font-size: 11px;
            color: #64748b;
        }
        .qr-seal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            margin-top: 30px;
            font-size: 11px;
            color: #94a3b8;
        }
        .official-seal {
            border: 2px dashed #94a3b8;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #64748b;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .marksheet-card {
                border: none;
                box-shadow: none;
                padding: 10px;
                max-width: 100%;
            }
            @page {
                size: A4 portrait;
                margin: 12mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>eShikkha Smart Marksheet Generator</strong> &bull;
            <span>{{ $details['student']['name'] }} ({{ $details['exam_name'] }})</span>
        </div>
        <button class="btn-print" onclick="window.print()">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print / Save as PDF
        </button>
    </div>

    <div class="marksheet-card">
        <div class="watermark">ESHIKKHA VERIFIED</div>

        <div class="header">
            <div class="crest-icon">🎓</div>
            <h1 class="inst-title">{{ $details['student']['institution_name'] }}</h1>
            <div class="inst-meta">
                EIIN: {{ $details['student']['institution_eiin'] }} &bull; Institution Code: {{ $details['student']['institution_code'] }} &bull; Academic Session: {{ $details['session_year'] }}
            </div>
            <div>
                <span class="doc-badge">Academic Transcript / Marksheet</span>
            </div>
            <div class="exam-info">
                {{ $details['exam_name'] }} ({{ $details['exam_date'] }})
            </div>
        </div>

        <div class="student-grid">
            <div class="info-col">
                <table>
                    <tr>
                        <td class="lbl">Student Name:</td>
                        <td class="val">{{ $details['student']['name'] }}</td>
                        <td class="lbl">Student ID / Roll:</td>
                        <td class="val">{{ $details['student']['roll_number'] }} ({{ $details['student']['admission_number'] }})</td>
                    </tr>
                    <tr>
                        <td class="lbl">Class / Program:</td>
                        <td class="val">{{ $details['student']['class_name'] }}</td>
                        <td class="lbl">Section / Group:</td>
                        <td class="val">{{ $details['student']['section_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Examination:</td>
                        <td class="val">{{ $details['exam_name'] }}</td>
                        <td class="lbl">Status / Result:</td>
                        <td class="val" style="color: #16a34a; font-weight: 700;">{{ strtoupper($details['status']) }}</td>
                    </tr>
                </table>
            </div>
            <div class="rank-box">
                <span class="lbl">Merit Position (মেধাক্রম)</span>
                <span class="pos">#{{ $details['merit_position']['section_rank'] }}</span>
                <span class="sub">Section Rank: {{ $details['merit_position']['section_rank'] }} / {{ $details['merit_position']['total_students_section'] }}</span>
                <span class="sub">Class Rank: {{ $details['merit_position']['class_rank'] }} / {{ $details['merit_position']['total_students_class'] }}</span>
            </div>
        </div>

        <table class="marks-table">
            <thead>
                <tr>
                    <th style="width: 40px;">SL</th>
                    <th class="left">Subject Name (বিষয়)</th>
                    <th style="width: 65px;">Credits</th>
                    <th style="width: 70px;">Full Marks</th>
                    <th style="width: 70px;">Internal</th>
                    <th style="width: 70px;">Theory</th>
                    <th style="width: 70px;">Practical</th>
                    <th style="width: 75px;">Obtained</th>
                    <th style="width: 65px;">Highest</th>
                    <th style="width: 65px;">Grade</th>
                    <th style="width: 65px;">GP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details['subject_marks'] as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="left">{{ $item['subject'] }}</td>
                    <td>{{ $item['credits'] }}</td>
                    <td>{{ $item['total_marks'] }}</td>
                    <td>{{ $item['internal_marks'] }}</td>
                    <td>{{ $item['theory_marks'] }}</td>
                    <td>{{ $item['practical_marks'] ?: '-' }}</td>
                    <td style="font-weight: 700;">{{ $item['obtained_marks'] }}</td>
                    <td style="color: #64748b;">{{ $item['highest_marks'] }}</td>
                    <td>
                        <span class="badge-grade {{ $item['grade'] === 'F' ? 'badge-fail' : '' }}">
                            {{ $item['grade'] }}
                        </span>
                    </td>
                    <td style="font-weight: 600;">{{ number_format($item['grade_point'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-bar">
            <div class="summary-card">
                <div class="title">Total Marks (মোট নম্বর)</div>
                <div class="val">{{ $details['total_marks_obtained'] }} / {{ $details['total_marks_possible'] }}</div>
            </div>
            <div class="summary-card">
                <div class="title">Percentage (শতাংশ)</div>
                <div class="val">{{ $details['percentage'] }}%</div>
            </div>
            <div class="summary-card highlight">
                <div class="title">GPA / CGPA</div>
                <div class="val">{{ number_format($details['gpa'], 2) }} / {{ number_format($details['cgpa'], 2) }}</div>
            </div>
            <div class="summary-card highlight">
                <div class="title">Letter Grade (গ্রেড)</div>
                <div class="val">{{ $details['grade'] }}</div>
            </div>
        </div>

        <div class="grading-system">
            <strong>Grading Scale:</strong>
            <span>80-100% : A+ (5.00)</span>
            <span>70-79% : A (4.00)</span>
            <span>60-69% : A- (3.50)</span>
            <span>50-59% : B (3.00)</span>
            <span>40-49% : C (2.00)</span>
            <span>33-39% : D (1.00)</span>
            <span>0-32% : F (0.00)</span>
        </div>

        <div style="background: #f8fafc; border-left: 4px solid #2563eb; padding: 10px 14px; border-radius: 4px; font-size: 13px; margin-bottom: 24px;">
            <strong>Evaluation Remarks:</strong> {{ $details['remarks'] }}
        </div>

        <div class="signatures">
            <div class="sig-col">
                <div class="sig-line"></div>
                <div class="sig-title">Class Teacher</div>
                <div class="sig-subtitle">Prepared By</div>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <div class="sig-title">Controller of Examinations</div>
                <div class="sig-subtitle">Verified & Verified By</div>
            </div>
            <div class="sig-col">
                <div class="sig-line"></div>
                <div class="sig-title">Principal / Head of Institution</div>
                <div class="sig-subtitle">eShikkha Smart Education</div>
            </div>
        </div>

        <div class="qr-seal">
            <div>
                System Generated Electronic Marksheet &bull; Date: {{ now()->format('d M, Y h:i A') }} &bull; Doc ID: ESK-{{ strtoupper(substr(md5($details['student']['id'] . $details['exam_id']), 0, 10)) }}
            </div>
            <div class="official-seal">
                OFFICIAL SEAL VERIFIED
            </div>
        </div>
    </div>

</body>
</html>
