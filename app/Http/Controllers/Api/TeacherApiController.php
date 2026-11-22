<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where(function ($q) use ($validated) {
            $q->where('email', $validated['email'])
                ->orWhere('phone', $validated['email']);
        })->where('role', 'teacher')->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।',
            ], 401);
        }

        $token = $user->createToken('teacher_app')->plainTextToken;
        $teacher = Teacher::where('user_id', $user->id)->first() ?? Teacher::where('institution_id', $user->institution_id)->first();
        $institution = $user->institution;

        return response()->json([
            'success' => true,
            'message' => 'সফলভাবে লগইন হয়েছে',
            'token' => $token,
            'teacher' => [
                'id' => $teacher?->id,
                'name' => $teacher?->name ?? $user->name,
                'employee_id' => $teacher?->employee_id,
                'designation' => $teacher?->designation,
                'department' => $teacher?->department,
                'phone' => $teacher?->phone ?? $user->phone,
                'email' => $teacher?->email ?? $user->email,
            ],
            'institution' => [
                'id' => $institution?->id,
                'name' => $institution?->name,
                'code' => $institution?->code,
                'type' => $institution?->type,
            ],
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first() ?? Teacher::where('institution_id', $user->institution_id)->first();
        $institutionId = $user->institution_id ?? $teacher?->institution_id;

        $classes = AcademicClass::where('institution_id', $institutionId)->with('sections')->get();
        $totalStudents = Student::where('institution_id', $institutionId)->count();

        $today = Carbon::today()->toDateString();
        $todayAttendanceTaken = Attendance::where('institution_id', $institutionId)
            ->whereDate('date', $today)
            ->count();

        $notices = Notice::where(function ($q) use ($institutionId) {
            $q->where('institution_id', $institutionId)->orWhereNull('institution_id');
        })->whereIn('audience', ['all', 'teachers'])->latest()->take(3)->get();

        return response()->json([
            'success' => true,
            'teacher' => $teacher,
            'stats' => [
                'total_classes' => $classes->count(),
                'total_students' => $totalStudents,
                'attendance_today_count' => $todayAttendanceTaken,
            ],
            'classes' => $classes,
            'recent_notices' => $notices,
        ]);
    }

    public function getStudents(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');

        $students = Student::where('institution_id', $institutionId)
            ->when($classId, fn ($q) => $q->where('academic_class_id', $classId))
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->orderBy('roll_number')
            ->get();

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }

    public function submitAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;

        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
        ]);

        foreach ($validated['attendances'] as $item) {
            Attendance::updateOrCreate(
                [
                    'institution_id' => $institutionId,
                    'student_id' => $item['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'status' => $item['status'],
                    'remarks' => 'Submitted via Teacher App at '.Carbon::now()->format('h:i A'),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'শিক্ষার্থীদের উপস্থিতি সফলভাবে সংরক্ষিত হয়েছে!',
        ]);
    }

    public function submitMarks(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_name' => 'required|string',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.obtained_marks' => 'required|numeric',
            'marks.*.total_marks' => 'required|numeric',
        ]);

        foreach ($validated['marks'] as $item) {
            $pct = ($item['obtained_marks'] / $item['total_marks']) * 100;

            [$grade, $point] = match (true) {
                $pct >= 80 => ['A+', 5.00],
                $pct >= 70 => ['A', 4.00],
                $pct >= 60 => ['A-', 3.50],
                $pct >= 50 => ['B', 3.00],
                $pct >= 40 => ['C', 2.00],
                $pct >= 33 => ['D', 1.00],
                default => ['F', 0.00],
            };

            ExamResult::updateOrCreate(
                [
                    'institution_id' => $institutionId,
                    'exam_id' => $validated['exam_id'],
                    'student_id' => $item['student_id'],
                    'subject_name' => $validated['subject_name'],
                ],
                [
                    'total_marks' => $item['total_marks'],
                    'obtained_marks' => $item['obtained_marks'],
                    'grade' => $grade,
                    'grade_point' => $point,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'পরীক্ষার ফলাফল সফলভাবে এন্ট্রি হয়েছে!',
        ]);
    }

    public function getNotices(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $notices = Notice::where(function ($q) use ($institutionId) {
            $q->where('institution_id', $institutionId)->orWhereNull('institution_id');
        })->whereIn('audience', ['all', 'teachers'])->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'notices' => $notices,
        ]);
    }
}
