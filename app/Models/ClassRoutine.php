<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRoutine extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'academic_class_id',
        'class_name',
        'section_id',
        'section_name',
        'teacher_id',
        'teacher_name',
        'day',
        'period_number',
        'subject',
        'start_time',
        'end_time',
        'room',
        'is_break',
    ];

    protected function casts(): array
    {
        return [
            'is_break' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getDisplayTeacherNameAttribute(): string
    {
        return $this->teacher_name ?: ($this->teacher?->name ?? 'অনুপস্থিত / নির্ধারিত নয়');
    }

    public function getDisplayClassNameAttribute(): string
    {
        return $this->class_name ?: ($this->academicClass?->name ?? 'সকল শ্রেণি');
    }

    public function getDisplaySectionNameAttribute(): string
    {
        return $this->section_name ?: ($this->section?->name ?? '');
    }
}
