<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectInternalMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_result_id',
        'item_name',
        'marks_obtained',
        'total_marks',
        'attendance_rate',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'float',
            'total_marks' => 'float',
            'attendance_rate' => 'float',
        ];
    }

    public function examResult(): BelongsTo
    {
        return $this->belongsTo(ExamResult::class);
    }
}
