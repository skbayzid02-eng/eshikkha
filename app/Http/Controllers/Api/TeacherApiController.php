<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\ClassReport;
use App\Models\ExamResult;
use App\Models\GateAttendance;
use App\Models\Homework;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\NotificationService;
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

    /**
     * Submit School Gate Entry & Exit Attendance.
     * Automatically dispatches push notification to guardians.
     */
    public function submitGateAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;

        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,late,absent,left_early',
            'attendances.*.entry_time' => 'nullable|string',
            'attendances.*.exit_time' => 'nullable|string',
            'attendances.*.gate_number' => 'nullable|string',
        ]);

        foreach ($validated['attendances'] as $item) {
            $student = Student::find($item['student_id']);
            if (! $student || $student->institution_id !== $institutionId) {
                continue;
            }

            $gateRecord = GateAttendance::where('institution_id', $institutionId)
                ->where('student_id', $student->id)
                ->whereDate('date', $validated['date'])
                ->first();

            if ($gateRecord) {
                $gateRecord->update([
                    'status' => $item['status'],
                    'entry_time' => $item['entry_time'] ?? Carbon::now()->format('H:i:s'),
                    'exit_time' => $item['exit_time'] ?? null,
                    'gate_number' => $item['gate_number'] ?? 'Gate 1',
                    'remarks' => 'Scanned by '.$user->name,
                ]);
            } else {
                GateAttendance::create([
                    'institution_id' => $institutionId,
                    'student_id' => $student->id,
                    'date' => Carbon::parse($validated['date'])->format('Y-m-d'),
                    'status' => $item['status'],
                    'entry_time' => $item['entry_time'] ?? Carbon::now()->format('H:i:s'),
                    'exit_time' => $item['exit_time'] ?? null,
                    'gate_number' => $item['gate_number'] ?? 'Gate 1',
                    'remarks' => 'Scanned by '.$user->name,
                ]);
            }

            // Notify student and guardian
            $timeStr = $item['entry_time'] ?? Carbon::now()->format('h:i A');
            $statusBangla = $item['status'] === 'late' ? 'দেরিতে স্কুলে উপস্থিত হয়েছে' : 'স্কুলে প্রবেশ করেছে';
            NotificationService::notifyStudent(
                $student,
                'স্কুল গেট উপস্থিতি আপডেট',
                "আপনার সন্তান {$student->name} আজ {$timeStr}-এ {$statusBangla}।",
                'gate_entry',
                ['date' => $validated['date'], 'status' => $item['status']]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'স্কুল গেট উপস্থিতি সফলভাবে সংরক্ষিত ও অভিভাবকদের নোটিফাই করা হয়েছে!',
        ]);
    }

    /**
     * Submit Class/Period Attendance.
     */
    public function submitClassAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;
        $teacher = Teacher::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'academic_class_id' => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'subject_name' => 'nullable|string',
            'period_number' => 'nullable|integer',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
        ]);

        foreach ($validated['attendances'] as $item) {
            $student = Student::find($item['student_id']);
            if (! $student || $student->institution_id !== $institutionId) {
                continue;
            }

            $classRecord = ClassAttendance::where('institution_id', $institutionId)
                ->where('student_id', $student->id)
                ->where('academic_class_id', $validated['academic_class_id'])
                ->whereDate('date', $validated['date'])
                ->where('period_number', $validated['period_number'] ?? 1)
                ->first();

            if ($classRecord) {
                $classRecord->update([
                    'section_id' => $validated['section_id'] ?? null,
                    'subject_id' => $validated['subject_id'] ?? null,
                    'subject_name' => $validated['subject_name'] ?? 'সাধারণ ক্লাস',
                    'teacher_id' => $teacher?->id,
                    'status' => $item['status'],
                    'remarks' => 'Class attendance submitted by '.$user->name,
                ]);
            } else {
                ClassAttendance::create([
                    'institution_id' => $institutionId,
                    'student_id' => $student->id,
                    'academic_class_id' => $validated['academic_class_id'],
                    'section_id' => $validated['section_id'] ?? null,
                    'subject_id' => $validated['subject_id'] ?? null,
                    'subject_name' => $validated['subject_name'] ?? 'সাধারণ ক্লাস',
                    'date' => Carbon::parse($validated['date'])->format('Y-m-d'),
                    'period_number' => $validated['period_number'] ?? 1,
                    'teacher_id' => $teacher?->id,
                    'status' => $item['status'],
                    'remarks' => 'Class attendance submitted by '.$user->name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'ক্লাসভিত্তিক উপস্থিতি সফলভাবে সংরক্ষিত হয়েছে!',
        ]);
    }

    /**
     * Submit daily class report (topics covered, homework, or class cancellation reason).
     */
    public function submitClassReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;
        $teacher = Teacher::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'academic_class_id' => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'subject_name' => 'required|string',
            'period_number' => 'nullable|integer',
            'date' => 'required|date',
            'class_conducted' => 'required|boolean',
            'cancellation_reason' => 'nullable|string|required_if:class_conducted,false',
            'topics_covered' => 'nullable|string',
            'homework_assigned' => 'nullable|string',
            'present_count' => 'nullable|integer',
            'absent_count' => 'nullable|integer',
            'remarks' => 'nullable|string',
        ]);

        $report = ClassReport::create([
            'institution_id' => $institutionId,
            'teacher_id' => $teacher?->id ?? 1,
            'academic_class_id' => $validated['academic_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
            'subject_name' => $validated['subject_name'],
            'period_number' => $validated['period_number'] ?? null,
            'date' => $validated['date'],
            'class_conducted' => $validated['class_conducted'],
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            'topics_covered' => $validated['topics_covered'] ?? null,
            'homework_assigned' => $validated['homework_assigned'] ?? null,
            'present_count' => $validated['present_count'] ?? null,
            'absent_count' => $validated['absent_count'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        // Push notification to students & guardians of this class
        $students = Student::where('institution_id', $institutionId)
            ->where('academic_class_id', $validated['academic_class_id'])
            ->when($validated['section_id'] ?? null, fn ($q, $sec) => $q->where('section_id', $sec))
            ->get();

        if (! $validated['class_conducted']) {
            $notifTitle = "ক্লাস বাতিল বিজ্ঞপ্তি ({$validated['subject_name']})";
            $notifBody = "আজকের {$validated['subject_name']} ক্লাস অনুষ্ঠিত হয়নি। কারণ: ".($validated['cancellation_reason'] ?? 'অনিবার্য কারণ');
            $notifType = 'class_cancelled';
        } else {
            $notifTitle = "দৈনিক ক্লাস রিপোর্ট ({$validated['subject_name']})";
            $notifBody = 'আজকের পাঠ: '.($validated['topics_covered'] ?? 'নির্ধারিত পাঠ সম্পন্ন হয়েছে');
            if (! empty($validated['homework_assigned'])) {
                $notifBody .= ' | হোমওয়ার্ক: '.$validated['homework_assigned'];
            }
            $notifType = 'class_report';
        }

        foreach ($students as $st) {
            NotificationService::notifyStudent($st, $notifTitle, $notifBody, $notifType, [
                'class_report_id' => $report->id,
                'subject' => $validated['subject_name'],
                'date' => $validated['date'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $validated['class_conducted']
                ? 'দৈনিক ক্লাস রিপোর্ট সফলভাবে জমা দেওয়া হয়েছে!'
                : 'ক্লাস বাতিলের কারণ সফলভাবে সংরক্ষিত হয়েছে এবং শিক্ষার্থীদের নোটিফাই করা হয়েছে!',
            'report' => $report,
        ]);
    }

    /**
     * Get class reports submitted by teacher.
     */
    public function getClassReports(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;
        $teacher = Teacher::where('user_id', $user->id)->first();

        $reports = ClassReport::where('institution_id', $institutionId)
            ->when($teacher, fn ($q) => $q->where('teacher_id', $teacher->id))
            ->with(['academicClass', 'section'])
            ->latest('date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }

    /**
     * Submit Homework for class/section.
     */
    public function submitHomework(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;
        $teacher = Teacher::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'academic_class_id' => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'total_marks' => 'nullable|integer',
            'color_theme' => 'nullable|string',
        ]);

        $hw = Homework::create([
            'institution_id' => $institutionId,
            'teacher_id' => $teacher?->id,
            'academic_class_id' => $validated['academic_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'subject' => $validated['subject'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'total_marks' => $validated['total_marks'] ?? 100,
            'status' => 'pending',
            'color_theme' => $validated['color_theme'] ?? 'indigo',
        ]);

        // Push notification to students & guardians
        $students = Student::where('institution_id', $institutionId)
            ->where('academic_class_id', $validated['academic_class_id'])
            ->when($validated['section_id'] ?? null, fn ($q, $sec) => $q->where('section_id', $sec))
            ->get();

        foreach ($students as $st) {
            NotificationService::notifyStudent(
                $st,
                "নতুন হোমওয়ার্ক: {$validated['subject']}",
                "{$validated['title']} (জমা দেওয়ার শেষ তারিখ: {$validated['due_date']})",
                'homework',
                ['homework_id' => $hw->id, 'due_date' => $validated['due_date']]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'হোমওয়ার্ক সফলভাবে দেওয়া হয়েছে এবং শিক্ষার্থীদের নোটিফাই করা হয়েছে!',
            'homework' => $hw,
        ]);
    }

    /**
     * Get Homework list for teacher.
     */
    public function getHomeworkList(Request $request): JsonResponse
    {
        $user = $request->user();
        $institutionId = $user->institution_id;
        $teacher = Teacher::where('user_id', $user->id)->first();

        $homeworks = Homework::where('institution_id', $institutionId)
            ->when($teacher, fn ($q) => $q->where('teacher_id', $teacher->id))
            ->with(['academicClass', 'section'])
            ->latest('due_date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'homeworks' => $homeworks,
        ]);
    }
}
