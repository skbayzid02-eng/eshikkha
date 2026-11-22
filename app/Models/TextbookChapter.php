<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TextbookChapter extends Model
{
    protected $fillable = [
        'textbook_id',
        'chapter_number',
        'title',
        'english_title',
        'page_start',
        'page_end',
        'summary',
        'content',
    ];

    protected $casts = [
        'chapter_number' => 'integer',
        'page_start' => 'integer',
        'page_end' => 'integer',
    ];

    public function textbook()
    {
        return $this->belongsTo(Textbook::class);
    }
}
