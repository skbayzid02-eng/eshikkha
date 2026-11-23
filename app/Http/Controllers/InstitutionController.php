<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoutine;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\Fee;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentExamSummary;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    /**
     * Resolve the current institution context.
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

        // Fallback to first active institution (e.g. DRMC)
        $default = Institution::where('status', 'active')->first() ?? Institution::first();
        session(['active_institution_id' => $default->id]);

        return $default;
    }

    public function switchTenant(int $id): RedirectResponse
    {
        $institution = Institution::findOrFail($id);
        session(['active_institution_id' => $institution->id]);

        return redirect()->back()->with('success', "সফলভাবে '{$institution->name}' এর প্যানেলে পরিবর্তন করা হয়েছে।");
    }

    public function dashboard(): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $today = Carbon::today()->toDateString();

        $totalStudents = $institution->students()->count();
        $totalTeachers = $institution->teachers()->count();
        $totalClasses = $institution->academicClasses()->count();

        $presentToday = Attendance::where('institution_id', $institution->id)
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $absentToday = Attendance::where('institution_id', $institution->id)
            ->whereDate('date', $today)
            ->where('status', 'absent')
            ->count();

        $attendanceRate = $totalStudents > 0 && ($presentToday + $absentToday) > 0
            ? round(($presentToday / ($presentToday + $absentToday)) * 100, 1)
            : 94.5;

        $totalFeeCollected = Fee::where('institution_id', $institution->id)
            ->where('status', 'paid')
            ->sum('amount');

        $pendingFees = Fee::where('institution_id', $institution->id)
            ->where('status', 'unpaid')
            ->sum('amount');

        $recentStudents = $institution->students()->with(['academicClass', 'section'])->latest()->take(5)->get();
        $recentNotices = Notice::where(function ($q) use ($institution) {
            $q->where('institution_id', $institution->id)
                ->orWhereNull('institution_id');
        })->latest()->take(5)->get();

        return view('institution.dashboard', compact(
            'institution',
            'allInstitutions',
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'presentToday',
            'absentToday',
            'attendanceRate',
            'totalFeeCollected',
            'pendingFees',
            'recentStudents',
            'recentNotices'
        ));
    }

    public function classes(Request $request): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $classes = $institution->academicClasses()
            ->with(['sections', 'students'])
            ->withCount(['students', 'sections'])
            ->orderBy('numeric_level', 'asc')
            ->get();

        return view('institution.classes', compact('institution', 'allInstitutions', 'classes'));
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'numeric_level' => 'nullable|integer|between:-2,16',
            'group' => 'nullable|string|in:general,science,humanities,business',
            'medium' => 'required|string|in:bangla,english',
            'initial_section_name' => 'nullable|string|max:255',
            'shift' => 'nullable|string|in:morning,day',
            'room_number' => 'nullable|string|max:50',
        ]);

        $level = $validated['numeric_level'] ?? $this->detectNumericLevel($validated['name']);

        $class = AcademicClass::create([
            'institution_id' => $institution->id,
            'name' => trim($validated['name']),
            'numeric_level' => $level,
            'group' => $validated['group'] ?? (($level >= 9 && $level <= 12) ? 'science' : 'general'),
            'medium' => $validated['medium'],
        ]);

        $sectionName = trim($validated['initial_section_name'] ?? 'শাখা - ক (Section A)');
        if ($sectionName !== '') {
            Section::create([
                'institution_id' => $institution->id,
                'academic_class_id' => $class->id,
                'name' => $sectionName,
                'shift' => $validated['shift'] ?? 'morning',
                'room_number' => $validated['room_number'] ?? 'Room '.($level > 0 ? (100 + $level) : 101),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "নতুন শ্রেণি '{$class->name}' সফলভাবে তৈরি করা হয়েছে!",
                'class' => $class,
            ]);
        }

        return back()->with('success', "নতুন শ্রেণি '{$class->name}' সফলভাবে তৈরি করা হয়েছে!");
    }

    public function storeSection(Request $request, int $classId): RedirectResponse
    {
        $institution = $this->getActiveInstitution();
        $class = $institution->academicClasses()->findOrFail($classId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shift' => 'required|string|in:morning,day',
            'room_number' => 'nullable|string|max:50',
        ]);

        Section::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'name' => trim($validated['name']),
            'shift' => $validated['shift'],
            'room_number' => $validated['room_number'] ?? 'Room '.rand(101, 305),
        ]);

        return back()->with('success', "শ্রেণি '{$class->name}'-এ নতুন শাখা '{$validated['name']}' সফলভাবে যুক্ত হয়েছে!");
    }

    public function destroyClass(int $id): RedirectResponse
    {
        $institution = $this->getActiveInstitution();
        $class = $institution->academicClasses()->withCount('students')->findOrFail($id);

        if ($class->students_count > 0) {
            return back()->with('error', "এই শ্রেণিতে {$class->students_count} জন শিক্ষার্থী থাকায় শ্রেণিটি মোছা যাবে না। আগে শিক্ষার্থীদের অন্য শ্রেণিতে স্থানান্তর করুন।");
        }

        $class->sections()->delete();
        $class->delete();

        return back()->with('success', 'শ্রেণিটি সফলভাবে মুছে ফেলা হয়েছে!');
    }

    public function destroySection(int $id): RedirectResponse
    {
        $institution = $this->getActiveInstitution();
        $section = Section::where('institution_id', $institution->id)->withCount('students')->findOrFail($id);

        if ($section->students_count > 0) {
            return back()->with('error', "এই শাখায় {$section->students_count} জন শিক্ষার্থী থাকায় শাখাটি মোছা যাবে না।");
        }

        $section->delete();

        return back()->with('success', 'শাখাটি সফলভাবে মুছে ফেলা হয়েছে!');
    }

    protected function detectNumericLevel(string $className): int
    {
        $lower = strtolower($className);
        if (str_contains($lower, 'play') || str_contains($className, 'প্লে')) {
            return -2;
        }
        if (str_contains($lower, 'nursery') || str_contains($className, 'নার্সারি')) {
            return -1;
        }
        if (str_contains($lower, 'kg') || str_contains($className, 'কেজি')) {
            return 0;
        }
        if (str_contains($lower, 'diploma') || str_contains($className, 'ডিপ্লোমা')) {
            return 13;
        }
        if (str_contains($lower, 'honours') || str_contains($className, 'অনার্স')) {
            return 14;
        }
        if (str_contains($lower, 'degree') || str_contains($className, 'ডিগ্রি')) {
            return 15;
        }
        if (str_contains($lower, 'master') || str_contains($className, 'মাস্টার্স')) {
            return 16;
        }
        if (str_contains($lower, '12') || str_contains($className, 'দ্বাদশ')) {
            return 12;
        }
        if (str_contains($lower, '11') || str_contains($className, 'একাদশ') || str_contains($lower, 'hsc')) {
            return 11;
        }
        if (str_contains($lower, '10') || str_contains($className, 'দশম') || str_contains($lower, 'ssc')) {
            return 10;
        }
        if (str_contains($lower, '9') || str_contains($className, 'নবম')) {
            return 9;
        }
        if (str_contains($lower, '8') || str_contains($className, 'অষ্টম') || str_contains($lower, 'jsc')) {
            return 8;
        }
        if (str_contains($lower, '7') || str_contains($className, 'সপ্তম')) {
            return 7;
        }
        if (str_contains($lower, '6') || str_contains($className, 'ষষ্ঠ')) {
            return 6;
        }
        if (str_contains($lower, '5') || str_contains($className, 'পঞ্চম') || str_contains($lower, 'psc')) {
            return 5;
        }
        if (str_contains($lower, '4') || str_contains($className, 'চতুর্থ')) {
            return 4;
        }
        if (str_contains($lower, '3') || str_contains($className, 'তৃতীয়')) {
            return 3;
        }
        if (str_contains($lower, '2') || str_contains($className, 'দ্বিতীয়')) {
            return 2;
        }
        if (str_contains($lower, '1') || str_contains($className, 'প্রথম') || str_contains($className, '১ম')) {
            return 1;
        }

        preg_match('/\d+/', $className, $matches);
        if (! empty($matches[0])) {
            return (int) $matches[0];
        }

        return 1;
    }

    public function students(Request $request): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $query = $institution->students()->with(['academicClass', 'section']);

        if ($request->filled('class_id') && $request->class_id !== 'all') {
            $query->where('academic_class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('roll_number', 'asc')->paginate(12)->withQueryString();
        $classes = $institution->academicClasses()->with('sections')->get();

        return view('institution.students', compact('institution', 'allInstitutions', 'students', 'classes'));
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_class_id' => 'nullable|exists:academic_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'class_name' => 'required_without:academic_class_id|nullable|string|max:100',
            'section_name' => 'nullable|string|max:100',
            'roll_number' => 'required|integer|min:1',
            'gender' => 'required|in:male,female,other',
            'guardian_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'guardian_phone' => 'required|string|max:20',
            'blood_group' => 'nullable|string|max:10',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'present_village' => 'nullable|string|max:255',
            'present_post_office' => 'nullable|string|max:255',
            'present_upazila' => 'nullable|string|max:255',
            'present_district' => 'nullable|string|max:255',
            'permanent_village' => 'nullable|string|max:255',
            'permanent_post_office' => 'nullable|string|max:255',
            'permanent_upazila' => 'nullable|string|max:255',
            'permanent_district' => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string',
        ]);

        // Resolve AcademicClass
        $classId = $validated['academic_class_id'] ?? null;
        if (! $classId && ! empty($validated['class_name'])) {
            $className = trim($validated['class_name']);
            $academicClass = AcademicClass::where('institution_id', $institution->id)
                ->where(function ($q) use ($className) {
                    $q->where('name', $className)
                        ->orWhere('name', 'like', "%{$className}%");
                })->first();

            if (! $academicClass) {
                $level = $this->detectNumericLevel($className);
                $academicClass = AcademicClass::create([
                    'institution_id' => $institution->id,
                    'name' => $className,
                    'numeric_level' => $level,
                    'group' => ($level >= 9 && $level <= 12) ? 'science' : 'general',
                    'medium' => 'bangla',
                ]);
            }
            $classId = $academicClass->id;
        }

        if (! $classId) {
            $academicClass = $institution->academicClasses()->first();
            if (! $academicClass) {
                $academicClass = AcademicClass::create([
                    'institution_id' => $institution->id,
                    'name' => 'প্রথম শ্রেণি (Class 1)',
                    'numeric_level' => 1,
                    'medium' => 'bangla',
                    'group' => 'general',
                ]);
            }
            $classId = $academicClass->id;
        }

        // Resolve Section
        $sectionId = $validated['section_id'] ?? null;
        $secName = trim($validated['section_name'] ?? '');
        if (! $sectionId && $secName !== '') {
            $section = Section::where('institution_id', $institution->id)
                ->where('academic_class_id', $classId)
                ->where(function ($q) use ($secName) {
                    $q->where('name', $secName)
                        ->orWhere('name', 'like', "%{$secName}%");
                })->first();

            if (! $section) {
                $section = Section::create([
                    'institution_id' => $institution->id,
                    'academic_class_id' => $classId,
                    'name' => $secName,
                    'shift' => 'morning',
                    'room_number' => 'Room 101',
                ]);
            }
            $sectionId = $section->id;
        }

        if (! $sectionId) {
            $section = Section::where('academic_class_id', $classId)->first();
            if (! $section) {
                $section = Section::create([
                    'institution_id' => $institution->id,
                    'academic_class_id' => $classId,
                    'name' => 'শাখা - ক (Section A)',
                    'shift' => 'morning',
                    'room_number' => 'Room 101',
                ]);
            }
            $sectionId = $section->id;
        }

        $admissionNo = $institution->code.'-ADM-'.date('Y').'-'.str_pad($institution->students()->count() + 1, 3, '0', STR_PAD_LEFT);

        $guardianName = $validated['guardian_name']
            ?? $validated['father_name']
            ?? $validated['mother_name']
            ?? $validated['name'];

        $presentParts = array_filter([
            $validated['present_village'] ?? null,
            $validated['present_post_office'] ?? null,
            $validated['present_upazila'] ?? null,
            $validated['present_district'] ?? null,
        ]);
        $presentAddress = ! empty($presentParts) ? implode(', ', $presentParts) : ($validated['address'] ?? null);

        $permanentParts = array_filter([
            $validated['permanent_village'] ?? null,
            $validated['permanent_post_office'] ?? null,
            $validated['permanent_upazila'] ?? null,
            $validated['permanent_district'] ?? null,
        ]);
        $permanentAddress = ! empty($permanentParts) ? implode(', ', $permanentParts) : ($validated['permanent_address'] ?? $presentAddress);

        Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $classId,
            'section_id' => $sectionId,
            'admission_number' => $admissionNo,
            'roll_number' => $validated['roll_number'],
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'blood_group' => $validated['blood_group'] ?? null,
            'guardian_name' => $guardianName,
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_relation' => 'guardian',
            'dob' => $validated['dob'] ?? null,
            'address' => $presentAddress,
            'present_village' => $validated['present_village'] ?? null,
            'present_post_office' => $validated['present_post_office'] ?? null,
            'present_upazila' => $validated['present_upazila'] ?? null,
            'present_district' => $validated['present_district'] ?? null,
            'permanent_village' => $validated['permanent_village'] ?? null,
            'permanent_post_office' => $validated['permanent_post_office'] ?? null,
            'permanent_upazila' => $validated['permanent_upazila'] ?? null,
            'permanent_district' => $validated['permanent_district'] ?? null,
            'permanent_address' => $permanentAddress,
            'status' => 'active',
        ]);

        return back()->with('success', 'নতুন শিক্ষার্থী সফলভাবে ভর্তি করা হয়েছে!');
    }

    public function teachers(Request $request): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $query = $institution->teachers();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(10)->withQueryString();

        return view('institution.teachers', compact('institution', 'allInstitutions', 'teachers'));
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'qualification' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
        ]);

        $empId = 'EMP-'.$institution->code.'-'.str_pad($institution->teachers()->count() + 1, 2, '0', STR_PAD_LEFT);

        Teacher::create(array_merge($validated, [
            'institution_id' => $institution->id,
            'employee_id' => $empId,
            'status' => 'active',
        ]));

        return back()->with('success', 'নতুন শিক্ষক সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function attendance(Request $request): View
    {
        return app(AttendanceController::class)->filter($request);
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        return app(AttendanceController::class)->storeAttendance($request);
    }

    public function fees(): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $fees = Fee::where('institution_id', $institution->id)
            ->with(['student.academicClass'])
            ->latest()
            ->paginate(10);

        $students = $institution->students()->orderBy('roll_number', 'asc')->get();

        $totalCollected = Fee::where('institution_id', $institution->id)->where('status', 'paid')->sum('amount');
        $totalUnpaid = Fee::where('institution_id', $institution->id)->where('status', 'unpaid')->sum('amount');

        return view('institution.fees', compact('institution', 'allInstitutions', 'fees', 'students', 'totalCollected', 'totalUnpaid'));
    }

    public function markFeePaid(Request $request, Fee $fee): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:bKash,Nagad,Rocket,Cash,Bank',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $fee->update([
            'status' => 'paid',
            'paid_amount' => $fee->amount,
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? 'TRX'.strtoupper(Str::random(8)),
            'paid_at' => Carbon::now(),
        ]);

        return back()->with('success', "ইনভয়েস #{$fee->invoice_no} এর ফি সফলভাবে গ্রহণ করা হয়েছে!");
    }

    public function storeFee(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
        ]);

        $invoiceNo = 'INV-'.$institution->code.'-'.date('Y').'-'.rand(1000, 9999);

        Fee::create(array_merge($validated, [
            'institution_id' => $institution->id,
            'invoice_no' => $invoiceNo,
            'status' => 'unpaid',
            'paid_amount' => 0,
        ]));

        return back()->with('success', 'নতুন ফি ইনভয়েস তৈরি করা হয়েছে!');
    }

    public function exams(): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        // Auto-seed default Exam Types if none exist for this institution
        if ($institution->examTypes()->count() === 0) {
            $defaultTypes = [
                ['name' => 'অর্ধবার্ষিক মূল্যায়ন / পরীক্ষা (Half Yearly)', 'code' => 'half_yearly', 'weight_percentage' => 100],
                ['name' => 'বার্ষিক মূল্যায়ন / পরীক্ষা (Annual Exam)', 'code' => 'annual', 'weight_percentage' => 100],
                ['name' => 'ক্লাস টেস্ট (Class Test)', 'code' => 'class_test', 'weight_percentage' => 20],
                ['name' => 'মডেল টেস্ট (Model Test)', 'code' => 'model_test', 'weight_percentage' => 100],
                ['name' => 'টিউটোরিয়াল (Tutorial)', 'code' => 'tutorial', 'weight_percentage' => 10],
            ];
            foreach ($defaultTypes as $dt) {
                $institution->examTypes()->create($dt);
            }
        }

        // Auto-seed default Subjects if none exist for this institution
        if ($institution->subjects()->count() === 0) {
            $defaultSubjects = [
                ['name' => 'বাংলা ১ম পত্র (Bangla 1st)', 'code' => '101', 'full_marks' => 100, 'pass_marks' => 33],
                ['name' => 'ইংরেজি ১ম পত্র (English 1st)', 'code' => '107', 'full_marks' => 100, 'pass_marks' => 33],
                ['name' => 'সাধারণ গণিত (Mathematics)', 'code' => '109', 'full_marks' => 100, 'pass_marks' => 33],
                ['name' => 'সাধারণ বিজ্ঞান (General Science)', 'code' => '127', 'full_marks' => 100, 'pass_marks' => 33],
                ['name' => 'বাংলাদেশ ও বিশ্বপরিচয় (BGS)', 'code' => '150', 'full_marks' => 100, 'pass_marks' => 33],
                ['name' => 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)', 'code' => '154', 'full_marks' => 50, 'pass_marks' => 17],
                ['name' => 'ইসলাম ও নৈতিক শিক্ষা (Religion)', 'code' => '111', 'full_marks' => 100, 'pass_marks' => 33],
            ];
            foreach ($defaultSubjects as $ds) {
                $institution->subjects()->create($ds);
            }
        }

        $exams = $institution->exams()->with(['academicClass', 'examType', 'examResults.student'])->get();
        $classes = $institution->academicClasses()->with(['sections', 'students' => fn ($q) => $q->orderBy('roll_number', 'asc')])->get();
        $examTypes = $institution->examTypes()->orderBy('name')->get();
        $subjects = $institution->subjects()->orderBy('name')->get();

        $classResultsData = $this->buildClassResultsData($institution, $classes, $exams);
        $initialClassList = array_keys($classResultsData);

        return view('institution.exams', compact(
            'institution',
            'allInstitutions',
            'exams',
            'classes',
            'examTypes',
            'subjects',
            'classResultsData',
            'initialClassList'
        ));
    }

    public function storeExam(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'academic_class_id' => 'required|exists:academic_classes,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'session_year' => 'nullable|string|max:20',
        ]);

        Exam::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $validated['academic_class_id'],
            'exam_type_id' => $validated['exam_type_id'] ?? null,
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'session_year' => $validated['session_year'] ?? date('Y'),
        ]);

        return back()->with('success', 'নতুন পরীক্ষা সফলভাবে তৈরি করা হয়েছে!');
    }

    public function storeExamResult(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'subject_name' => 'required|string|max:255',
            'total_marks' => 'required|numeric|min:1',
            'obtained_marks' => 'required|numeric|min:0',
        ]);

        $obtained = (float) $validated['obtained_marks'];
        $total = (float) $validated['total_marks'];
        $percentage = $total > 0 ? ($obtained / $total) * 100 : 0;

        if ($percentage >= 80) {
            $grade = 'A+';
            $gp = 5.0;
        } elseif ($percentage >= 70) {
            $grade = 'A';
            $gp = 4.0;
        } elseif ($percentage >= 60) {
            $grade = 'A-';
            $gp = 3.5;
        } elseif ($percentage >= 50) {
            $grade = 'B';
            $gp = 3.0;
        } elseif ($percentage >= 40) {
            $grade = 'C';
            $gp = 2.0;
        } elseif ($percentage >= 33) {
            $grade = 'D';
            $gp = 1.0;
        } else {
            $grade = 'F';
            $gp = 0.0;
        }

        ExamResult::create([
            'institution_id' => $institution->id,
            'exam_id' => $validated['exam_id'],
            'exam_type_id' => $validated['exam_type_id'] ?? null,
            'student_id' => $validated['student_id'],
            'subject_id' => $validated['subject_id'] ?? null,
            'subject_name' => $validated['subject_name'],
            'total_marks' => $total,
            'obtained_marks' => $obtained,
            'grade' => $grade,
            'grade_point' => $gp,
        ]);

        return back()->with('success', 'শিক্ষার্থীর পরীক্ষার ফলাফল সফলভাবে সংরক্ষিত হয়েছে!');
    }

    /**
     * Store dynamic multi-row marksheet for a student across multiple subjects and exam types.
     */
    public function storeDynamicMarksheet(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
            'rows' => 'required|array|min:1',
            'rows.*.subject_name' => 'required|string|max:255',
            'rows.*.subject_id' => 'nullable',
            'rows.*.total_marks' => 'required|numeric|min:1',
            'rows.*.obtained_marks' => 'required|numeric|min:0',
        ]);

        $exam = Exam::where('institution_id', $institution->id)->findOrFail($validated['exam_id']);
        $student = Student::where('institution_id', $institution->id)->findOrFail($validated['student_id']);
        $examTypeId = $validated['exam_type_id'] ?? $exam->exam_type_id;

        $totalPossible = 0;
        $totalObtained = 0;
        $hasFail = false;
        $gradePointsSum = 0;
        $validSubjectsCount = 0;

        DB::transaction(function () use ($institution, $exam, $student, $examTypeId, $validated, &$totalPossible, &$totalObtained, &$hasFail, &$gradePointsSum, &$validSubjectsCount) {
            foreach ($validated['rows'] as $row) {
                $subjectName = trim($row['subject_name'] ?? '');
                if (empty($subjectName)) {
                    continue;
                }

                $totalMarks = (float) ($row['total_marks'] ?? 100);
                $obtainedMarks = (float) ($row['obtained_marks'] ?? 0);
                $percentage = $totalMarks > 0 ? ($obtainedMarks / $totalMarks) * 100 : 0;

                // Letter grade and grade point calculation
                if ($percentage >= 80) {
                    $grade = 'A+';
                    $gp = 5.0;
                } elseif ($percentage >= 70) {
                    $grade = 'A';
                    $gp = 4.0;
                } elseif ($percentage >= 60) {
                    $grade = 'A-';
                    $gp = 3.5;
                } elseif ($percentage >= 50) {
                    $grade = 'B';
                    $gp = 3.0;
                } elseif ($percentage >= 40) {
                    $grade = 'C';
                    $gp = 2.0;
                } elseif ($percentage >= 33) {
                    $grade = 'D';
                    $gp = 1.0;
                } else {
                    $grade = 'F';
                    $gp = 0.0;
                    $hasFail = true;
                }

                $subjectId = null;
                if (! empty($row['subject_id']) && is_numeric($row['subject_id'])) {
                    $subjectId = (int) $row['subject_id'];
                } else {
                    $subject = Subject::firstOrCreate(
                        [
                            'institution_id' => $institution->id,
                            'name' => $subjectName,
                        ],
                        [
                            'academic_class_id' => $student->academic_class_id,
                            'full_marks' => $totalMarks,
                            'pass_marks' => round($totalMarks * 0.33, 2),
                        ]
                    );
                    $subjectId = $subject->id;
                }

                ExamResult::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'subject_name' => $subjectName,
                    ],
                    [
                        'subject_id' => $subjectId,
                        'exam_type_id' => $examTypeId,
                        'total_marks' => $totalMarks,
                        'obtained_marks' => $obtainedMarks,
                        'grade' => $grade,
                        'grade_point' => $gp,
                    ]
                );

                $totalPossible += $totalMarks;
                $totalObtained += $obtainedMarks;
                $gradePointsSum += $gp;
                $validSubjectsCount++;
            }

            // Student Exam Summary
            if ($validSubjectsCount > 0) {
                $overallGpa = $hasFail ? 0.0 : round(min(5.0, $gradePointsSum / $validSubjectsCount), 2);
                $overallGrade = $this->calculateLetterGrade($overallGpa);
                $status = $hasFail ? 'failed' : 'passed';
                $overallPercentage = $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 2) : 0;

                StudentExamSummary::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'total_marks_possible' => $totalPossible,
                        'total_marks_obtained' => $totalObtained,
                        'percentage' => $overallPercentage,
                        'gpa' => $overallGpa,
                        'grade' => $overallGrade,
                        'status' => $status,
                        'remarks' => "ডাইনামিক মার্কশিট থেকে প্রাপ্ত ফলাফল: {$overallGrade} (GPA: {$overallGpa})",
                    ]
                );
            }
        });

        return back()->with('success', "'{$student->name}' এর ডাইনামিক বিষয় ও পরীক্ষার নম্বর সফলভাবে মার্কশিটে সংরক্ষণ করা হয়েছে!");
    }

    /**
     * Fetch all associated subject records for a student for the edit form.
     */
    public function getStudentMarksheet(Student $student, Request $request): JsonResponse
    {
        $institution = $this->getActiveInstitution();
        if ($student->institution_id !== $institution->id) {
            abort(403);
        }

        $examId = $request->query('exam_id');
        $query = $student->examResults()->with(['subject', 'examType', 'exam']);
        if ($examId) {
            $query->where('exam_id', $examId);
        }

        $records = $query->get()->map(function ($er) {
            return [
                'id' => $er->id,
                'subject_id' => $er->subject_id,
                'subject_name' => $er->subject_name,
                'exam_id' => $er->exam_id,
                'exam_title' => $er->exam?->title,
                'exam_type_id' => $er->exam_type_id ?? $er->exam?->exam_type_id,
                'total_marks' => (float) $er->total_marks,
                'obtained_marks' => (float) $er->obtained_marks,
                'grade' => $er->grade,
                'grade_point' => (float) $er->grade_point,
            ];
        });

        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'roll_number' => $student->roll_number,
            'records' => $records,
        ]);
    }

    /**
     * Store a new subject for the institution.
     */
    public function storeSubject(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'academic_class_id' => 'nullable|exists:academic_classes,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'full_marks' => 'nullable|numeric|min:1',
            'pass_marks' => 'nullable|numeric|min:0',
        ]);

        Subject::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $validated['academic_class_id'] ?? null,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'full_marks' => $validated['full_marks'] ?? 100,
            'pass_marks' => $validated['pass_marks'] ?? 33,
        ]);

        return back()->with('success', 'নতুন বিষয় সফলভাবে তৈরি করা হয়েছে!');
    }

    /**
     * Store a new exam type for the institution.
     */
    public function storeExamType(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'weight_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        ExamType::create([
            'institution_id' => $institution->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? Str::slug($validated['name']),
            'weight_percentage' => $validated['weight_percentage'] ?? 100,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'নতুন পরীক্ষার ধরন সফলভাবে তৈরি করা হয়েছে!');
    }

    public function routines(Request $request): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $classes = $institution->academicClasses()->with('sections')->get();
        $teachers = $institution->teachers()->orderBy('name')->get();

        $query = ClassRoutine::where('institution_id', $institution->id)
            ->with(['academicClass', 'section', 'teacher']);

        if ($request->filled('class_id') && $request->class_id !== 'all') {
            $query->where('academic_class_id', $request->class_id);
        }

        $routines = $query->orderBy('period_number')->orderBy('start_time')->get();

        // Build weekly grid structure for dynamic row/column builder (Student Grid)
        $weeklyGrid = $this->buildWeeklyGridData($institution, $routines, $classes);

        // Build weekly grid structure for Teacher Grid
        $teacherWeeklyGrid = $this->buildTeacherWeeklyGridData($institution, $routines, $teachers);

        // Build class-specific weekly grids (Student Class Routine View)
        $classWeeklyGrids = $this->buildClassWeeklyGrids($institution, $routines, $classes);
        $initialClassList = array_keys($classWeeklyGrids);

        // Template JSON for export/reference
        $templateJson = $this->buildWeeklyRoutineTemplate($institution, $routines);

        // Group routines by teacher for Teacher Routine tab
        $teacherRoutines = $routines->filter(function ($rt) {
            return ! $rt->is_break && ($rt->teacher_name || $rt->teacher_id);
        })->groupBy(function ($rt) {
            return $rt->display_teacher_name;
        });

        return view('institution.routines', compact('institution', 'allInstitutions', 'classes', 'teachers', 'routines', 'templateJson', 'weeklyGrid', 'teacherWeeklyGrid', 'classWeeklyGrids', 'initialClassList', 'teacherRoutines'));
    }

    public function storeRoutine(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'day' => 'required|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'period_number' => 'nullable|integer|min:1|max:20',
            'start_time' => 'required|string|max:20',
            'end_time' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'class_name' => 'nullable|string|max:255',
            'academic_class_id' => 'nullable|exists:academic_classes,id',
            'section_name' => 'nullable|string|max:255',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,id',
            'room' => 'nullable|string|max:100',
            'is_break' => 'nullable|boolean',
        ]);

        // Resolve class
        $classId = $validated['academic_class_id'] ?? null;
        $className = trim($validated['class_name'] ?? '');
        if (! $classId && $className !== '') {
            $academicClass = AcademicClass::where('institution_id', $institution->id)
                ->where(function ($q) use ($className) {
                    $q->where('name', $className)->orWhere('name', 'like', "%{$className}%");
                })->first();

            if (! $academicClass) {
                $level = $this->detectNumericLevel($className);
                $academicClass = AcademicClass::create([
                    'institution_id' => $institution->id,
                    'name' => $className,
                    'numeric_level' => $level,
                    'group' => ($level >= 9 && $level <= 12) ? 'science' : 'general',
                    'medium' => 'bangla',
                ]);
            }
            $classId = $academicClass->id;
        }

        if (! $classId) {
            $classId = $institution->academicClasses()->first()?->id ?? 1;
        }

        // Resolve section
        $sectionId = $validated['section_id'] ?? null;
        $secName = trim($validated['section_name'] ?? '');
        if (! $sectionId && $secName !== '') {
            $section = Section::where('institution_id', $institution->id)
                ->where('academic_class_id', $classId)
                ->where('name', $secName)
                ->first();

            if (! $section) {
                $section = Section::create([
                    'institution_id' => $institution->id,
                    'academic_class_id' => $classId,
                    'name' => $secName,
                    'shift' => 'morning',
                    'room_number' => $validated['room'] ?? 'Room 101',
                ]);
            }
            $sectionId = $section->id;
        }

        if (! $sectionId) {
            $sectionId = Section::where('academic_class_id', $classId)->first()?->id
                ?? Section::create([
                    'institution_id' => $institution->id,
                    'academic_class_id' => $classId,
                    'name' => 'শাখা - ক (Section A)',
                    'shift' => 'morning',
                    'room_number' => $validated['room'] ?? 'Room 101',
                ])->id;
        }

        // Resolve teacher
        $teacherId = $validated['teacher_id'] ?? null;
        $teacherName = trim($validated['teacher_name'] ?? '');
        if (! $teacherId && $teacherName !== '') {
            $teacher = Teacher::where('institution_id', $institution->id)
                ->where('name', 'like', "%{$teacherName}%")
                ->first();
            $teacherId = $teacher?->id;
        }

        ClassRoutine::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $classId,
            'class_name' => $className ?: null,
            'section_id' => $sectionId,
            'section_name' => $secName ?: null,
            'teacher_id' => $teacherId,
            'teacher_name' => $teacherName ?: null,
            'day' => $validated['day'],
            'period_number' => $validated['period_number'] ?? 1,
            'subject' => $validated['subject'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'room' => $validated['room'] ?? null,
            'is_break' => (bool) ($request->boolean('is_break')),
        ]);

        return back()->with('success', 'রুটিন পিরিয়ড সফলভাবে যুক্ত করা হয়েছে!');
    }

    public function importRoutineJson(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $jsonString = $request->input('json_content');

        if ($request->hasFile('json_file')) {
            $jsonString = file_get_contents($request->file('json_file')->getRealPath());
        }

        if (empty($jsonString)) {
            return back()->with('error', 'অনুগ্রহ করে JSON টেক্সট পেস্ট করুন অথবা একটি JSON ফাইল আপলোড করুন।');
        }

        $data = json_decode($jsonString, true);

        if (! is_array($data)) {
            return back()->with('error', 'অবৈধ JSON ফরম্যাট! সঠিক JSON স্ট্রাকচার দিয়ে পুনরায় চেষ্টা করুন।');
        }

        $daysData = $data['days'] ?? $data;
        $importedCount = 0;
        $validDays = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

        if ($request->boolean('replace_existing')) {
            ClassRoutine::where('institution_id', $institution->id)->delete();
        }

        foreach ($daysData as $dayKey => $dayInfo) {
            $matchedDay = null;
            foreach ($validDays as $vd) {
                if (strcasecmp($vd, $dayKey) === 0) {
                    $matchedDay = $vd;
                    break;
                }
            }

            if (! $matchedDay) {
                $bnMap = [
                    'শনিবার' => 'Saturday',
                    'রবিবার' => 'Sunday',
                    'সোমবার' => 'Monday',
                    'মঙ্গলবার' => 'Tuesday',
                    'বুধবার' => 'Wednesday',
                    'বৃহস্পতিবার' => 'Thursday',
                ];
                $matchedDay = $bnMap[$dayKey] ?? null;
            }

            if (! $matchedDay) {
                continue;
            }

            $periods = [];
            if (is_array($dayInfo) && isset($dayInfo['periods']) && is_array($dayInfo['periods'])) {
                $periods = $dayInfo['periods'];
            } elseif (is_array($dayInfo)) {
                $periods = $dayInfo;
            }

            foreach ($periods as $pIdx => $p) {
                if (! is_array($p)) {
                    continue;
                }

                $timeRaw = $p['time'] ?? '';
                $startTime = $p['start_time'] ?? '';
                $endTime = $p['end_time'] ?? '';
                $periodNumber = $p['period_number'] ?? ($pIdx + 1);

                if (empty($startTime) && ! empty($timeRaw)) {
                    $parts = explode('-', $timeRaw);
                    $startTime = trim($parts[0] ?? '09:00 AM');
                    $endTime = trim($parts[1] ?? '09:45 AM');
                }

                $className = trim($p['class_name'] ?? '');
                $sectionName = trim($p['section'] ?? ($p['section_name'] ?? ''));
                $subject = trim($p['subject'] ?? 'সাধারণ ক্লাস');
                $teacherName = trim($p['teacher_name'] ?? '');
                $room = trim($p['room_number'] ?? ($p['room'] ?? ''));
                $isBreak = (bool) ($p['is_break'] ?? false);

                $classId = null;
                if (! empty($className)) {
                    $academicClass = AcademicClass::where('institution_id', $institution->id)
                        ->where(function ($q) use ($className) {
                            $q->where('name', $className)->orWhere('name', 'like', "%{$className}%");
                        })->first();

                    if (! $academicClass) {
                        $level = $this->detectNumericLevel($className);
                        $academicClass = AcademicClass::create([
                            'institution_id' => $institution->id,
                            'name' => $className,
                            'numeric_level' => $level,
                            'group' => ($level >= 9 && $level <= 12) ? 'science' : 'general',
                            'medium' => 'bangla',
                        ]);
                    }
                    $classId = $academicClass->id;
                }

                if (! $classId) {
                    $classId = $institution->academicClasses()->first()?->id ?? 1;
                }

                $section = null;
                if (! empty($sectionName)) {
                    $section = Section::where('institution_id', $institution->id)
                        ->where('academic_class_id', $classId)
                        ->where('name', $sectionName)
                        ->first();
                }

                if (! $section) {
                    $section = Section::where('academic_class_id', $classId)->first()
                        ?? Section::create([
                            'institution_id' => $institution->id,
                            'academic_class_id' => $classId,
                            'name' => $sectionName ?: 'শাখা - ক (Section A)',
                            'shift' => 'morning',
                            'room_number' => $room ?: 'Room 101',
                        ]);
                }

                $teacherId = null;
                if (! empty($teacherName)) {
                    $teacher = Teacher::where('institution_id', $institution->id)
                        ->where('name', 'like', "%{$teacherName}%")
                        ->first();
                    $teacherId = $teacher?->id;
                }

                ClassRoutine::create([
                    'institution_id' => $institution->id,
                    'academic_class_id' => $classId,
                    'class_name' => $className ?: null,
                    'section_id' => $section->id,
                    'section_name' => $sectionName ?: null,
                    'teacher_id' => $teacherId,
                    'teacher_name' => $teacherName ?: null,
                    'day' => $matchedDay,
                    'period_number' => $periodNumber,
                    'subject' => $subject,
                    'start_time' => $startTime ?: '09:00 AM',
                    'end_time' => $endTime ?: '09:45 AM',
                    'room' => $room ?: null,
                    'is_break' => $isBreak,
                ]);

                $importedCount++;
            }
        }

        return back()->with('success', "JSON থেকে সর্বমোট {$importedCount} টি রুটিন পিরিয়ড সফলভাবে ইমপোর্ট করা হয়েছে!");
    }

    public function exportRoutineJson(): JsonResponse
    {
        $institution = $this->getActiveInstitution();
        $routines = ClassRoutine::where('institution_id', $institution->id)
            ->with(['academicClass', 'section', 'teacher'])
            ->orderBy('start_time')
            ->get();

        $data = $this->buildWeeklyRoutineTemplate($institution, $routines);

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="school_weekly_routine.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function destroyRoutine(ClassRoutine $routine): RedirectResponse
    {
        $institution = $this->getActiveInstitution();
        if ($routine->institution_id === $institution->id) {
            $routine->delete();
        }

        return back()->with('success', 'রুটিন পিরিয়ডটি মুছে ফেলা হয়েছে!');
    }

    protected function buildWeeklyRoutineTemplate(Institution $institution, $routines): array
    {
        $dayNames = [
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার',
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
        ];

        $days = [];

        foreach ($dayNames as $dayEn => $dayBn) {
            $dayRoutines = $routines->where('day', $dayEn);
            $periods = [];
            $periodNum = 1;

            if ($dayRoutines->isNotEmpty()) {
                foreach ($dayRoutines as $rt) {
                    $periods[] = [
                        'period_number' => $rt->period_number ?: $periodNum++,
                        'time' => $rt->start_time.' - '.$rt->end_time,
                        'start_time' => $rt->start_time,
                        'end_time' => $rt->end_time,
                        'class_name' => $rt->display_class_name,
                        'section' => $rt->display_section_name,
                        'subject' => $rt->subject,
                        'teacher_name' => $rt->display_teacher_name,
                        'room_number' => $rt->room ?? 'N/A',
                        'is_break' => (bool) $rt->is_break,
                    ];
                }
            } else {
                $periods[] = [
                    'period_number' => 1,
                    'time' => '09:00 AM - 09:45 AM',
                    'start_time' => '09:00 AM',
                    'end_time' => '09:45 AM',
                    'class_name' => 'দশম শ্রেণি (Class 10)',
                    'section' => 'শাখা - ক (Section A)',
                    'subject' => 'বাংলা ১ম পত্র',
                    'teacher_name' => 'মুহাম্মদ রফিকুল ইসলাম',
                    'room_number' => 'Room 201',
                    'is_break' => false,
                ];
                $periods[] = [
                    'period_number' => 2,
                    'time' => '09:45 AM - 10:30 AM',
                    'start_time' => '09:45 AM',
                    'end_time' => '10:30 AM',
                    'class_name' => 'দশম শ্রেণি (Class 10)',
                    'section' => 'শাখা - ক (Section A)',
                    'subject' => 'English for Today',
                    'teacher_name' => 'মিসেস ফারহানা আক্তার',
                    'room_number' => 'Room 201',
                    'is_break' => false,
                ];
                $periods[] = [
                    'period_number' => 3,
                    'time' => '10:30 AM - 11:15 AM',
                    'start_time' => '10:30 AM',
                    'end_time' => '11:15 AM',
                    'class_name' => 'সকল শ্রেণি',
                    'section' => '',
                    'subject' => 'টিফিন ও নাস্তার বিরতি',
                    'teacher_name' => '-',
                    'room_number' => 'ক্যান্টিন',
                    'is_break' => true,
                ];
            }

            $days[$dayEn] = [
                'day_name_bn' => $dayBn,
                'day_name_en' => $dayEn,
                'periods' => $periods,
            ];
        }

        return [
            'institution_name' => $institution->name,
            'academic_year' => date('Y'),
            'schedule_type' => 'সাপ্তাহিক ক্লাস রুটিন (শনিবার হতে বৃহস্পতিবার)',
            'description' => 'বিদ্যালয়ের সাপ্তাহিক ক্লাস সময়সূচী। প্রতিটি দিনের জন্য সময়, ক্লাস, বিষয়, শিক্ষক ও রুম নম্বর উল্লেখ করা আছে।',
            'days' => $days,
        ];
    }

    public function saveRoutineGrid(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $day = $request->input('day', 'Saturday');
        $gridJson = $request->input('grid_json');
        $scopedClassName = trim($request->input('class_name', ''));

        if (empty($gridJson)) {
            return back()->with('error', 'গ্রিডের কোনো ডেটা পাওয়া যায়নি!');
        }

        $gridData = json_decode($gridJson, true);
        if (! is_array($gridData)) {
            return back()->with('error', 'গ্রিড ডেটার ফরম্যাট সঠিক নয়!');
        }

        // Delete existing routines for this specific day (or scoped to class if specified)
        if (! empty($scopedClassName)) {
            ClassRoutine::where('institution_id', $institution->id)
                ->where('day', $day)
                ->where(function ($q) use ($scopedClassName) {
                    $q->where('class_name', $scopedClassName)
                        ->orWhereHas('academicClass', function ($aq) use ($scopedClassName) {
                            $aq->where('name', $scopedClassName);
                        });
                })
                ->delete();
        } else {
            ClassRoutine::where('institution_id', $institution->id)
                ->where('day', $day)
                ->delete();
        }

        $savedCount = 0;
        foreach ($gridData as $row) {
            $rowClassName = trim($row['class_name'] ?? '');
            $rowSectionName = trim($row['row_title'] ?? ($row['section_name'] ?? ''));
            $rowTeacherName = trim($row['teacher_name'] ?? '');

            $periods = $row['periods'] ?? [];
            foreach ($periods as $pIdx => $period) {
                $subject = trim($period['subject'] ?? '');
                $isBreak = (bool) ($period['is_break'] ?? false);

                // If subject is empty and not a break, skip this cell
                if (empty($subject) && ! $isBreak) {
                    continue;
                }

                $cellClassName = trim($period['class_name'] ?? '');
                $className = $scopedClassName ?: ($rowClassName ?: ($cellClassName ?: 'সকল শ্রেণি'));

                $cellTeacherName = trim($period['teacher_name'] ?? '');
                $teacherName = $rowTeacherName ?: $cellTeacherName;

                // Resolve or create class
                $academicClass = AcademicClass::where('institution_id', $institution->id)
                    ->where(function ($q) use ($className) {
                        $q->where('name', $className)->orWhere('name', 'like', "%{$className}%");
                    })->first();

                if (! $academicClass) {
                    $level = $this->detectNumericLevel($className);
                    $academicClass = AcademicClass::create([
                        'institution_id' => $institution->id,
                        'name' => $className,
                        'numeric_level' => $level,
                        'group' => ($level >= 9 && $level <= 12) ? 'science' : 'general',
                        'medium' => 'bangla',
                    ]);
                }

                $sectionName = $rowSectionName ?: ($period['section_name'] ?? 'শাখা - ক (Section A)');
                $section = Section::where('academic_class_id', $academicClass->id)
                    ->where('name', $sectionName)
                    ->first()
                    ?? Section::create([
                        'institution_id' => $institution->id,
                        'academic_class_id' => $academicClass->id,
                        'name' => $sectionName,
                        'shift' => 'morning',
                        'room_number' => 'Room 101',
                    ]);

                $room = trim($period['room'] ?? '');
                $startTime = trim($period['start_time'] ?? '09:00 AM');
                $endTime = trim($period['end_time'] ?? '09:45 AM');
                $periodNum = intval($period['period_number'] ?? ($pIdx + 1));

                $teacherId = null;
                if (! empty($teacherName)) {
                    $teacher = Teacher::where('institution_id', $institution->id)
                        ->where('name', 'like', "%{$teacherName}%")
                        ->first();
                    $teacherId = $teacher?->id;
                }

                ClassRoutine::create([
                    'institution_id' => $institution->id,
                    'academic_class_id' => $academicClass->id,
                    'class_name' => $className,
                    'section_id' => $section->id,
                    'section_name' => $section->name,
                    'teacher_id' => $teacherId,
                    'teacher_name' => $teacherName ?: null,
                    'day' => $day,
                    'period_number' => $periodNum,
                    'subject' => $subject ?: 'টিফিন বিরতি',
                    'start_time' => $startTime ?: '09:00 AM',
                    'end_time' => $endTime ?: '09:45 AM',
                    'room' => $room ?: null,
                    'is_break' => $isBreak,
                ]);

                $savedCount++;
            }
        }

        $dayNamesBn = [
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার',
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
        ];
        $bnDay = $dayNamesBn[$day] ?? $day;

        if ($savedCount === 0) {
            return back()->with('success', "{$bnDay}-এর রুটিন সফলভাবে ফাঁকা (রিসেট) করা হয়েছে!");
        }

        return back()->with('success', "{$bnDay}-এর মাস্টার রুটিন গ্রিডে মোট {$savedCount} টি ঘর সফলভাবে সংরক্ষিত হয়েছে!");
    }

    protected function buildWeeklyGridData(Institution $institution, $routines, $classes): array
    {
        $days = [
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার',
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
        ];

        $defaultPeriods = [
            ['number' => 1, 'start_time' => '09:00 AM', 'end_time' => '09:45 AM', 'label' => '১ম পিরিয়ড'],
            ['number' => 2, 'start_time' => '09:45 AM', 'end_time' => '10:30 AM', 'label' => '২য় পিরিয়ড'],
            ['number' => 3, 'start_time' => '10:30 AM', 'end_time' => '11:15 AM', 'label' => '৩য় পিরিয়ড'],
            ['number' => 4, 'start_time' => '11:15 AM', 'end_time' => '11:45 AM', 'label' => 'টিফিন বিরতি', 'is_break' => true],
            ['number' => 5, 'start_time' => '11:45 AM', 'end_time' => '12:30 PM', 'label' => '৪র্থ পিরিয়ড'],
            ['number' => 6, 'start_time' => '12:30 PM', 'end_time' => '01:15 PM', 'label' => '৫ম পিরিয়ড'],
        ];

        $gridByDay = [];

        foreach ($days as $dayEn => $dayBn) {
            $dayRoutines = $routines->where('day', $dayEn);

            $dayCols = $defaultPeriods;
            $maxPeriod = $dayRoutines->max('period_number');
            if ($maxPeriod && $maxPeriod > count($dayCols)) {
                for ($p = count($dayCols) + 1; $p <= $maxPeriod; $p++) {
                    $dayCols[] = [
                        'number' => $p,
                        'start_time' => '01:15 PM',
                        'end_time' => '02:00 PM',
                        'label' => "{$p}ম পিরিয়ড",
                    ];
                }
            }

            $rows = [];

            // If this day has saved routines, only build rows for the classes that actually have routines!
            if ($dayRoutines->isNotEmpty()) {
                $savedClassNames = $dayRoutines->pluck('display_class_name')->unique()->values();

                foreach ($savedClassNames as $cName) {
                    $cells = [];
                    foreach ($dayCols as $col) {
                        $periodNum = $col['number'];
                        $existing = $dayRoutines->first(function ($rt) use ($cName, $periodNum) {
                            return $rt->display_class_name === $cName && intval($rt->period_number) === intval($periodNum);
                        });

                        if ($existing) {
                            $cells[] = [
                                'period_number' => $periodNum,
                                'start_time' => $existing->start_time,
                                'end_time' => $existing->end_time,
                                'subject' => $existing->subject,
                                'teacher_name' => $existing->display_teacher_name === 'অনুপস্থিত / নির্ধারিত নয়' ? '' : $existing->display_teacher_name,
                                'room' => $existing->room ?? '',
                                'is_break' => (bool) $existing->is_break,
                            ];
                        } else {
                            $cells[] = [
                                'period_number' => $periodNum,
                                'start_time' => $col['start_time'],
                                'end_time' => $col['end_time'],
                                'subject' => '',
                                'teacher_name' => '',
                                'room' => '',
                                'is_break' => false,
                            ];
                        }
                    }

                    $rows[] = [
                        'class_name' => $cName,
                        'periods' => $cells,
                    ];
                }
            }

            $gridByDay[$dayEn] = [
                'day_name_bn' => $dayBn,
                'columns' => $dayCols,
                'rows' => $rows, // Completely empty [] if nothing has been saved for this day!
            ];
        }

        return $gridByDay;
    }

    protected function buildTeacherWeeklyGridData(Institution $institution, $routines, $teachers): array
    {
        $days = [
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার',
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
        ];

        $defaultPeriods = [
            ['number' => 1, 'start_time' => '09:00 AM', 'end_time' => '09:45 AM', 'label' => '১ম পিরিয়ড'],
            ['number' => 2, 'start_time' => '09:45 AM', 'end_time' => '10:30 AM', 'label' => '২য় পিরিয়ড'],
            ['number' => 3, 'start_time' => '10:30 AM', 'end_time' => '11:15 AM', 'label' => '৩য় পিরিয়ড'],
            ['number' => 4, 'start_time' => '11:15 AM', 'end_time' => '11:45 AM', 'label' => 'টিফিন বিরতি', 'is_break' => true],
            ['number' => 5, 'start_time' => '11:45 AM', 'end_time' => '12:30 PM', 'label' => '৪র্থ পিরিয়ড'],
            ['number' => 6, 'start_time' => '12:30 PM', 'end_time' => '01:15 PM', 'label' => '৫ম পিরিয়ড'],
        ];

        $gridByDay = [];

        foreach ($days as $dayEn => $dayBn) {
            $dayRoutines = $routines->where('day', $dayEn);
            $dayCols = $defaultPeriods;

            $rows = [];

            if ($dayRoutines->isNotEmpty()) {
                $savedTeacherNames = $dayRoutines->filter(function ($rt) {
                    return ! $rt->is_break && ($rt->teacher_name || $rt->teacher_id);
                })->pluck('display_teacher_name')->unique()->values();

                foreach ($savedTeacherNames as $tName) {
                    $cells = [];
                    foreach ($dayCols as $col) {
                        $periodNum = $col['number'];
                        $existing = $dayRoutines->first(function ($rt) use ($tName, $periodNum) {
                            return $rt->display_teacher_name === $tName && intval($rt->period_number) === intval($periodNum);
                        });

                        if ($existing) {
                            $cells[] = [
                                'period_number' => $periodNum,
                                'start_time' => $existing->start_time,
                                'end_time' => $existing->end_time,
                                'subject' => $existing->subject,
                                'class_name' => $existing->display_class_name === 'সকল শ্রেণি' ? '' : $existing->display_class_name,
                                'teacher_name' => $tName,
                                'room' => $existing->room ?? '',
                                'is_break' => (bool) $existing->is_break,
                            ];
                        } else {
                            $cells[] = [
                                'period_number' => $periodNum,
                                'start_time' => $col['start_time'],
                                'end_time' => $col['end_time'],
                                'subject' => '',
                                'class_name' => '',
                                'teacher_name' => $tName,
                                'room' => '',
                                'is_break' => false,
                            ];
                        }
                    }

                    $rows[] = [
                        'teacher_name' => $tName,
                        'periods' => $cells,
                    ];
                }
            }

            $gridByDay[$dayEn] = [
                'day_name_bn' => $dayBn,
                'columns' => $dayCols,
                'rows' => $rows,
            ];
        }

        return $gridByDay;
    }

    protected function buildClassWeeklyGrids(Institution $institution, $routines, $classes): array
    {
        $days = [
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার',
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
        ];

        $defaultPeriods = [
            ['number' => 1, 'start_time' => '09:00 AM', 'end_time' => '09:45 AM', 'label' => '১ম পিরিয়ড'],
            ['number' => 2, 'start_time' => '09:45 AM', 'end_time' => '10:30 AM', 'label' => '২য় পিরিয়ড'],
            ['number' => 3, 'start_time' => '10:30 AM', 'end_time' => '11:15 AM', 'label' => '৩য় পিরিয়ড'],
            ['number' => 4, 'start_time' => '11:15 AM', 'end_time' => '11:45 AM', 'label' => 'টিফিন বিরতি', 'is_break' => true],
            ['number' => 5, 'start_time' => '11:45 AM', 'end_time' => '12:30 PM', 'label' => '৪র্থ পিরিয়ড'],
            ['number' => 6, 'start_time' => '12:30 PM', 'end_time' => '01:15 PM', 'label' => '৫ম পিরিয়ড'],
        ];

        $savedClassNames = $routines->pluck('display_class_name')->unique()->filter()->values();
        $definedClassNames = $classes->pluck('name')->unique()->filter()->values();
        $allClassNames = $definedClassNames->concat($savedClassNames)->unique()->values();

        if ($allClassNames->isEmpty()) {
            $allClassNames = collect(['ষষ্ঠ শ্রেণি', 'সপ্তম শ্রেণি', 'অষ্টম শ্রেণি', 'নবম শ্রেণি (ক্লাস নাইন)', 'দশম শ্রেণি', 'অনার্স ১ম বর্ষ']);
        }

        $result = [];

        foreach ($allClassNames as $cName) {
            $classRoutines = $routines->filter(function ($rt) use ($cName) {
                return $rt->display_class_name === $cName;
            });

            $result[$cName] = [];

            foreach ($days as $dayEn => $dayBn) {
                $dayRoutines = $classRoutines->where('day', $dayEn);
                $dayCols = $defaultPeriods;

                $maxPeriod = $dayRoutines->max('period_number');
                if ($maxPeriod && $maxPeriod > count($dayCols)) {
                    for ($p = count($dayCols) + 1; $p <= $maxPeriod; $p++) {
                        $dayCols[] = [
                            'number' => $p,
                            'start_time' => '01:15 PM',
                            'end_time' => '02:00 PM',
                            'label' => "{$p}ম পিরিয়ড",
                        ];
                    }
                }

                $rows = [];
                if ($dayRoutines->isNotEmpty()) {
                    $sections = $dayRoutines->pluck('display_section_name')->unique()->values();
                    if ($sections->isEmpty()) {
                        $sections = collect([' শাখা - ক (Section A)']);
                    }

                    foreach ($sections as $secName) {
                        $secRoutines = $dayRoutines->filter(function ($rt) use ($secName) {
                            return $rt->display_section_name === $secName || empty($secName);
                        });

                        $cells = [];
                        foreach ($dayCols as $col) {
                            $pNum = $col['number'];
                            $existing = $secRoutines->first(function ($rt) use ($pNum) {
                                return intval($rt->period_number) === intval($pNum);
                            });

                            if ($existing) {
                                $cells[] = [
                                    'period_number' => $pNum,
                                    'start_time' => $existing->start_time,
                                    'end_time' => $existing->end_time,
                                    'subject' => $existing->subject,
                                    'teacher_name' => $existing->display_teacher_name === 'অনুপস্থিত / নির্ধারিত নয়' ? '' : $existing->display_teacher_name,
                                    'room' => $existing->room ?? '',
                                    'is_break' => (bool) $existing->is_break,
                                ];
                            } else {
                                $cells[] = [
                                    'period_number' => $pNum,
                                    'start_time' => $col['start_time'],
                                    'end_time' => $col['end_time'],
                                    'subject' => '',
                                    'teacher_name' => '',
                                    'room' => '',
                                    'is_break' => false,
                                ];
                            }
                        }

                        $rows[] = [
                            'row_title' => $secName ?: 'শাখা - ক (Section A)',
                            'periods' => $cells,
                        ];
                    }
                } else {
                    $cells = [];
                    foreach ($dayCols as $col) {
                        $cells[] = [
                            'period_number' => $col['number'],
                            'start_time' => $col['start_time'],
                            'end_time' => $col['end_time'],
                            'subject' => '',
                            'teacher_name' => '',
                            'room' => '',
                            'is_break' => false,
                        ];
                    }
                    $rows[] = [
                        'row_title' => 'শাখা - ক (Section A)',
                        'periods' => $cells,
                    ];
                }

                $result[$cName][$dayEn] = [
                    'day_name_bn' => $dayBn,
                    'columns' => $dayCols,
                    'rows' => $rows,
                ];
            }
        }

        return $result;
    }

    protected function buildClassResultsData(Institution $institution, $classes, $exams): array
    {
        $result = [];

        // All students of the institution with their exam results and summaries, ordered by roll number ASC
        $students = $institution->students()
            ->with(['academicClass', 'section', 'examResults.exam.examType', 'examResults.examType', 'examResults.subject', 'examSummaries'])
            ->orderBy('roll_number', 'asc')
            ->get();

        // 1. Process each defined AcademicClass
        foreach ($classes as $class) {
            $classStudents = $students->where('academic_class_id', $class->id);
            $studentList = [];

            foreach ($classStudents as $student) {
                $studentResults = $student->examResults->map(function ($er) {
                    return [
                        'id' => $er->id,
                        'exam_id' => $er->exam_id,
                        'exam_type_id' => $er->exam_type_id ?? $er->exam?->exam_type_id,
                        'exam_title' => $er->exam?->title ?? 'পরীক্ষা',
                        'exam_type' => $er->examType?->name ?? $er->exam?->examType?->name ?? 'সাধারণ পরীক্ষা',
                        'subject_name' => $er->subject_name,
                        'subject_id' => $er->subject_id,
                        'obtained_marks' => (float) $er->obtained_marks,
                        'total_marks' => (float) $er->total_marks,
                        'grade' => $er->grade,
                        'grade_point' => (float) $er->grade_point,
                    ];
                })->values()->all();

                $summary = $student->examSummaries->first();

                if ($summary && count($studentResults) > 0) {
                    $totalObtained = (float) $summary->total_marks_obtained;
                    $totalPossible = (float) $summary->total_marks_possible;
                    $gpa = (float) $summary->gpa;
                    $grade = $summary->grade ?: $this->calculateLetterGrade($gpa);
                    $status = $summary->status ?: ($grade === 'F' ? 'অনুত্তীর্ণ' : 'উত্তীর্ণ');
                    $rank = $summary->class_merit_rank ?? $summary->section_merit_rank;
                } elseif (count($studentResults) > 0) {
                    $totalObtained = (float) collect($studentResults)->sum('obtained_marks');
                    $totalPossible = (float) collect($studentResults)->sum('total_marks');
                    $hasFail = collect($studentResults)->contains('grade', 'F');
                    $avgGp = collect($studentResults)->avg('grade_point');
                    $gpa = $hasFail ? 0.0 : round(min(5.0, (float) $avgGp), 2);
                    $grade = $this->calculateLetterGrade($gpa);
                    $status = $hasFail ? 'অনুত্তীর্ণ' : 'উত্তীর্ণ';
                    $rank = null;
                } else {
                    $totalObtained = null;
                    $totalPossible = null;
                    $gpa = null;
                    $grade = 'অপেক্ষমাণ';
                    $status = 'ফলাফল এন্ট্রি হয়নি';
                    $rank = null;
                }

                $studentList[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'roll_number' => $student->roll_number,
                    'section_name' => $student->section?->name ?? 'সাধারণ শাখা',
                    'gender' => $student->gender,
                    'total_obtained' => $totalObtained,
                    'total_possible' => $totalPossible,
                    'gpa' => $gpa !== null ? number_format($gpa, 2) : '—',
                    'gpa_raw' => $gpa,
                    'grade' => $grade,
                    'status' => $status,
                    'rank' => $rank,
                    'has_results' => count($studentResults) > 0,
                    'results' => $studentResults,
                ];
            }

            // Also check if any examResults in exams belong to this class where student wasn't in $classStudents
            $classExams = $exams->where('academic_class_id', $class->id);
            foreach ($classExams as $ex) {
                foreach ($ex->examResults as $er) {
                    if ($er->student && ! collect($studentList)->contains('id', $er->student->id)) {
                        $st = $er->student;
                        $studentResults = [
                            [
                                'id' => $er->id,
                                'exam_id' => $ex->id,
                                'exam_type_id' => $er->exam_type_id ?? $ex->exam_type_id,
                                'exam_title' => $ex->title,
                                'subject_name' => $er->subject_name,
                                'subject_id' => $er->subject_id,
                                'obtained_marks' => (float) $er->obtained_marks,
                                'total_marks' => (float) $er->total_marks,
                                'grade' => $er->grade,
                                'grade_point' => (float) $er->grade_point,
                            ],
                        ];
                        $studentList[] = [
                            'id' => $st->id,
                            'name' => $st->name,
                            'roll_number' => $st->roll_number,
                            'section_name' => $st->section?->name ?? 'সাধারণ শাখা',
                            'gender' => $st->gender,
                            'total_obtained' => (float) $er->obtained_marks,
                            'total_possible' => (float) $er->total_marks,
                            'gpa' => number_format((float) $er->grade_point, 2),
                            'gpa_raw' => (float) $er->grade_point,
                            'grade' => $er->grade,
                            'status' => $er->grade === 'F' ? 'অনুত্তীর্ণ' : 'উত্তীর্ণ',
                            'rank' => null,
                            'has_results' => true,
                            'results' => $studentResults,
                        ];
                    }
                }
            }

            // Sort students by roll number
            usort($studentList, function ($a, $b) {
                return ($a['roll_number'] ?? 99999) <=> ($b['roll_number'] ?? 99999);
            });

            $result[$class->name] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'students_count' => count($studentList),
                'passed_count' => collect($studentList)->filter(fn ($s) => $s['grade'] !== 'F' && $s['grade'] !== 'অপেক্ষমাণ' && $s['has_results'])->count(),
                'students' => $studentList,
            ];
        }

        // 2. Also check if there are students in classes not in $classes table (custom classes)
        $orphanStudents = $students->whereNull('academic_class_id');
        if ($orphanStudents->isNotEmpty()) {
            $otherList = [];
            foreach ($orphanStudents as $student) {
                $otherList[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'roll_number' => $student->roll_number,
                    'section_name' => $student->section?->name ?? 'সাধারণ শাখা',
                    'gender' => $student->gender,
                    'total_obtained' => null,
                    'total_possible' => null,
                    'gpa' => '—',
                    'gpa_raw' => null,
                    'grade' => 'অপেক্ষমাণ',
                    'status' => 'ফলাফল এন্ট্রি হয়নি',
                    'rank' => null,
                    'has_results' => false,
                    'results' => [],
                ];
            }
            $result['অন্যান্য শ্রেণি'] = [
                'class_id' => null,
                'class_name' => 'অন্যান্য শ্রেণি',
                'students_count' => count($otherList),
                'passed_count' => 0,
                'students' => $otherList,
            ];
        }

        // If no classes exist at all, give default classes
        if (empty($result)) {
            $defaults = ['ষষ্ঠ শ্রেণি', 'সপ্তম শ্রেণি', 'অষ্টম শ্রেণি', 'নবম শ্রেণি (ক্লাস নাইন)', 'দশম শ্রেণি', 'একাদশ শ্রেণি'];
            foreach ($defaults as $defName) {
                $result[$defName] = [
                    'class_id' => null,
                    'class_name' => $defName,
                    'students_count' => 0,
                    'passed_count' => 0,
                    'students' => [],
                ];
            }
        }

        return $result;
    }

    protected function calculateLetterGrade(float $gpa): string
    {
        if ($gpa >= 5.0) {
            return 'A+';
        }
        if ($gpa >= 4.0) {
            return 'A';
        }
        if ($gpa >= 3.5) {
            return 'A-';
        }
        if ($gpa >= 3.0) {
            return 'B';
        }
        if ($gpa >= 2.0) {
            return 'C';
        }
        if ($gpa >= 1.0) {
            return 'D';
        }

        return 'F';
    }

    public function notices(): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $notices = Notice::where('institution_id', $institution->id)
            ->orWhereNull('institution_id')
            ->latest()
            ->paginate(10);

        return view('institution.notices', compact('institution', 'allInstitutions', 'notices'));
    }

    public function storeNotice(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,teachers,students,guardians',
            'send_sms' => 'nullable|boolean',
        ]);

        Notice::create([
            'institution_id' => $institution->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience' => $validated['audience'],
            'published_at' => Carbon::today()->toDateString(),
            'is_pinned' => true,
        ]);

        $smsNotice = $request->boolean('send_sms') ? ' এবং সকল শিক্ষার্থীর অভিভাবকদের মোবাইলে বাল্ক SMS পাঠানো হয়েছে!' : '';

        return back()->with('success', 'বিজ্ঞপ্তি সফলভাবে প্রকাশিত হয়েছে'.$smsNotice);
    }

    /**
     * Display Academic Years and Enrollments management (Point 3).
     */
    public function academicYears(): View
    {
        $institution = $this->getActiveInstitution();
        $allInstitutions = Institution::all();

        $years = $institution->academicYears()
            ->withCount('enrollments')
            ->orderBy('name', 'desc')
            ->get();

        $classes = $institution->academicClasses()->with('sections')->get();
        $currentYear = $years->firstWhere('is_current', true) ?? $years->first();

        $enrollments = $currentYear
            ? $currentYear->enrollments()->with(['student', 'academicClass', 'section'])->paginate(25)
            : collect();

        return view('institution.academic_years', compact(
            'institution',
            'allInstitutions',
            'years',
            'currentYear',
            'classes',
            'enrollments'
        ));
    }

    /**
     * Store new academic year without deleting previous years.
     */
    public function storeAcademicYear(Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_current')) {
            $institution->academicYears()->update(['is_current' => false]);
        }

        $institution->academicYears()->create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $request->boolean('is_current'),
            'status' => 'active',
        ]);

        return back()->with('success', "নতুন শিক্ষাবর্ষ '{$validated['name']}' সফলভাবে তৈরি হয়েছে! পূর্ববর্তী শিক্ষাবর্ষের সকল তথ্য সংরক্ষিত রয়েছে।");
    }

    /**
     * Activate an academic year as the current year.
     */
    public function activateAcademicYear(AcademicYear $year): RedirectResponse
    {
        $institution = $this->getActiveInstitution();
        if ($year->institution_id !== $institution->id) {
            abort(403);
        }

        $institution->academicYears()->update(['is_current' => false]);
        $year->update(['is_current' => true, 'status' => 'active']);

        return back()->with('success', "শিক্ষাবর্ষ '{$year->name}' বর্তমান শিক্ষাবর্ষ হিসেবে সক্রিয় করা হয়েছে।");
    }

    /**
     * Enroll or promote students into the academic year.
     */
    public function enrollStudentsInYear(AcademicYear $year, Request $request): RedirectResponse
    {
        $institution = $this->getActiveInstitution();
        if ($year->institution_id !== $institution->id) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_class_id' => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'roll_number' => 'nullable|integer',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        StudentEnrollment::updateOrCreate(
            [
                'institution_id' => $institution->id,
                'academic_year_id' => $year->id,
                'student_id' => $student->id,
            ],
            [
                'academic_class_id' => $validated['academic_class_id'],
                'section_id' => $validated['section_id'] ?? null,
                'roll_number' => $validated['roll_number'] ?? $student->roll_number,
                'enrollment_date' => Carbon::today(),
                'status' => 'enrolled',
            ]
        );

        // Update student current class
        $student->update([
            'academic_class_id' => $validated['academic_class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'roll_number' => $validated['roll_number'] ?? $student->roll_number,
        ]);

        return back()->with('success', "শিক্ষার্থী '{$student->name}' শিক্ষাবর্ষ '{$year->name}'-এ সফলভাবে এনরোল/স্থানান্তর করা হয়েছে।");
    }
}
