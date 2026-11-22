<?php

namespace Database\Factories;

use App\Models\Textbook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Textbook>
 */
class TextbookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_level' => 1,
            'class_name' => 'Class 1',
            'class_name_bn' => 'প্রথম শ্রেণি',
            'title' => 'আমার বাংলা বই',
            'english_title' => 'Amar Bangla Boi',
            'subject' => 'Bangla',
            'cover_image' => null,
            'cover_color_start' => '#3B82F6',
            'cover_color_end' => '#1D4ED8',
            'icon' => 'menu_book_rounded',
            'author' => 'জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)',
            'edition' => '২০২৬ শিক্ষাবর্ষ',
            'total_pages' => 112,
            'has_pdf' => true,
            'pdf_url' => '/api/student/textbooks/1/pdf',
            'sort_order' => 1,
            'is_active' => true,
        ];
    }
}
