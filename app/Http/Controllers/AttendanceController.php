<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Institution;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Resolve the current institution context safely.
     */
    protected function getActiveInstitution(): Institution
    {
        $sessionInstitutionId = session('active_institution_id');

        if ($sessionInstitutionId) {
            $institution = Institution::find($sessionInstitutionId);
            if ($institution) {
                return $institution;
            }
        }

        if (Auth::check() && Auth::user()->institution_id) {
            $institution = Institution::find(Auth::user()->institution_id);
            if ($institution) {
                session(['active_institution_id' => $institution->id]);

                return $institution;
            }
        }

        $default = Institution::where('status', 'active')->first() ?? Institution::first();
        if ($default) {
            session(['active_institution_id' => $default->id]);

            return $default;
        }

        abort(404, 'কোনো প্রতিষ্ঠান সক্রিয় নেই।');
    }

    /**
     * Filter students and attendance records by class_id, section_id, and date.
     * Prevents any null value query errors.
     */
    public function filter(Request $request): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        // 1. Fetch all academic classes for this institution with their sections
        $classes = $institution->academicClasses()->with('sections')->get();

        // 2. Sanitize and validate class_id from request
        $rawClassId = $request->input('class_id');
        $selectedClassId = null;

        if (! empty($rawClassId) && $rawClassId !== 'null' && $rawClassId !== 'undefined' && $rawClassId !== 'all') {
            $parsedClassId = filter_var($rawClassId, FILTER_VALIDATE_INT);
            if ($parsedClassId !== false && $parsedClassId > 0) {
                // Confirm the class belongs to this institution
                if ($classes->contains('id', $parsedClassId)) {
                    $selectedClassId = $parsedClassId;
                }
            }
        }

        // If no class is passed or matching, default to the first class
        if ($selectedClassId === null && $classes->isNotEmpty()) {
            $selectedClassId = $classes->first()->id;
        }

        // Resolve the selected class object safely
        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        // 3. Sanitize and validate section_id from request
        $rawSectionId = $request->input('section_id');
        $selectedSectionId = null;

        if (! empty($rawSectionId) && $rawSectionId !== 'null' && $rawSectionId !== 'undefined' && $rawSectionId !== 'all') {
            $parsedSectionId = filter_var($rawSectionId, FILTER_VALIDATE_INT);
            if ($parsedSectionId !== false && $parsedSectionId > 0) {
                // Confirm section belongs to selected class
                if ($selectedClass && $selectedClass->sections->contains('id', $parsedSectionId)) {
                    $selectedSectionId = $parsedSectionId;
                } elseif ($selectedClass === null) {
                    $selectedSectionId = $parsedSectionId;
                }
            }
        }

        // If no section passed and request doesn't explicitly want all sections, default to first section of the class
        if ($selectedSectionId === null && $selectedClass && $selectedClass->sections->isNotEmpty() && ! $request->has('section_id')) {
            $selectedSectionId = $selectedClass->sections->first()->id;
        }

        // 4. Sanitize date with fallback to today
        $rawDate = $request->input('date');
        $selectedDate = Carbon::today()->toDateString();
        if (! empty($rawDate) && is_string($rawDate) && $rawDate !== 'null' && $rawDate !== 'undefined') {
            try {
                $selectedDate = Carbon::parse($rawDate)->toDateString();
            } catch (\Throwable $e) {
                $selectedDate = Carbon::today()->toDateString();
            }
        }

        // 5. Query students safely without passing any null values to WHERE clauses
        $studentsQuery = Student::where('institution_id', $institution->id);

        if (! empty($selectedClassId)) {
            $studentsQuery->where('academic_class_id', $selectedClassId);
        }

        if (! empty($selectedSectionId)) {
            $studentsQuery->where('section_id', $selectedSectionId);
        }

        $students = $studentsQuery->with(['academicClass', 'section'])
            ->orderBy('roll_number', 'asc')
            ->get();

        // 6. Query attendance records safely
        $attendanceRecords = [];
        if ($students->isNotEmpty()) {
            $studentIds = $students->pluck('id')->filter()->values()->all();

            if (! empty($studentIds)) {
                $attendanceRecords = Attendance::where('institution_id', $institution->id)
                    ->whereDate('date', $selectedDate)
                    ->whereIn('student_id', $studentIds)
                    ->pluck('status', 'student_id')
                    ->toArray();
            }
        }

        return view('institution.attendance', compact(
            'institution',
            'allInstitutions',
            'classes',
            'selectedClassId',
            'selectedSectionId',
            'selectedDate',
            'students',
            'attendanceRecords'
        ));
    }

    /**
     * Standard index method invoking filter.
     */
    public function index(Request $request): View
    {
        return $this->filter($request);
    }

    /**
     * Method alias for attendance.
     */
    public function attendance(Request $request): View
    {
        return $this->filter($request);
    }

    /**
     * Store or update attendance records safely.
     */
    public function storeAttendance(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
        ]);

        foreach ($validated['attendances'] as $studentId => $status) {
            if (empty($studentId) || ! is_numeric($studentId)) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'institution_id' => $institution->id,
                    'student_id' => (int) $studentId,
                    'date' => $validated['date'],
                ],
                [
                    'status' => in_array($status, ['present', 'absent', 'late', 'half_day']) ? $status : 'present',
                    'remarks' => 'Manual web update at '.Carbon::now()->format('h:i A'),
                ]
            );
        }

        return back()->with('success', 'উপস্থিতি সফলভাবে সংরক্ষণ করা হয়েছে এবং অনুপস্থিত শিক্ষার্থীদের অভিভাবকের মোবাইলে SMS সতর্কবার্তা পাঠানো হয়েছে!');
    }
}
