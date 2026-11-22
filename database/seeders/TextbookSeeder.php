<?php

namespace Database\Seeders;

use App\Models\Textbook;
use App\Models\TextbookChapter;
use Illuminate\Database\Seeder;

class TextbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TextbookChapter::query()->delete();
        Textbook::query()->delete();

        $jsonPath = __DIR__.'/textbooks_catalog.json';
        if (! file_exists($jsonPath)) {
            return;
        }

        $catalog = json_decode(file_get_contents($jsonPath), true);

        foreach ($catalog as $levelStr => $classData) {
            $level = (int) $levelStr;
            $className = $classData['class_name'];
            $classNameBn = $classData['class_name_bn'];

            foreach ($classData['books'] as $index => $b) {
                $group = $b['group'] ?? 'general';

                $textbook = Textbook::create([
                    'class_level' => $level,
                    'class_name' => $className,
                    'class_name_bn' => $classNameBn,
                    'title' => $b['title'],
                    'english_title' => $b['english_title'],
                    'subject' => $b['subject'],
                    'group' => $group,
                    'cover_image' => null,
                    'cover_color_start' => $b['color_start'],
                    'cover_color_end' => $b['color_end'],
                    'icon' => $b['icon'],
                    'author' => 'জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)',
                    'edition' => '২০২৬ শিক্ষাবর্ষ',
                    'total_pages' => $b['pages'],
                    'has_pdf' => true,
                    'pdf_url' => url("/api/student/textbooks/{$level}/pdf?subject=".urlencode($b['subject'])),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);

                // Create realistic chapters for each textbook
                $chapters = [
                    ['no' => 1, 'title' => 'অধ্যায় ১: প্রাথমিক পাঠ ও ভূমিকা', 'pages' => [1, 25], 'summary' => 'বিষয়টির মৌলিক ধারণা, প্রয়োজনীয়তা ও ব্যবহারিক শিখন।'],
                    ['no' => 2, 'title' => 'অধ্যায় ২: মূল তত্ত্ব ও পর্যবেক্ষণ', 'pages' => [26, 60], 'summary' => 'বিশদ আলোচনা, বাস্তব উদাহরণ ও অনুশীলনমূলক কাজ।'],
                    ['no' => 3, 'title' => 'অধ্যায় ৩: বিশ্লেষণ ও সমস্যা সমাধান', 'pages' => [61, 95], 'summary' => 'গাণিতিক বা ব্যবহারিক সমস্যার সহজ সমাধান এবং প্রশ্নাবলী।'],
                    ['no' => 4, 'title' => 'অধ্যায় ৪: মূল্যায়ন ও সারাংশ', 'pages' => [96, $b['pages']], 'summary' => 'বহুনির্বাচনি ও সৃজনশীল প্রশ্ন এবং পাঠের সারসংক্ষেপ।'],
                ];

                foreach ($chapters as $ch) {
                    TextbookChapter::create([
                        'textbook_id' => $textbook->id,
                        'chapter_number' => $ch['no'],
                        'title' => $ch['title'],
                        'english_title' => "Chapter {$ch['no']}",
                        'page_start' => $ch['pages'][0],
                        'page_end' => $ch['pages'][1],
                        'summary' => $ch['summary'],
                        'content' => "## {$ch['title']}\n\n**সংক্ষিপ্ত পরিচিতি:**\n{$ch['summary']}\n\n**মূল পাঠ্য বিষয়বস্তু:**\nজাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB) ২০২৬ শিক্ষাক্রম অনুযায়ী এই অধ্যায়ের মাধ্যমে শিক্ষার্থী বাস্তব জীবনে বিষয়টির প্রয়োগ ও দক্ষতা অর্জন করতে পারবে।\n\n- প্রধান শিক্ষণীয় বিষয়:\n  1. ধারণা স্পষ্টীকরণ\n  2. বাস্তব অভিজ্ঞতা ও উদাহরণ\n  3. নিয়মিত অনুশীলন ও মূল্যায়ন\n\n*পৃষ্ঠা নম্বর:* {$ch['pages'][0]} থেকে {$ch['pages'][1]}",
                    ]);
                }
            }
        }
    }
}
