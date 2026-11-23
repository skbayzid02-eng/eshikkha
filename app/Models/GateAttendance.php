<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'student_id',
        'academic_year_id',
        'date',
        'entry_time',
        'exit_time',
        'status',
        'gate_number',
        'device_id',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'string',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
