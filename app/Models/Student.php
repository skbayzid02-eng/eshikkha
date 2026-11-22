<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'user_id',
        'academic_class_id',
        'group',
        'section_id',
        'admission_number',
        'roll_number',
        'name',
        'gender',
        'dob',
        'blood_group',
        'guardian_name',
        'father_name',
        'mother_name',
        'guardian_phone',
        'guardian_relation',
        'address',
        'present_village',
        'present_post_office',
        'present_upazila',
        'present_district',
        'permanent_village',
        'permanent_post_office',
        'permanent_upazila',
        'permanent_district',
        'permanent_address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function guardianStudents(): HasMany
    {
        return $this->hasMany(GuardianStudent::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_students', 'student_id', 'guardian_user_id')
            ->withPivot(['relationship', 'status', 'verification_otp', 'verified_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StudentMessage::class);
    }

    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class, 'academic_class_id', 'academic_class_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(StudentLeave::class);
    }

    public function examSummaries(): HasMany
    {
        return $this->hasMany(StudentExamSummary::class);
    }
}
