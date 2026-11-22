<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'title',
        'content',
        'audience',
        'published_at',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_pinned' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
