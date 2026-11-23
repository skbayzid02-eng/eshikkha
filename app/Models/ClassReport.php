<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'teacher_id',
        'academic_class_id',
        'section_id',
        'subject_id',
        'subject_name',
        'period_number',
        'date',
        'class_conducted',
        'cancellation_reason',
        'topics_covered',
        'homework_assigned',
        'present_count',
        'absent_count',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'class_conducted' => 'boolean',
            'period_number' => 'integer',
            'present_count' => 'integer',
            'absent_count' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
