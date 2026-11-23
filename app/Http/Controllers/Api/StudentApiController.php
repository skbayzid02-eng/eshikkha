<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\ClassReport;
use App\Models\ClassRoutine;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Fee;
use App\Models\GateAttendance;
use App\Models\GuardianStudent;
use App\Models\Homework;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\PushNotification;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentExamSummary;
use App\Models\StudentLeave;
use App\Models\StudentMessage;
use App\Models\Textbook;
use App\Models\User;
use App\Models\UserDeviceToken;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentApiController extends Controller
{
    /**
     * Resolve the target student for the current request.
     * Strictly requires authentication and permission checks. No demo or unauthenticated fallbacks.
     */
    protected function resolveStudent(Request $request): ?Student
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        // 1. If user is a student, return their own linked student profile
        if ($user->role === 'student') {
            return Student::where('user_id', $user->id)
                ->where('institution_id', $user->institution_id)
                ->with(['academicClass', 'section', 'institution'])
                ->first();
        }

        // 2. If user is a guardian, verify that requested child is officially approved and linked
        if ($user->role === 'guardian') {
            $studentId = $request->query('student_id') ?? $request->input('student_id');

            if (! $studentId) {
                $firstLink = GuardianStudent::where('guardian_user_id', $user->id)
                    ->where('status', 'approved')
                    ->first();
                $studentId = $firstLink?->student_id;
            }

            if (! $studentId) {
                return null;
            }

            $isLinked = GuardianStudent::where('guardian_user_id', $user->id)
                ->where('student_id', $studentId)
                ->where('status', 'approved')
                ->exists();

            if (! $isLinked) {
                return null;
            }

            return Student::with(['academicClass', 'section', 'institution'])->find($studentId);
        }

        // 3. If institution admin or teacher, scope strictly by institution
        if (in_array($user->role, ['institution_admin', 'teacher'])) {
            $studentId = $request->query('student_id') ?? $request->input('student_id');
            if ($studentId) {
                return Student::where('institution_id', $user->institution_id)
                    ->with(['academicClass', 'section', 'institution'])
                    ->find($studentId);
            }
        }

        return null;
    }

    /**
     * Get dynamic available classes for student registration and management.
     */
    public function availableClasses(Request $request): JsonResponse
    {
        $institutionId = $request->query('institution_id');

        $query = AcademicClass::with('sections');
        if ($institutionId) {
            $query->where('institution_id', $institutionId);
        }

        $dbClasses = $query->orderBy('numeric_level', 'asc')->get();

        $standardClasses = collect(range(1, 12))->map(function ($lvl) {
            return [
                'id' => "Class {$lvl}",
                'class_id' => $lvl,
                'name' => "Class {$lvl}",
                'name_bn' => $lvl === 1 ? 'প্রথম শ্রেণি' : ($lvl === 2 ? 'দ্বিতীয় শ্রেণি' : "শ্রেণি {$lvl}"),
                'name_en' => "Class {$lvl}",
                'level' => $lvl,
                'numeric_level' => $lvl,
                'group' => ($lvl >= 9 && $lvl <= 12) ? 'science' : 'general',
                'medium' => 'bangla',
                'sections' => [
                    ['id' => $lvl * 10, 'name' => 'শাখা - ক (Section A)', 'shift' => 'morning', 'room_number' => 'Room '.(100 + $lvl)],
                ],
            ];
        });

        $dbFormatted = $dbClasses->map(function ($c) {
            return [
                'id' => $c->name,
                'class_id' => $c->id,
                'name' => $c->name,
                'name_bn' => $c->name,
                'name_en' => $c->name,
                'level' => $c->numeric_level,
                'numeric_level' => $c->numeric_level,
                'group' => $c->group ?? 'general',
                'medium' => $c->medium ?? 'bangla',
                'sections' => $c->sections->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'shift' => $s->shift,
                        'room_number' => $s->room_number,
                    ];
                })->values()->all(),
            ];
        });

        $merged = $standardClasses->concat($dbFormatted)->unique('name')->values()->all();

        return response()->json([
            'success' => true,
            'total' => count($merged),
            'classes' => $merged,
        ]);
    }

    /**
     * Store a dynamic custom class via API.
     */
    public function storeClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'institution_id' => 'nullable|exists:institutions,id',
            'name' => 'required|string|max:255',
            'numeric_level' => 'nullable|integer|between:-2,16',
            'group' => 'nullable|string',
            'medium' => 'nullable|string|in:bangla,english',
            'section_name' => 'nullable|string|max:255',
            'shift' => 'nullable|string|in:morning,day',
            'room_number' => 'nullable|string|max:50',
        ]);

        $instId = $validated['institution_id'] ?? Institution::first()?->id ?? 1;
        $level = $validated['numeric_level'] ?? $this->resolveClassLevel($validated['name']);

        $academicClass = AcademicClass::create([
            'institution_id' => $instId,
            'name' => trim($validated['name']),
            'numeric_level' => $level,
            'group' => $validated['group'] ?? (($level >= 9 && $level <= 12) ? 'science' : 'general'),
            'medium' => $validated['medium'] ?? 'bangla',
        ]);

        $section = Section::create([
            'institution_id' => $instId,
            'academic_class_id' => $academicClass->id,
            'name' => trim($validated['section_name'] ?? 'শাখা - ক (Section A)'),
            'shift' => $validated['shift'] ?? 'morning',
            'room_number' => $validated['room_number'] ?? 'Room '.($level > 0 ? (100 + $level) : 101),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'কাস্টম শ্রেণি সফলভাবে তৈরি ও সিঙ্ক হয়েছে! (Custom class created & synced successfully)',
            'class' => [
                'id' => $academicClass->name,
                'class_id' => $academicClass->id,
                'name' => $academicClass->name,
                'name_bn' => $academicClass->name,
                'name_en' => $academicClass->name,
                'numeric_level' => $academicClass->numeric_level,
                'group' => $academicClass->group,
                'medium' => $academicClass->medium,
                'sections' => [
                    [
                        'id' => $section->id,
                        'name' => $section->name,
                        'shift' => $section->shift,
                        'room_number' => $section->room_number,
                    ],
                ],
            ],
        ], 201);
    }

    /**
     * Get all 19 supported institution types and list of active institutions.
     */
    public function availableInstitutions(): JsonResponse
    {
        $types = Institution::supportedTypes();
        $formattedTypes = [];

        foreach ($types as $key => $typeData) {
            $formattedTypes[] = [
                'id' => $key,
                'name_bn' => $typeData['name_bn'],
                'name_en' => $typeData['name_en'],
                'management' => $typeData['management'],
                'icon' => $typeData['icon'],
                'classes' => $typeData['classes'],
            ];
        }

        $institutions = Institution::where('status', 'active')
            ->select('id', 'name', 'code', 'eiin', 'type', 'district', 'division')
            ->get()
            ->map(function ($inst) {
                return [
                    'id' => $inst->id,
                    'name' => $inst->name,
                    'code' => $inst->code,
                    'eiin' => $inst->eiin,
                    'type' => $inst->type,
                    'type_name' => $inst->type_name,
                    'management' => 'পূর্ণাঙ্গ',
                    'location' => "{$inst->district}, {$inst->division}",
                ];
            });

        return response()->json([
            'success' => true,
            'types_count' => count($formattedTypes),
            'management' => 'পূর্ণাঙ্গ',
            'types' => $formattedTypes,
            'institutions' => $institutions,
        ]);
    }

    /**
     * Register a new Student.
     * Required fields: name, email, password, roll_number, class_name (e.g. Class 1).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'roll_number' => 'required|string|max:50',
            'class_name' => 'required|string|max:100',
            'academic_class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'group' => 'nullable|string|in:science,humanities,business',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'institution_id' => 'nullable|integer',
            'institution_type' => 'nullable|string',
            'institution_name' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        $institutionId = $validated['institution_id'] ?? null;
        if (! $institutionId && ! empty($validated['institution_name'])) {
            $inst = Institution::where('name', $validated['institution_name'])->first();
            if (! $inst) {
                $typeKey = $validated['institution_type'] ?? 'govt_primary_school';
                $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['institution_name']), 0, 4) ?: 'INST').'-'.rand(100, 999);
                $inst = Institution::create([
                    'name' => $validated['institution_name'],
                    'slug' => Str::slug($validated['institution_name']).'-'.rand(100, 999),
                    'code' => $code,
                    'type' => $typeKey,
                    'division' => 'Dhaka',
                    'district' => 'Dhaka',
                    'phone' => '01711000000',
                    'email' => 'info@'.Str::slug($code).'.edu.bd',
                    'status' => 'active',
                    'subscription_plan' => 'standard',
                    'student_capacity' => 1500,
                ]);
            }
            $institutionId = $inst->id;
        } elseif (! $institutionId && ! empty($validated['institution_type'])) {
            $inst = Institution::where('type', $validated['institution_type'])->first();
            $institutionId = $inst?->id;
        }

        $institutionId = $institutionId ?? Institution::first()?->id ?? 1;
        $classNameInput = trim($validated['class_name']);

        // Check if explicit academic_class_id provided
        $academicClass = null;
        if (! empty($validated['academic_class_id'])) {
            $academicClass = AcademicClass::find($validated['academic_class_id']);
            if ($academicClass) {
                $institutionId = $academicClass->institution_id;
                $classNameInput = $academicClass->name;
            }
        }

        $numericLevel = $academicClass ? $academicClass->numeric_level : $this->resolveClassLevel($classNameInput);

        $group = $validated['group'] ?? $academicClass?->group ?? null;
        if (($numericLevel >= 9 && $numericLevel <= 12) && ! $group) {
            $group = 'science';
        }

        // Find or create AcademicClass if not explicitly found
        if (! $academicClass) {
            $academicClass = AcademicClass::where('institution_id', $institutionId)
                ->where(function ($q) use ($classNameInput, $numericLevel) {
                    $q->where('name', $classNameInput)
                        ->orWhere('name', 'like', "%{$classNameInput}%");
                    if ($numericLevel !== null) {
                        $q->orWhere('numeric_level', $numericLevel);
                    }
                })->first();
        }

        if (! $academicClass) {
            $formattedName = $numericLevel === 1 ? 'প্রথম শ্রেণি (Class 1)' : "Class {$numericLevel}";
            $academicClass = AcademicClass::create([
                'institution_id' => $institutionId,
                'name' => $classNameInput ?: $formattedName,
                'numeric_level' => $numericLevel ?? 1,
                'group' => $group,
                'medium' => 'bangla',
            ]);
        }

        // Find or create Section
        $section = null;
        if (! empty($validated['section_id'])) {
            $section = Section::where('academic_class_id', $academicClass->id)->find($validated['section_id']);
        }
        if (! $section) {
            $section = Section::where('academic_class_id', $academicClass->id)->first();
        }
        if (! $section) {
            $section = Section::create([
                'institution_id' => $institutionId,
                'academic_class_id' => $academicClass->id,
                'name' => 'শাখা - ক (Section A)',
                'shift' => 'morning',
                'room_number' => 'Room '.($numericLevel ? (100 + $numericLevel) : 101),
            ]);
        }

        // Create Student User
        $user = User::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'role' => 'student',
            'phone' => $validated['phone'] ?? '01711'.rand(100000, 999999),
            'status' => 'active',
        ]);

        // Generate Admission Number (e.g. ADM-2026-C01-001)
        $admissionYear = date('Y');
        $levelCode = str_pad((string) ($academicClass->numeric_level ?? 1), 2, '0', STR_PAD_LEFT);
        $cleanRoll = preg_replace('/\D/', '', $validated['roll_number']) ?: '1';
        $rollCode = str_pad($cleanRoll, 3, '0', STR_PAD_LEFT);
        $admissionNumber = "ADM-{$admissionYear}-C{$levelCode}-{$rollCode}";

        // Create Student Profile record
        $student = Student::create([
            'institution_id' => $institutionId,
            'user_id' => $user->id,
            'academic_class_id' => $academicClass->id,
            'group' => $group,
            'section_id' => $section->id,
            'admission_number' => $admissionNumber,
            'roll_number' => (int) $cleanRoll,
            'name' => $validated['name'],
            'gender' => $validated['gender'] ?? 'male',
            'dob' => now()->subYears($numericLevel ? (5 + $numericLevel) : 6)->format('Y-m-d'),
            'blood_group' => 'A+',
            'guardian_name' => $validated['father_name'] ?? 'মাহমুদুল হাসান (Mahmudul Hasan)',
            'father_name' => $validated['father_name'] ?? 'মাহমুদুল হাসান (Mahmudul Hasan)',
            'mother_name' => $validated['mother_name'] ?? 'আমিনা বেগম (Amina Begum)',
            'guardian_phone' => $user->phone,
            'guardian_relation' => 'পিতা (Father)',
            'address' => 'ঢাকা, বাংলাদেশ',
            'status' => 'active',
        ]);

        $token = $user->createToken('student_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'রেজিস্ট্রেশন সফলভাবে সম্পন্ন হয়েছে (Registration successful)',
            'token' => $token,
            'student' => $student->load(['academicClass', 'section', 'institution']),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * Student Login (Email, Phone, or Admission Number).
     */
    public function login(Request $request): JsonResponse
    {
        $identifier = trim($request->input('identifier') ?? $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($identifier === '' || $password === '') {
            return response()->json([
                'success' => false,
                'message' => 'ইমেইল অথবা আইডি এবং পাসওয়ার্ড প্রয়োজন। (Identifier and password are required)',
            ], 422);
        }

        $user = User::where(function ($q) use ($identifier) {
            $q->where('email', $identifier)
                ->orWhere('phone', $identifier);
        })->where('role', 'student')->first();

        // Also check if any user with this email exists
        if (! $user) {
            $user = User::where('email', $identifier)->first();
        }

        // Also allow login with admission number
        $student = null;
        if (! $user) {
            $student = Student::where('admission_number', $identifier)->first();
            if ($student && $student->user) {
                $user = $student->user;
            }
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'প্রদত্ত তথ্য অথবা পাসওয়ার্ড সঠিক নয়। (Invalid credentials)',
            ], 401);
        }

        $token = $user->createToken('student_app')->plainTextToken;
        if (! $student) {
            $student = Student::where('user_id', $user->id)
                ->with(['academicClass', 'section', 'institution'])
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'সফলভাবে লগইন হয়েছে (Login successful)',
            'token' => $token,
            'student' => $student,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * 1. Student Profile & Institution Information.
     */
    public function profile(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'name' => $student->name,
                'group' => $student->group,
                'gender' => $student->gender,
                'dob' => $student->dob ? $student->dob->format('Y-m-d') : null,
                'blood_group' => $student->blood_group,
                'photo' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200',
                'address' => $student->address,
                'status' => $student->status,
                'father_name' => $student->father_name ?? $student->guardian_name ?? 'মাহমুদুল হাসান (Mahmudul Hasan)',
                'mother_name' => $student->mother_name ?? 'আমিনা বেগম (Amina Begum)',
                'guardian' => [
                    'name' => $student->guardian_name ?? 'মাহমুদুল হাসান',
                    'father_name' => $student->father_name ?? $student->guardian_name ?? 'মাহমুদুল হাসান (Mahmudul Hasan)',
                    'mother_name' => $student->mother_name ?? 'আমিনা বেগম (Amina Begum)',
                    'phone' => $student->guardian_phone,
                    'relation' => $student->guardian_relation,
                ],
                'class' => [
                    'id' => $student->academic_class_id,
                    'name' => $student->academicClass?->name ?? 'একাদশ শ্রেণি',
                    'medium' => $student->academicClass?->medium ?? 'bangla',
                ],
                'section' => [
                    'id' => $student->section_id,
                    'name' => $student->section?->name ?? 'বিজ্ঞান-১',
                    'room' => $student->section?->room_number ?? 'Room 301',
                    'shift' => $student->section?->shift ?? 'morning',
                ],
                'institution' => [
                    'id' => $student->institution?->id,
                    'name' => $student->institution?->name ?? 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ',
                    'code' => $student->institution?->code ?? 'DRMC-101',
                    'eiin' => $student->institution?->eiin ?? '108252',
                    'type' => $student->institution?->type ?? 'college',
                    'address' => $student->institution?->address ?? 'Mirpur Road, Mohammadpur, Dhaka',
                    'phone' => $student->institution?->phone ?? '01711223344',
                    'email' => $student->institution?->email ?? 'info@drmc.edu.bd',
                ],
            ],
        ]);
    }

    /**
     * Complete Profile Hub covering all 10 requirements:
     * 1. Personal Info
     * 2. Student ID Card
     * 3. Institution Info
     * 4. Guardian Info
     * 5. Fees & Payments
     * 6. Notifications
     * 7. Messages
     * 8. Settings & Language
     * 9. Help & Support
     * 10. Logout Endpoint & Info
     */
    public function profileHub(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $fees = Fee::where('student_id', $student->id)->orderBy('due_date', 'desc')->get();
        $totalPayable = $fees->sum('amount');
        $totalPaid = $fees->where('status', 'paid')->sum('paid_amount');
        $dueAmount = $fees->where('status', '!=', 'paid')->sum('amount');

        if ($fees->isEmpty()) {
            $totalPayable = 18500.0;
            $totalPaid = 15000.0;
            $dueAmount = 3500.0;
        }

        $notices = Notice::where(function ($q) use ($student) {
            $q->where('institution_id', $student->institution_id)->orWhereNull('institution_id');
        })->whereIn('audience', ['all', 'students'])
            ->latest()
            ->take(5)
            ->get();

        $messages = StudentMessage::where('student_id', $student->id)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'personal_info' => [
                'id' => $student->id,
                'name' => $student->name,
                'bangla_name' => $student->name,
                'father_name' => $student->father_name ?? $student->guardian_name ?? 'মাহমুদুল হাসান (Mahmudul Hasan)',
                'mother_name' => $student->mother_name ?? 'আমিনা বেগম (Amina Begum)',
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'gender' => $student->gender ?? 'male',
                'dob' => $student->dob ? $student->dob->format('Y-m-d') : '2008-01-15',
                'formatted_dob' => $student->dob ? $student->dob->format('d M, Y') : '15 Jan, 2008',
                'blood_group' => $student->blood_group ?? 'B+',
                'religion' => 'Islam (ইসলাম)',
                'address' => $student->address ?? 'ধানমন্ডি, ঢাকা - ১২০৯',
                'phone' => '01712-345678',
                'email' => $student->user?->email ?? 'tanvir.ahmed@drmc.edu.bd',
                'emergency_contact' => $student->guardian_phone ?? '01711000000',
                'status' => $student->status ?? 'active',
                'photo' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200',
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'father_name' => $student->father_name ?? $student->guardian_name ?? 'মাহমুদুল হাসান (Mahmudul Hasan)',
                'mother_name' => $student->mother_name ?? 'আমিনা বেগম (Amina Begum)',
                'admission_number' => $student->admission_number,
                'roll_number' => (string) $student->roll_number,
                'class_name' => $student->academicClass?->name ?? 'Class 11 (HSC Science)',
                'section_name' => $student->section?->name ?? 'Science A',
                'date_of_birth' => $student->dob ? $student->dob->format('Y-m-d') : '2008-01-15',
                'blood_group' => $student->blood_group ?? 'B+',
                'gender' => $student->gender ?? 'male',
                'religion' => 'Islam',
                'address' => $student->address ?? 'Dhaka, Bangladesh',
                'phone' => '01711000000',
                'email' => $student->user?->email ?? 'tanvir.ahmed@drmc.edu.bd',
                'emergency_contact' => $student->guardian_phone ?? '01811000000',
            ],
            'student_id' => [
                'card_number' => 'DRMC-STU-2026-0001',
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class_name' => $student->academicClass?->name ?? 'Class 11 (HSC Science)',
                'section_name' => $student->section?->name ?? 'Science A',
                'room_number' => $student->section?->room_number ?? 'Room 301',
                'session' => '2025-2026',
                'shift' => $student->section?->shift ?? 'Morning (প্রভাতী)',
                'barcode' => 'DRMC20260001',
                'qr_data' => 'ESHIKKHA:STUDENT:'.$student->id.':'.$student->admission_number,
                'issued_date' => '01 Jan 2026',
                'valid_until' => '31 Dec 2026',
                'rfid_chip_id' => 'RFID-DRMC-98124',
            ],
            'institution_info' => [
                'id' => $student->institution?->id,
                'name' => $student->institution?->name ?? 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ',
                'name_en' => 'Dhaka Residential Model College (DRMC)',
                'eiin' => $student->institution?->eiin ?? '108252',
                'code' => $student->institution?->code ?? 'DRMC-101',
                'type' => $student->institution?->type ?? 'college',
                'campus' => 'Mirpur Road, Mohammadpur, Dhaka - 1207',
                'principal_name' => 'Brigadier General Kazi Shameem Farhad',
                'phone' => '+880 2-9118322',
                'email' => $student->institution?->email ?? 'principal@drmc.edu.bd',
                'website' => 'https://drmc.edu.bd',
                'established' => '1960',
            ],
            'guardian_info' => [
                'name' => $student->father_name ?? $student->guardian_name ?? 'মোঃ রফিকুল ইসলাম চৌধুরী',
                'father_name' => $student->father_name ?? $student->guardian_name ?? 'মোঃ রফিকুল ইসলাম চৌধুরী',
                'mother_name' => $student->mother_name ?? 'নাসরিন সুলতানা',
                'relation' => $student->guardian_relation ?? 'Father (পিতা)',
                'phone' => $student->guardian_phone ?? '01711000000',
                'occupation' => 'ব্যবসায়ী (Businessman)',
                'address' => $student->address ?? 'ধানমন্ডি, ঢাকা',
                'nid' => '1975269123456789',
            ],
            'fees_and_payments' => [
                'total_payable' => (float) $totalPayable,
                'total_paid' => (float) $totalPaid,
                'due_amount' => (float) $dueAmount,
                'status' => $dueAmount > 0 ? 'due' : 'paid',
                'status_label' => $dueAmount > 0 ? 'বকেয়া ফি আছে (Due)' : 'পরিশোধিত (No Dues)',
                'last_payment_date' => '12 Oct 2026',
                'last_transaction_id' => 'BKTRX987654',
                'recent_history' => $fees->isNotEmpty() ? $fees->map(function ($f) {
                    return [
                        'id' => $f->id,
                        'title' => $f->fee_type,
                        'amount' => (float) $f->amount,
                        'due_date' => $f->due_date ? Carbon::parse($f->due_date)->format('d M, Y') : '10 Nov 2026',
                        'status' => $f->status,
                        'payment_method' => $f->payment_method ?? 'bKash',
                        'transaction_id' => $f->transaction_id ?? 'TRX'.rand(100000, 999999),
                    ];
                }) : [
                    [
                        'id' => 1,
                        'title' => 'November Tuition Fee',
                        'amount' => 2500.0,
                        'due_date' => '10 Nov 2026',
                        'status' => 'due',
                        'payment_method' => null,
                        'transaction_id' => null,
                    ],
                    [
                        'id' => 2,
                        'title' => 'Physics & Chemistry Lab Fee',
                        'amount' => 1000.0,
                        'due_date' => '15 Nov 2026',
                        'status' => 'due',
                        'payment_method' => null,
                        'transaction_id' => null,
                    ],
                    [
                        'id' => 3,
                        'title' => 'October Tuition Fee',
                        'amount' => 2500.0,
                        'due_date' => '10 Oct 2026',
                        'status' => 'paid',
                        'payment_method' => 'bKash',
                        'transaction_id' => 'BKTX874512',
                    ],
                ],
            ],
            'notifications' => [
                'unread_count' => $notices->count(),
                'items' => $notices->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'content' => $n->content,
                        'published_at' => $n->created_at ? $n->created_at->format('d M Y, h:i A') : '18 Nov 2026',
                        'is_urgent' => false,
                    ];
                }),
                'preferences' => [
                    'push_notifications' => true,
                    'sms_alerts' => true,
                    'email_reports' => true,
                ],
            ],
            'messages' => [
                'unread_count' => $messages->where('is_read', false)->count(),
                'items' => $messages->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'teacher_name' => $m->teacher_name ?? 'মুহাম্মদ রফিকুল ইসলাম',
                        'designation' => 'সিনিয়র শিক্ষক (উচ্চতর গণিত)',
                        'subject' => $m->subject,
                        'message' => $m->message,
                        'is_read' => (bool) $m->is_read,
                        'sent_at' => $m->created_at ? $m->created_at->format('d M, h:i A') : 'Today',
                    ];
                }),
            ],
            'settings' => [
                'current_language' => 'bn',
                'languages' => [
                    ['code' => 'bn', 'name' => 'বাংলা (Bengali)'],
                    ['code' => 'en', 'name' => 'English (International)'],
                ],
                'theme' => 'light',
                'biometric_auth' => true,
                'app_version' => 'v2.4.0 (Smart Bangladesh)',
            ],
            'help_and_support' => [
                'hotline' => '16300',
                'hotline_label' => 'জাতীয় শিক্ষা হেল্পলাইন (National Education Helpline)',
                'support_phone' => '+880 9612345678',
                'support_email' => 'support@eshikkha.gov.bd',
                'website' => 'https://eshikkha.gov.bd',
                'support_hours' => '24/7 (২৪ ঘণ্টা সেবা)',
                'faqs' => [
                    [
                        'question' => 'কীভাবে অনলাইনে বকেয়া ফি পরিশোধ করব?',
                        'answer' => 'প্রোফাইল থেকে "ফি ও পেমেন্ট" অপশনে গিয়ে "Pay Online" বাটনে ক্লিক করে bKash, Nagad বা Rocket এর মাধ্যমে তাৎক্ষণিক ফি প্রদান করা যায়।',
                    ],
                    [
                        'question' => 'ডিজিটাল স্টুডেন্ট আইডি কার্ড কীভাবে প্রদর্শন করব?',
                        'answer' => 'প্রোফাইল থেকে "Student ID" কার্ডে ট্যাপ করলে ফুলস্ক্রিন ডিজিটাল আইডি কার্ড ও কিউআর কোড দেখা যাবে।',
                    ],
                    [
                        'question' => 'ছুটির জন্য কীভাবে আবেদন করতে পারি?',
                        'answer' => 'Attendance ট্যাব থেকে "Apply for Leave" বাটনে ক্লিক করে ছুটির কারণ ও তারিখ নির্বাচন করে আবেদন জমা দেওয়া যাবে।',
                    ],
                ],
                'privacy_policy_url' => 'https://eshikkha.gov.bd/privacy',
                'terms_url' => 'https://eshikkha.gov.bd/terms',
            ],
            'logout' => [
                'endpoint' => '/api/student/logout',
                'confirmation_title' => 'লগআউট নিশ্চিতকরণ (Confirm Logout)',
                'confirmation_message' => 'আপনি কি নিশ্চিত যে আপনি শিক্ষার্থী অ্যাকাউন্ট থেকে লগআউট করতে চান?',
            ],
        ]);
    }

    /**
     * Student Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'সফলভাবে লগআউট সম্পন্ন হয়েছে (Logged out successfully)',
        ]);
    }

    /**
     * Student Main Dashboard Overview (covering all 11 items).
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        // Attendance stats
        $today = Carbon::today()->toDateString();
        $todayAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        $totalAttendanceDays = Attendance::where('student_id', $student->id)->count();
        $presentDays = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendancePercentage = $totalAttendanceDays > 0 ? round(($presentDays / $totalAttendanceDays) * 100, 1) : 96.5;

        // Results summary
        $results = ExamResult::where('student_id', $student->id)->get();
        $totalObtained = $results->sum('obtained_marks');
        $totalPossible = $results->sum('total_marks');
        $overallPercentage = $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 1) : 64.6;
        $avgGpa = $results->count() > 0 ? round($results->avg('grade_point'), 2) : 5.00;

        // Homework / Assignments
        $homeworks = Homework::where('academic_class_id', $student->academic_class_id)
            ->orderBy('due_date', 'asc')
            ->get();

        // Today's Routine
        $dayOfWeek = Carbon::now()->format('l'); // e.g. Saturday, Sunday, Monday
        $todayRoutine = ClassRoutine::where('academic_class_id', $student->academic_class_id)
            ->where('day', $dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get();

        if ($todayRoutine->isEmpty()) {
            $todayRoutine = ClassRoutine::where('academic_class_id', $student->academic_class_id)
                ->where('day', 'Saturday')
                ->orderBy('start_time', 'asc')
                ->get();
        }

        // Upcoming Exam Schedules
        $upcomingExams = ExamSchedule::where('academic_class_id', $student->academic_class_id)
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get();

        // Messages from teachers
        $unreadMessagesCount = StudentMessage::where('student_id', $student->id)->where('is_read', false)->count();
        $recentMessages = StudentMessage::where('student_id', $student->id)->latest()->take(3)->get();

        // Notices
        $notices = Notice::where(function ($q) use ($student) {
            $q->where('institution_id', $student->institution_id)->orWhereNull('institution_id');
        })->whereIn('audience', ['all', 'students'])->latest()->take(3)->get();

        // Fees
        $unpaidFees = Fee::where('student_id', $student->id)->where('status', 'unpaid')->get();

        $classLevel = $this->resolveClassLevel($student->academicClass?->name);
        $textbooksData = $this->getTextbooksForLevel($classLevel);

        return response()->json([
            'success' => true,
            'student' => $student,
            'attendance' => [
                'today_status' => $todayAttendance ? $todayAttendance->status : 'present',
                'today_remarks' => $todayAttendance ? $todayAttendance->remarks : 'RFID Gate Entry: 07:54 AM',
                'percentage' => $attendancePercentage,
                'total_days' => max($totalAttendanceDays, 28),
                'present_days' => max($presentDays, 27),
            ],
            'results_summary' => [
                'overall_percentage' => $overallPercentage,
                'gpa' => $avgGpa,
                'letter_grade' => $avgGpa >= 4.0 ? 'A+' : ($avgGpa >= 3.5 ? 'A' : 'B'),
                'total_subjects' => $results->count(),
                'passed_subjects' => $results->where('obtained_marks', '>=', 33)->count(),
                'failed_subjects' => $results->where('obtained_marks', '<', 33)->count(),
            ],
            'textbooks' => $textbooksData['books'],
            'textbooks_summary' => [
                'total_books' => count($textbooksData['books']),
                'class_level' => $classLevel,
                'class_name' => $textbooksData['class_name'],
                'class_name_bn' => $textbooksData['class_name_bn'],
            ],
            'homeworks' => $homeworks,
            'today_routine' => $todayRoutine,
            'upcoming_exams' => $upcomingExams,
            'unread_messages_count' => $unreadMessagesCount,
            'recent_messages' => $recentMessages,
            'notices' => $notices,
            'unpaid_fees_count' => $unpaidFees->count(),
            'unpaid_fees_amount' => $unpaidFees->sum('amount'),
        ]);
    }

    /**
     * 5. Attendance History.
     */
    public function attendanceHistory(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $attendances = Attendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        $total = max($attendances->count(), 1);
        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = $attendances->where('status', 'absent')->count();

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'attendance_percentage' => round(($present / $total) * 100, 1),
            'present_count' => $present,
            'late_count' => $late,
            'absent_count' => $absent,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Compute comprehensive attendance analytics covering:
     * 1. Today's presence (আজ উপস্থিত কি না)
     * 2. Absent days (কোন দিন অনুপস্থিত ছিল)
     * 3. Late arrival days (কোন দিন দেরিতে এসেছে)
     * 4. Monthly attendance breakdown (মাসিক উপস্থিতি)
     * 5. Overall attendance percentage (মোট উপস্থিতির শতাংশ)
     * 6. Leave history (ছুটির ইতিহাস)
     */
    public function getAttendanceAnalyticsData(Student $student): array
    {
        $today = Carbon::today()->toDateString();
        $todayAttendance = Attendance::where('student_id', $student->id)->whereDate('date', $today)->first();

        $allAttendances = Attendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->get();

        $totalDays = $allAttendances->count();
        $presentCount = $allAttendances->where('status', 'present')->count();
        $lateCount = $allAttendances->where('status', 'late')->count();
        $absentCount = $allAttendances->where('status', 'absent')->count();
        $leaveCount = $allAttendances->where('status', 'leave')->count();

        $overallPercentage = $totalDays > 0 ? round((($presentCount + $lateCount) / $totalDays) * 100, 1) : 92.5;

        // 1. Today's Status
        $todayData = [
            'is_present' => $todayAttendance && in_array($todayAttendance->status, ['present', 'late']),
            'status' => $todayAttendance ? $todayAttendance->status : 'present',
            'status_label' => match ($todayAttendance ? $todayAttendance->status : 'present') {
                'present' => 'উপস্থিত (Present)',
                'late' => 'দেরিতে উপস্থিতি (Late)',
                'absent' => 'অনুপস্থিত (Absent)',
                'leave' => 'ছুটিতে (On Leave)',
                default => 'অপেক্ষমান (Pending)',
            },
            'remarks' => $todayAttendance ? $todayAttendance->remarks : 'RFID Gate Entry: 07:54 AM',
            'date' => $today,
        ];

        // 2. Absent Days
        $absentDays = $allAttendances->where('status', 'absent')->map(function ($att) {
            $date = Carbon::parse($att->date);

            return [
                'id' => $att->id,
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('d M, Y'),
                'day_name' => $date->format('l'),
                'status' => 'absent',
                'remarks' => $att->remarks ?: 'অনুপস্থিত (Uninformed absence)',
            ];
        })->values();

        // 3. Late Days
        $lateDays = $allAttendances->where('status', 'late')->map(function ($att) {
            $date = Carbon::parse($att->date);

            return [
                'id' => $att->id,
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('d M, Y'),
                'day_name' => $date->format('l'),
                'status' => 'late',
                'remarks' => $att->remarks ?: 'দেরিতে উপস্থিতি',
            ];
        })->values();

        // 4. Monthly Attendance Breakdown
        $monthlyHistory = $allAttendances->groupBy(function ($att) {
            return Carbon::parse($att->date)->format('F Y');
        })->map(function ($monthRecords, $monthName) {
            $mTotal = $monthRecords->count();
            $mPresent = $monthRecords->where('status', 'present')->count();
            $mLate = $monthRecords->where('status', 'late')->count();
            $mAbsent = $monthRecords->where('status', 'absent')->count();
            $mLeave = $monthRecords->where('status', 'leave')->count();
            $mRate = $mTotal > 0 ? round((($mPresent + $mLate) / $mTotal) * 100, 1) : 100.0;

            return [
                'month_name' => $monthName,
                'total_days' => $mTotal,
                'present_count' => $mPresent,
                'late_count' => $mLate,
                'absent_count' => $mAbsent,
                'leave_count' => $mLeave,
                'percentage' => $mRate,
            ];
        })->values();

        // 5. Leave History
        $leaves = StudentLeave::where('student_id', $student->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'today' => $todayData,
            'overall' => [
                'percentage' => $overallPercentage,
                'total_days' => $totalDays,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
                'leave_count' => $leaveCount,
            ],
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'monthly_history' => $monthlyHistory,
            'leaves_history' => $leaves,
            'recent_records' => $allAttendances->take(20)->values(),
        ];
    }

    /**
     * Dedicated Attendance Analytics Endpoint.
     */
    public function attendanceAnalytics(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $analytics = $this->getAttendanceAnalyticsData($student);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Apply for Student Leave.
     */
    public function applyLeave(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $validated = $request->validate([
            'leave_type' => 'required|string|in:sick,casual,medical,family,holiday',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        $leave = StudentLeave::create([
            'institution_id' => $student->institution_id,
            'student_id' => $student->id,
            'leave_type' => $validated['leave_type'],
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_count' => $daysCount,
            'reason' => $validated['reason'] ?? '',
            'status' => 'approved',
            'approved_by' => 'Auto Approved via Digital Portal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ছুটির আবেদন সফলভাবে গৃহীত হয়েছে! (Leave applied successfully)',
            'leave' => $leave,
        ]);
    }

    /**
     * 6. Exam Results.
     */
    public function results(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $results = ExamResult::where('student_id', $student->id)
            ->with('exam')
            ->get();

        $totalMarksObtained = $results->sum('obtained_marks');
        $totalMarksMax = $results->sum('total_marks');
        $averageGpa = $results->count() > 0 ? round($results->avg('grade_point'), 2) : 0.00;
        $overallPercentage = $totalMarksMax > 0 ? round(($totalMarksObtained / $totalMarksMax) * 100, 1) : 0.0;

        $terms = [
            [
                'term_name' => '1st Midterm',
                'term_period' => '15 Sep to 18 Sep',
                'status_badge' => 'Promoted',
                'is_promoted' => true,
                'results' => $results->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'subject' => $r->subject_name,
                        'marks' => $r->obtained_marks,
                        'total_marks' => $r->total_marks,
                        'status' => $r->obtained_marks >= 33 ? 'PASS' : 'FAILED',
                        'grade' => $r->grade,
                        'date' => '28/08/2026',
                    ];
                }),
            ],
            [
                'term_name' => '2nd Midterm',
                'term_period' => '15 Sep to 18 Sep',
                'status_badge' => '3 Failed',
                'is_promoted' => false,
                'results' => [],
            ],
            [
                'term_name' => '3rd Midterm',
                'term_period' => '15 Sep to 18 Sep',
                'status_badge' => 'Promoted',
                'is_promoted' => true,
                'results' => [],
            ],
        ];

        return response()->json([
            'success' => true,
            'student_name' => $student->name,
            'class_name' => $student->academicClass?->name ?? 'Class 11',
            'overall_percentage' => $overallPercentage > 0 ? $overallPercentage : 58.0,
            'gpa' => $averageGpa,
            'letter_grade' => $averageGpa >= 4.0 ? 'A+' : 'A',
            'terms' => $terms,
            'raw_results' => $results,
        ]);
    }

    /**
     * Compute comprehensive 10-Point Academic Result Details:
     * 1. পরীক্ষার নাম
     * 2. পরীক্ষার তারিখ
     * 3. প্রতিটি বিষয়ের নম্বর (Internal, Theory, Practical)
     * 4. পূর্ণমান
     * 5. প্রাপ্ত নম্বর
     * 6. গ্রেড
     * 7. GPA বা CGPA
     * 8. মোট নম্বর
     * 9. মেধাক্রম (প্রতিষ্ঠানের নীতিমালা অনুযায়ী)
     * 10. PDF মার্কশিট
     */
    public function buildAcademicDetails(Student $student, ?int $examId = null): array
    {
        $exam = null;
        if ($examId) {
            $exam = Exam::find($examId);
        }

        if (! $exam) {
            $exam = Exam::where('institution_id', $student->institution_id)
                ->where('academic_class_id', $student->academic_class_id)
                ->latest()
                ->first()
                ?? Exam::where('institution_id', $student->institution_id)->first()
                ?? Exam::first();
        }

        $examResults = ExamResult::where('student_id', $student->id)
            ->where('exam_id', $exam?->id)
            ->with('internalItems')
            ->get();

        $summary = StudentExamSummary::where('student_id', $student->id)
            ->where('exam_id', $exam?->id)
            ->first();

        $totalPossible = $summary ? $summary->total_marks_possible : ($examResults->sum('total_marks') ?: 800);
        $totalObtained = $summary ? $summary->total_marks_obtained : ($examResults->sum('obtained_marks') ?: 658);
        $gpa = $summary ? $summary->gpa : 4.15;
        $cgpa = $summary ? ($summary->cgpa ?: 8.64) : 8.64;
        $grade = $summary ? $summary->grade : 'A+';
        $sectionRank = $summary ? $summary->section_merit_rank : 1;
        $classRank = $summary ? $summary->class_merit_rank : 3;
        $totalSecStudents = $summary ? $summary->total_students_section : 45;
        $totalClassStudents = $summary ? $summary->total_students_class : 180;
        $percentage = $summary ? $summary->percentage : ($totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 2) : 82.25);
        $status = $summary ? $summary->status : 'promoted';
        $remarks = $summary ? $summary->remarks : 'অসাধারণ ফলাফল! মেধাক্রমে সেকশনে ১ম এবং কলেজে ৩য় স্থান অধিকার করেছে।';

        $subjectMarks = $examResults->map(function ($r) {
            $internalItems = $r->internalItems->map(function ($item) {
                return [
                    'name' => $item->item_name,
                    'marks_obtained' => $item->marks_obtained,
                    'total_marks' => $item->total_marks,
                    'display' => "{$item->marks_obtained}/{$item->total_marks}",
                    'attendance_rate' => $item->attendance_rate,
                ];
            });

            return [
                'id' => $r->id,
                'subject' => $r->subject_name,
                'credits' => $r->credits ?: 3,
                'total_marks' => $r->total_marks,
                'theory_marks' => $r->theory_marks ?: round($r->obtained_marks * 0.5),
                'practical_marks' => $r->practical_marks ?: 0,
                'internal_marks' => $r->internal_marks ?: round($r->obtained_marks * 0.5),
                'obtained_marks' => $r->obtained_marks,
                'highest_marks' => $r->highest_marks ?: min(100, $r->obtained_marks + 8),
                'grade' => $r->grade,
                'grade_point' => $r->grade_point,
                'attendance_rate' => $r->internalItems->isNotEmpty() ? $r->internalItems->first()->attendance_rate : 85.0,
                'internal_items' => $internalItems,
                'status' => $r->obtained_marks >= 33 ? 'PASS' : 'FAILED',
            ];
        });

        $startDate = $exam?->start_date ? Carbon::parse($exam->start_date)->format('d M, Y') : '15 Jun, 2026';
        $endDate = $exam?->end_date ? Carbon::parse($exam->end_date)->format('d M, Y') : '18 Jun, 2026';

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class_name' => $student->academicClass?->name ?? 'Class 11',
                'section_name' => $student->section?->name ?? 'Science A',
                'institution_name' => $student->institution?->name ?? 'Dhaka Residential Model College',
                'institution_code' => $student->institution?->code ?? 'DRMC-101',
                'institution_eiin' => $student->institution?->eiin ?? '108252',
            ],
            'exam_name' => $exam?->title ?? '1st Midterm Examination 2026',
            'exam_id' => $exam?->id ?? 2,
            'exam_date' => "{$startDate} to {$endDate}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'session_year' => $exam?->session_year ?? '2026',
            'subject_marks' => $subjectMarks,
            'total_marks_possible' => $totalPossible,
            'total_marks_obtained' => $totalObtained,
            'grade' => $grade,
            'gpa' => $gpa,
            'cgpa' => $cgpa,
            'gpa_cgpa_label' => "GPA: {$gpa} / 5.00 • CGPA: {$cgpa}",
            'total_marks' => "{$totalObtained} / {$totalPossible} ({$percentage}%)",
            'percentage' => $percentage,
            'merit_position' => [
                'section_rank' => $sectionRank,
                'class_rank' => $classRank,
                'total_students_section' => $totalSecStudents,
                'total_students_class' => $totalClassStudents,
                'badge' => "১ম স্থান (Section Rank: {$sectionRank}/{$totalSecStudents})",
                'description' => "সেকশনে {$sectionRank}ম স্থান এবং সার্বিক ক্লাসে {$classRank}য় স্থান (Class Rank {$classRank}/{$totalClassStudents})",
            ],
            'status' => $status,
            'remarks' => $remarks,
            'pdf_marksheet_url' => url("/api/student/marksheet/{$exam?->id}/pdf?student_id={$student->id}"),
        ];
    }

    /**
     * Dedicated Academic Results Endpoint covering all 10 items.
     */
    public function academicResults(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $examId = $request->query('exam_id');
        $academicDetails = $this->buildAcademicDetails($student, $examId ? (int) $examId : null);

        return response()->json([
            'success' => true,
            'data' => $academicDetails,
        ]);
    }

    /**
     * Printable Official PDF Marksheet View.
     */
    public function marksheetPdf(Request $request, int $examId)
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            abort(404, 'শিক্ষার্থী পাওয়া যায়নি');
        }

        $details = $this->buildAcademicDetails($student, $examId);

        return view('marksheet_pdf', compact('details'));
    }

    /**
     * 7. Homework & Assignments.
     */
    public function homework(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $query = Homework::where('academic_class_id', $student->academic_class_id)->with('teacher');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $homeworks = $query->orderBy('due_date', 'asc')->get();

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'total_count' => $homeworks->count(),
            'due_count' => $homeworks->where('status', 'due')->count(),
            'overdue_count' => $homeworks->where('status', 'overdue')->count(),
            'submitted_count' => $homeworks->where('status', 'submitted')->count(),
            'homeworks' => $homeworks,
        ]);
    }

    /**
     * Submit Homework.
     */
    public function submitHomework(Request $request, int $id): JsonResponse
    {
        $homework = Homework::findOrFail($id);

        $homework->update([
            'status' => 'submitted',
            'submitted_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'হোমওয়ার্ক সফলভাবে জমা দেওয়া হয়েছে! (Homework submitted successfully)',
            'homework' => $homework,
        ]);
    }

    /**
     * 8. Class Routine / Timetable.
     */
    public function routine(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $routines = ClassRoutine::where(function ($q) use ($student) {
            $q->where('academic_class_id', $student->academic_class_id);
            if ($student->academicClass?->name) {
                $q->orWhere('class_name', $student->academicClass->name);
            }
        })
            ->with('teacher')
            ->orderBy('period_number', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Group by Day
        $grouped = $routines->groupBy('day');

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'class_name' => $student->academicClass?->name,
            'section_name' => $student->section?->name,
            'grouped_routine' => $grouped,
            'all_routines' => $routines,
        ]);
    }

    /**
     * 9. Exam Routine / Schedules.
     */
    public function examRoutine(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $schedules = ExamSchedule::where('academic_class_id', $student->academic_class_id)
            ->with('exam')
            ->orderBy('exam_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'class_name' => $student->academicClass?->name,
            'exam_schedules' => $schedules,
        ]);
    }

    /**
     * 10. Teacher's Messages.
     */
    public function messages(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $messages = StudentMessage::where('student_id', $student->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'unread_count' => $messages->where('is_read', false)->count(),
            'messages' => $messages,
        ]);
    }

    /**
     * Mark Teacher Message as Read.
     */
    public function markMessageAsRead(Request $request, int $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        $message = StudentMessage::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'মেসেজ পড়া হয়েছে (Marked as read)',
            'data' => $message,
        ]);
    }

    /**
     * 11. Institution Notices.
     */
    public function notices(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $notices = Notice::where(function ($q) use ($student) {
            $q->where('institution_id', $student->institution_id)->orWhereNull('institution_id');
        })->whereIn('audience', ['all', 'students'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'notices' => $notices,
        ]);
    }

    /**
     * Fees & Payment.
     */
    public function fees(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $fees = Fee::where('student_id', $student->id)->orderBy('due_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'fees' => $fees,
        ]);
    }

    public function payFee(Request $request, int $feeId): JsonResponse
    {
        $student = $this->resolveStudent($request);

        $fee = Fee::where('id', $feeId)->where('student_id', $student->id)->firstOrFail();

        $validated = $request->validate([
            'payment_method' => 'required|in:bKash,Nagad,Rocket',
            'account_number' => 'required|string',
        ]);

        $trxId = strtoupper($validated['payment_method'][0]).'TX'.strtoupper(Str::random(8));

        $fee->update([
            'status' => 'paid',
            'paid_amount' => $fee->amount,
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $trxId,
            'paid_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "ফি সফলভাবে পরিশোধিত হয়েছে! ট্রানজ্যাকশন আইডি: {$trxId}",
            'transaction_id' => $trxId,
            'fee' => $fee,
        ]);
    }

    /**
     * Combined One-Stop Student Portal Data.
     */
    public function portalData(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        // Run profile, dashboard, homework, routine, exam-routine, results, messages, notices
        $profile = $this->profile($request)->getData(true);
        $dashboard = $this->dashboard($request)->getData(true);
        $results = $this->results($request)->getData(true);
        $homework = $this->homework($request)->getData(true);
        $routine = $this->routine($request)->getData(true);
        $examRoutine = $this->examRoutine($request)->getData(true);
        $messages = $this->messages($request)->getData(true);
        $notices = $this->notices($request)->getData(true);
        $attendance = $this->attendanceHistory($request)->getData(true);
        $attendanceAnalytics = $this->getAttendanceAnalyticsData($student);
        $studyData = $this->study($request)->getData(true);
        $academicDetails = $this->buildAcademicDetails($student);

        return response()->json([
            'success' => true,
            'profile' => $profile['student'] ?? null,
            'dashboard' => $dashboard,
            'results' => $results,
            'homework' => $homework,
            'routine' => $routine,
            'exam_routine' => $examRoutine,
            'messages' => $messages,
            'notices' => $notices['notices'] ?? [],
            'attendance' => $attendance,
            'attendance_analytics' => $attendanceAnalytics,
            'study' => $studyData,
            'academic_details' => $academicDetails,
        ]);
    }

    /**
     * Study Hub Endpoint covering subjects, curriculum, homework, practicals, and routine.
     */
    public function study(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $isClass1 = ($student->academicClass?->numeric_level === 1)
            || str_contains(strtolower($student->academicClass?->name ?? ''), 'class 1')
            || str_contains($student->academicClass?->name ?? '', 'প্রথম')
            || str_contains($student->academicClass?->name ?? '', '১ম');

        // Subject curriculum & syllabus
        if ($isClass1) {
            $subjects = [
                [
                    'id' => 1,
                    'name' => 'Amar Bangla Boi',
                    'bangla_name' => 'আমার বাংলা বই (১ম শ্রেণি)',
                    'code' => 'BAN-101',
                    'chapters_count' => 8,
                    'completed_chapters' => 5,
                    'progress' => 65,
                    'teacher_name' => 'মিসেস রুমানা পারভীন',
                    'teacher_designation' => 'সহকারী শিক্ষক (প্রাথমিক শাখা)',
                    'room' => 'Room 101',
                    'color_theme' => 'emerald',
                    'icon' => 'menu_book_rounded',
                    'materials_count' => 12,
                    'topics' => ['বর্ণমালা শেখা (অ-ঔ, ক-ঁ)', 'ইচ্ছে মতো আঁকি', 'ছড়া: আতা গাছে তোতা পাখি', 'ছোট গল্প ও বাক্য গঠন'],
                ],
                [
                    'id' => 2,
                    'name' => 'English for Today',
                    'bangla_name' => 'English for Today (Class 1)',
                    'code' => 'ENG-101',
                    'chapters_count' => 6,
                    'completed_chapters' => 4,
                    'progress' => 68,
                    'teacher_name' => 'সালমা জাহান',
                    'teacher_designation' => 'সহকারী শিক্ষক',
                    'room' => 'Room 101',
                    'color_theme' => 'blue',
                    'icon' => 'school_rounded',
                    'materials_count' => 10,
                    'topics' => ['Alphabet (A to Z)', 'Greetings & Farewells', 'Numbers 1 to 10', 'Rhymes & Action Songs'],
                ],
                [
                    'id' => 3,
                    'name' => 'Elementary Mathematics',
                    'bangla_name' => 'প্রাথমিক গণিত (১ম শ্রেণি)',
                    'code' => 'MATH-101',
                    'chapters_count' => 7,
                    'completed_chapters' => 4,
                    'progress' => 60,
                    'teacher_name' => 'মোঃ জহিরুল হক',
                    'teacher_designation' => 'সহকারী শিক্ষক',
                    'room' => 'Room 101',
                    'color_theme' => 'orange',
                    'icon' => 'calculate_rounded',
                    'materials_count' => 14,
                    'topics' => ['তুলনা করি (কম-বেশি, ছোট-বড়)', 'সংখ্যা গণনা (১-২০)', 'যোগের প্রাথমিক ধারণা', 'জ্যামিতিক আকৃতি পরিচিতি'],
                ],
                [
                    'id' => 4,
                    'name' => 'Arts, Crafts & Environment',
                    'bangla_name' => 'চারু ও কারুকলা এবং পরিবেশ পরিচিতি',
                    'code' => 'ART-101',
                    'chapters_count' => 5,
                    'completed_chapters' => 3,
                    'progress' => 75,
                    'teacher_name' => 'মাহিয়া চৌধুরী',
                    'teacher_designation' => 'চারুকলা শিক্ষক',
                    'room' => 'Art Room',
                    'color_theme' => 'purple',
                    'icon' => 'palette_rounded',
                    'materials_count' => 8,
                    'topics' => ['রং ও তুলি পরিচিতি', 'আমাদের জাতীয় পতাকা আঁকা', 'চারপাশের গাছপালা ও পশুপাখি', 'পরিষ্কার-পরিচ্ছন্নতা'],
                ],
            ];
        } else {
            $subjects = [
                [
                    'id' => 1,
                    'name' => 'Higher Mathematics',
                    'bangla_name' => 'উচ্চতর গণিত',
                    'code' => 'HM-265',
                    'chapters_count' => 10,
                    'completed_chapters' => 7,
                    'progress' => 70,
                    'teacher_name' => 'মুহাম্মদ রফিকুল ইসলাম',
                    'teacher_designation' => 'সিনিয়র সহকারী শিক্ষক',
                    'room' => 'Room 301',
                    'color_theme' => 'blue',
                    'icon' => 'calculate_rounded',
                    'materials_count' => 14,
                    'topics' => ['Matrix & Determinants', 'Vectors', 'Differentiation', 'Integration', 'Coordinate Geometry'],
                ],
                [
                    'id' => 2,
                    'name' => 'Physics',
                    'bangla_name' => 'পদার্থবিজ্ঞান',
                    'code' => 'PHY-174',
                    'chapters_count' => 10,
                    'completed_chapters' => 6,
                    'progress' => 60,
                    'teacher_name' => 'ড. আহসান হাবীব',
                    'teacher_designation' => 'সহযোগী অধ্যাপক',
                    'room' => 'Lab 2',
                    'color_theme' => 'orange',
                    'icon' => 'bolt_rounded',
                    'materials_count' => 12,
                    'topics' => ['Vector Mechanics', 'Work, Power & Energy', 'Gravitation & Gravity', 'Structural Properties of Matter'],
                ],
                [
                    'id' => 3,
                    'name' => 'Chemistry',
                    'bangla_name' => 'রসায়ন',
                    'code' => 'CHEM-176',
                    'chapters_count' => 8,
                    'completed_chapters' => 5,
                    'progress' => 62,
                    'teacher_name' => 'মোঃ মোখলেসুর রহমান',
                    'teacher_designation' => 'সহকারী অধ্যাপক',
                    'room' => 'Lab 1',
                    'color_theme' => 'emerald',
                    'icon' => 'science_rounded',
                    'materials_count' => 10,
                    'topics' => ['Qualitative Chemistry', 'Periodic Table & Chemical Bond', 'Chemical Changes', 'Environmental Chemistry'],
                ],
                [
                    'id' => 4,
                    'name' => 'English',
                    'bangla_name' => 'ইংরেজি',
                    'code' => 'ENG-107',
                    'chapters_count' => 12,
                    'completed_chapters' => 9,
                    'progress' => 75,
                    'teacher_name' => 'মিসেস ফারহানা আক্তার',
                    'teacher_designation' => 'প্রভাষক',
                    'room' => 'Room 301',
                    'color_theme' => 'purple',
                    'icon' => 'menu_book_rounded',
                    'materials_count' => 16,
                    'topics' => ['Reading Skills', 'Grammar in Context', 'Writing Formal Letters & Essays', 'Vocabulary Building'],
                ],
                [
                    'id' => 5,
                    'name' => 'Information & Communication Technology',
                    'bangla_name' => 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)',
                    'code' => 'ICT-275',
                    'chapters_count' => 6,
                    'completed_chapters' => 5,
                    'progress' => 83,
                    'teacher_name' => 'প্রকৌশলী তানভীর আহমেদ',
                    'teacher_designation' => 'আইসিটি ইনস্ট্রাক্টর',
                    'room' => 'Computer Lab',
                    'color_theme' => 'teal',
                    'icon' => 'computer_rounded',
                    'materials_count' => 18,
                    'topics' => ['Information & Communication World', 'Communication Systems & Networking', 'HTML & Web Design', 'C Programming', 'Database Management System'],
                ],
                [
                    'id' => 6,
                    'name' => 'Biology',
                    'bangla_name' => 'জীববিজ্ঞান',
                    'code' => 'BIO-178',
                    'chapters_count' => 12,
                    'completed_chapters' => 7,
                    'progress' => 58,
                    'teacher_name' => 'ড. নাসরিন জাহান',
                    'teacher_designation' => 'সহকারী অধ্যাপক',
                    'room' => 'Biology Lab',
                    'color_theme' => 'indigo',
                    'icon' => 'biotech_rounded',
                    'materials_count' => 11,
                    'topics' => ['Cell and its Structure', 'Cell Division', 'Plant Physiology', 'Genetics & Evolution'],
                ],
            ];
        }

        // Homeworks
        $homeworks = Homework::where('academic_class_id', $student->academic_class_id)
            ->with('teacher')
            ->orderBy('due_date', 'asc')
            ->get();

        // Practicals
        $practicals = [
            [
                'id' => 1,
                'title' => 'Chemistry Titration & Acid-Base Practical',
                'bangla_title' => 'রসায়ন এসিড-ক্ষার টাইট্রেশন ল্যাব',
                'subject' => 'Chemistry',
                'experiments_count' => 12,
                'lab_room' => 'Chemistry Lab 1',
                'icon' => 'science_rounded',
                'color_theme' => 'emerald',
            ],
            [
                'id' => 2,
                'title' => 'Physics Screw Gauge & Spherometer Lab',
                'bangla_title' => 'পদার্থবিজ্ঞান স্লাইড ক্যালিপার্স ও ভার্নিয়ার ল্যাব',
                'subject' => 'Physics',
                'experiments_count' => 10,
                'lab_room' => 'Physics Lab 2',
                'icon' => 'bolt_rounded',
                'color_theme' => 'amber',
            ],
            [
                'id' => 3,
                'title' => 'C Programming & Web Dev Practical Lab',
                'bangla_title' => 'সি প্রোগ্রামিং ও এইচটিএমএল ল্যাব',
                'subject' => 'ICT',
                'experiments_count' => 15,
                'lab_room' => 'Central Computer Lab',
                'icon' => 'computer_rounded',
                'color_theme' => 'teal',
            ],
            [
                'id' => 4,
                'title' => 'Plant Tissue Identification & Cell Slide Lab',
                'bangla_title' => 'উদ্ভিদ টিস্যু ও কোষ স্লাইড পর্যবেক্ষণ',
                'subject' => 'Biology',
                'experiments_count' => 8,
                'lab_room' => 'Biology Lab',
                'icon' => 'biotech_rounded',
                'color_theme' => 'indigo',
            ],
        ];

        // Routines
        $dayOfWeek = Carbon::now()->format('l');
        $routines = ClassRoutine::where('academic_class_id', $student->academic_class_id)
            ->where('day', $dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get();

        if ($routines->isEmpty()) {
            $routines = ClassRoutine::where('academic_class_id', $student->academic_class_id)
                ->where('day', 'Saturday')
                ->orderBy('start_time', 'asc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'summary' => [
                'total_subjects' => count($subjects),
                'due_homework_count' => $homeworks->where('status', 'due')->count(),
                'overdue_homework_count' => $homeworks->where('status', 'overdue')->count(),
                'submitted_homework_count' => $homeworks->where('status', 'submitted')->count(),
                'practicals_count' => count($practicals),
                'today_classes_count' => $routines->count(),
            ],
            'subjects' => $subjects,
            'homeworks' => $homeworks,
            'practicals' => $practicals,
            'routines' => $routines,
        ]);
    }

    /**
     * Unified 5-Button Student App Navigation Hub:
     * 1. Home
     * 2. Study
     * 3. Attendance
     * 4. Results
     * 5. Profile
     */
    public function navigationHub(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'শিক্ষার্থীর তথ্য পাওয়া যায়নি'], 404);
        }

        $profileData = $this->profile($request)->getData(true);
        $dashboardData = $this->dashboard($request)->getData(true);
        $studyData = $this->study($request)->getData(true);
        $attendanceAnalytics = $this->getAttendanceAnalyticsData($student);
        $academicDetails = $this->buildAcademicDetails($student);

        return response()->json([
            'success' => true,
            'buttons' => [
                'home' => [
                    'id' => 'home',
                    'title' => 'Home',
                    'bangla_title' => 'হোম',
                    'icon' => 'home_filled',
                    'badge' => null,
                    'summary' => [
                        'greeting' => 'Good Morning!',
                        'student_name' => $student->name,
                        'banner_title' => 'SPECIAL Coaching Class Started!',
                        'today_attendance' => $dashboardData['attendance']['today_status'] ?? 'present',
                        'unread_messages' => $dashboardData['unread_messages_count'] ?? 0,
                        'pending_homework' => $studyData['summary']['due_homework_count'] ?? 0,
                    ],
                ],
                'study' => [
                    'id' => 'study',
                    'title' => 'Study',
                    'bangla_title' => 'স্টাডি',
                    'icon' => 'auto_stories_rounded',
                    'badge' => ($studyData['summary']['due_homework_count'] ?? 0) > 0 ? (string) ($studyData['summary']['due_homework_count']) : null,
                    'summary' => $studyData['summary'],
                ],
                'attendance' => [
                    'id' => 'attendance',
                    'title' => 'Attendance',
                    'bangla_title' => 'উপস্থিতি',
                    'icon' => 'how_to_reg_rounded',
                    'badge' => null,
                    'summary' => [
                        'today_status' => $attendanceAnalytics['today']['status'] ?? 'present',
                        'percentage' => $attendanceAnalytics['overall']['percentage'] ?? 92.5,
                        'absent_count' => $attendanceAnalytics['overall']['absent_count'] ?? 2,
                        'late_count' => $attendanceAnalytics['overall']['late_count'] ?? 3,
                        'leave_count' => $attendanceAnalytics['overall']['leave_count'] ?? 6,
                    ],
                ],
                'results' => [
                    'id' => 'results',
                    'title' => 'Results',
                    'bangla_title' => 'ফলাফল',
                    'icon' => 'assessment_outlined',
                    'badge' => 'A+',
                    'summary' => [
                        'latest_exam' => $academicDetails['exam_name'],
                        'overall_percentage' => $academicDetails['percentage'],
                        'gpa' => $academicDetails['gpa'],
                        'cgpa' => $academicDetails['cgpa'],
                        'letter_grade' => $academicDetails['grade'],
                        'section_rank' => $academicDetails['merit_position']['section_rank'],
                        'class_rank' => $academicDetails['merit_position']['class_rank'],
                        'pdf_url' => $academicDetails['pdf_marksheet_url'],
                    ],
                ],
                'profile' => [
                    'id' => 'profile',
                    'title' => 'Profile',
                    'bangla_title' => 'প্রোফাইল',
                    'icon' => 'person_outline',
                    'badge' => null,
                    'summary' => [
                        'name' => $student->name,
                        'roll' => $student->roll_number,
                        'admission_number' => $student->admission_number,
                        'class_name' => $student->academicClass?->name ?? 'Class 11',
                        'institution_name' => $student->institution?->name ?? 'DRMC',
                    ],
                ],
            ],
            'home_data' => $dashboardData,
            'study_data' => $studyData,
            'textbooks' => $this->getTextbooksForLevel($this->resolveClassLevel($student->academicClass?->name)),
            'attendance_data' => $attendanceAnalytics,
            'results_data' => $academicDetails,
            'profile_data' => $profileData['student'] ?? null,
        ]);
    }

    /**
     * Resolve class numeric level (-2 to 16) from class name.
     */
    public function resolveClassLevel(?string $className, ?int $explicitLevel = null): int
    {
        if ($explicitLevel !== null && $explicitLevel >= -2 && $explicitLevel <= 16) {
            return $explicitLevel;
        }

        if (! $className) {
            return 1;
        }

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

        return 1;
    }

    /**
     * Get Textbooks and Chapters for a specific Class level (-2 to 16) and optional group.
     */
    public function getTextbooksForLevel(int $level, ?string $group = null): array
    {
        $level = max(-2, min(16, $level));

        $query = Textbook::where('class_level', $level)
            ->where('is_active', true)
            ->with('chapters');

        if (($level >= 9 && $level <= 12) && $group && $group !== 'all') {
            $query->whereIn('group', ['general', $group]);
        }

        $dbBooks = $query->orderBy('sort_order', 'asc')->get();

        $classNameMap = [
            -2 => ['en' => 'Play Group', 'bn' => 'প্লে গ্রুপ'],
            -1 => ['en' => 'Nursery', 'bn' => 'নার্সারি'],
            0 => ['en' => 'KG', 'bn' => 'কেজি'],
            1 => ['en' => 'Class 1', 'bn' => 'প্রথম শ্রেণি'],
            2 => ['en' => 'Class 2', 'bn' => 'দ্বিতীয় শ্রেণি'],
            3 => ['en' => 'Class 3', 'bn' => 'তৃতীয় শ্রেণি'],
            4 => ['en' => 'Class 4', 'bn' => 'চতুর্থ শ্রেণি'],
            5 => ['en' => 'Class 5', 'bn' => 'পঞ্চম শ্রেণি'],
            6 => ['en' => 'Class 6', 'bn' => 'ষষ্ঠ শ্রেণি'],
            7 => ['en' => 'Class 7', 'bn' => 'সপ্তম শ্রেণি'],
            8 => ['en' => 'Class 8', 'bn' => 'অষ্টম শ্রেণি'],
            9 => ['en' => 'Class 9', 'bn' => 'নবম শ্রেণি'],
            10 => ['en' => 'Class 10', 'bn' => 'দশম শ্রেণি'],
            11 => ['en' => 'Class 11', 'bn' => 'একাদশ শ্রেণি'],
            12 => ['en' => 'Class 12', 'bn' => 'দ্বাদশ শ্রেণি'],
            13 => ['en' => 'Diploma', 'bn' => 'ডিপ্লোমা'],
            14 => ['en' => 'Honours', 'bn' => 'অনার্স'],
            15 => ['en' => 'Degree', 'bn' => 'ডিগ্রি'],
            16 => ['en' => 'Masters', 'bn' => 'মাস্টার্স'],
        ];

        $className = $classNameMap[$level]['en'] ?? "Class {$level}";
        $classNameBn = $classNameMap[$level]['bn'] ?? "শ্রেণি {$level}";

        if ($dbBooks->isNotEmpty()) {
            $formattedBooks = $dbBooks->map(function ($b) {
                return [
                    'id' => $b->id,
                    'class_level' => $b->class_level,
                    'class_name' => $b->class_name,
                    'class_name_bn' => $b->class_name_bn,
                    'group' => $b->group ?? 'general',
                    'title' => $b->title,
                    'english_title' => $b->english_title,
                    'subject' => $b->subject,
                    'cover_image' => $b->cover_image,
                    'cover_color_start' => $b->cover_color_start,
                    'cover_color_end' => $b->cover_color_end,
                    'icon' => $b->icon,
                    'author' => $b->author,
                    'edition' => $b->edition,
                    'total_pages' => $b->total_pages,
                    'has_pdf' => (bool) $b->has_pdf,
                    'pdf_url' => $b->pdf_url ?: url("/api/student/textbooks/{$b->id}/pdf"),
                    'chapters_count' => $b->chapters->count(),
                    'chapters' => $b->chapters->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'chapter_number' => $c->chapter_number,
                            'title' => $c->title,
                            'english_title' => $c->english_title,
                            'page_start' => $c->page_start,
                            'page_end' => $c->page_end,
                            'summary' => $c->summary,
                            'content' => $c->content,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();

            return [
                'class_level' => $level,
                'class_name' => $className,
                'class_name_bn' => $classNameBn,
                'group' => $group ?: 'general',
                'books' => $formattedBooks,
                'summary' => [
                    'total_books' => count($formattedBooks),
                    'class_level' => $level,
                    'class_name' => $className,
                    'class_name_bn' => $classNameBn,
                    'group' => $group ?: 'general',
                ],
            ];
        }

        return [
            'class_level' => $level,
            'class_name' => $className,
            'class_name_bn' => $classNameBn,
            'group' => $group ?: 'general',
            'books' => [],
            'summary' => [
                'total_books' => 0,
                'class_level' => $level,
                'class_name' => $className,
                'class_name_bn' => $classNameBn,
                'group' => $group ?: 'general',
            ],
        ];
    }

    /**
     * Dedicated API Endpoint for Textbooks according to student class/group or explicit parameter.
     */
    public function textbooks(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        $explicitLevel = ($request->has('class_level') && $request->query('class_level') !== '') ? (int) $request->query('class_level') : null;
        $explicitClass = $request->query('class_name');
        $group = $request->query('group') ?: $student?->group ?: $student?->academicClass?->group;

        $level = $this->resolveClassLevel($explicitClass ?: $student?->academicClass?->name, $explicitLevel);
        $data = $this->getTextbooksForLevel($level, $group);

        return response()->json([
            'success' => true,
            'student_id' => $student?->id,
            'class_level' => $data['class_level'],
            'class_name' => $data['class_name'],
            'class_name_bn' => $data['class_name_bn'],
            'group' => $group ?: 'general',
            'total_books' => count($data['books']),
            'books' => $data['books'],
        ]);
    }

    /**
     * Interactive Printable & Readable NCTB PDF View.
     */
    public function textbookPdf(Request $request, int $id)
    {
        $textbook = Textbook::with('chapters')->find($id);

        if (! $textbook) {
            $level = max(-2, min(12, $id));
            $textbook = Textbook::where('class_level', $level)->with('chapters')->first();
        }

        if (! $textbook) {
            abort(404, 'বই পাওয়া যায়নি');
        }

        $html = view('textbook_pdf', ['book' => $textbook])->render();

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Get student's school/gate entry and exit attendance history.
     */
    public function gateAttendance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (! $student) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত শিক্ষার্থী তথ্য।'], 403);
        }

        $records = GateAttendance::where('student_id', $student->id)
            ->where('institution_id', $student->institution_id)
            ->orderBy('date', 'desc')
            ->take(60)
            ->get();

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'records' => $records,
        ]);
    }

    /**
     * Get student's period/class-wise attendance history.
     */
    public function classAttendance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (! $student) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত শিক্ষার্থী তথ্য।'], 403);
        }

        $records = ClassAttendance::where('student_id', $student->id)
            ->where('institution_id', $student->institution_id)
            ->with(['academicClass', 'section', 'teacher'])
            ->orderBy('date', 'desc')
            ->take(60)
            ->get();

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'records' => $records,
        ]);
    }

    /**
     * Get daily class reports (topics covered, homework, or class cancellation reason) for student's class.
     */
    public function classReports(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (! $student) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত শিক্ষার্থী তথ্য।'], 403);
        }

        $reports = ClassReport::where('institution_id', $student->institution_id)
            ->where('academic_class_id', $student->academic_class_id)
            ->when($student->section_id, function ($q) use ($student) {
                $q->where(function ($sq) use ($student) {
                    $sq->where('section_id', $student->section_id)->orWhereNull('section_id');
                });
            })
            ->with(['teacher', 'academicClass', 'section'])
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'reports' => $reports,
        ]);
    }

    /**
     * Register or update push notification device token.
     */
    public function storeDeviceToken(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত ব্যবহারকারী।'], 401);
        }

        $validated = $request->validate([
            'device_token' => 'required|string|max:500',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        UserDeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_token' => $validated['device_token'],
            ],
            [
                'institution_id' => $user->institution_id ?? 1,
                'platform' => $validated['platform'] ?? 'android',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'ডিভাইস টোকেন সফলভাবে সংরক্ষিত হয়েছে।',
        ]);
    }

    /**
     * Get student's push notifications list.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত ব্যবহারকারী।'], 401);
        }

        $notifications = PushNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a push notification as read.
     */
    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত ব্যবহারকারী।'], 401);
        }

        $notification = PushNotification::where('user_id', $user->id)->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'নোটিফিকেশন পঠিত হিসেবে চিহ্নিত করা হয়েছে।',
        ]);
    }
}
