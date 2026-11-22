<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'exam_id',
        'exam_type_id',
        'student_id',
        'subject_id',
        'subject_name',
        'credits',
        'total_marks',
        'theory_marks',
        'practical_marks',
        'internal_marks',
        'obtained_marks',
        'highest_marks',
        'grade',
        'grade_point',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'total_marks' => 'integer',
            'theory_marks' => 'float',
            'practical_marks' => 'float',
            'internal_marks' => 'float',
            'obtained_marks' => 'float',
            'highest_marks' => 'float',
            'grade_point' => 'float',
        ];
    }

    public function internalItems(): HasMany
    {
        return $this->hasMany(SubjectInternalMark::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
