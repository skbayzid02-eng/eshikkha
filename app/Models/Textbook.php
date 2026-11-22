<?php

namespace App\Models;

use Database\Factories\TextbookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Textbook extends Model
{
    /** @use HasFactory<TextbookFactory> */
    use HasFactory;

    protected $fillable = [
        'class_level',
        'class_name',
        'class_name_bn',
        'title',
        'english_title',
        'subject',
        'group',
        'cover_image',
        'cover_color_start',
        'cover_color_end',
        'icon',
        'author',
        'edition',
        'total_pages',
        'has_pdf',
        'pdf_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'class_level' => 'integer',
        'total_pages' => 'integer',
        'has_pdf' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function chapters()
    {
        return $this->hasMany(TextbookChapter::class)->orderBy('chapter_number', 'asc');
    }
}
