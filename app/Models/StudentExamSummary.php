<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentExamSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'exam_id',
        'student_id',
        'total_marks_possible',
        'total_marks_obtained',
        'percentage',
        'gpa',
        'cgpa',
        'grade',
        'section_merit_rank',
        'class_merit_rank',
        'total_students_section',
        'total_students_class',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'total_marks_possible' => 'float',
            'total_marks_obtained' => 'float',
            'percentage' => 'float',
            'gpa' => 'float',
            'cgpa' => 'float',
            'section_merit_rank' => 'integer',
            'class_merit_rank' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
