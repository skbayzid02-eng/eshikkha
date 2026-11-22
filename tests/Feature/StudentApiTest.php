<?php

namespace Tests\Feature;

use App\Models\Homework;
use App\Models\Student;
use App\Models\StudentMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_student_can_login_with_email_and_password(): void
    {
        $response = $this->postJson('/api/student/login', [
            'identifier' => 'student@drmc.edu.bd',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'token',
                'student' => [
                    'id',
                    'name',
                    'admission_number',
                    'roll_number',
                ],
            ]);
    }

    public function test_student_can_login_with_admission_number(): void
    {
        $response = $this->postJson('/api/student/login', [
            'identifier' => 'DRMC-ADM-2024-001',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('student.roll_number', 1);
    }

    public function test_student_profile_endpoint_returns_details(): void
    {
        $response = $this->getJson('/api/student/profile?student_id=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'student' => [
                    'id',
                    'name',
                    'roll_number',
                    'gender',
                    'blood_group',
                    'class' => ['name'],
                    'section' => ['name'],
                    'institution' => ['name', 'code', 'eiin'],
                    'guardian' => ['name', 'phone'],
                ],
            ]);
    }

    public function test_student_dashboard_returns_summary_for_all_components(): void
    {
        $response = $this->getJson('/api/student/dashboard?student_id=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'student',
                'attendance' => ['today_status', 'percentage'],
                'results_summary' => ['overall_percentage', 'gpa'],
                'homeworks',
                'today_routine',
                'upcoming_exams',
                'unread_messages_count',
                'recent_messages',
                'notices',
            ]);
    }

    public function test_student_attendance_history_endpoint(): void
    {
        $response = $this->getJson('/api/student/attendance?student_id=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'attendance_percentage',
                'present_count',
                'attendances',
            ]);
    }

    public function test_student_attendance_analytics_returns_all_six_components(): void
    {
        $response = $this->getJson('/api/student/attendance-analytics?student_id=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'student_id',
                    'today' => ['is_present', 'status', 'remarks'],
                    'overall' => ['percentage', 'total_days', 'present_count', 'absent_count', 'late_count', 'leave_count'],
                    'absent_days',
                    'late_days',
                    'monthly_history',
                    'leaves_history',
                ],
            ]);

        // Verify absent days and late days are populated
        $data = $response->json('data');
        $this->assertNotEmpty($data['absent_days']);
        $this->assertNotEmpty($data['late_days']);
        $this->assertNotEmpty($data['monthly_history']);
        $this->assertNotEmpty($data['leaves_history']);
    }

    public function test_student_can_apply_for_leave(): void
    {
        $response = $this->postJson('/api/student/leaves/apply?student_id=2', [
            'leave_type' => 'medical',
            'title' => 'Dental Checkup Leave',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-20',
            'reason' => 'Routine dental surgery and rest.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'leave' => [
                    'leave_type' => 'medical',
                    'days_count' => 1,
                    'status' => 'approved',
                ],
            ]);

        $this->assertDatabaseHas('student_leaves', [
            'student_id' => 2,
            'title' => 'Dental Checkup Leave',
        ]);
    }

    public function test_guardian_can_view_child_attendance_analytics(): void
    {
        $response = $this->getJson('/api/guardian/children/2/attendance-analytics');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'student_id',
                    'today',
                    'overall',
                    'absent_days',
                    'late_days',
                    'monthly_history',
                    'leaves_history',
                ],
            ]);
    }

    public function test_student_exam_results_endpoint(): void
    {
        $response = $this->getJson('/api/student/results?student_id=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'student_name',
                'class_name',
                'overall_percentage',
                'gpa',
                'terms',
            ]);
    }

    public function test_student_homework_list_and_submission(): void
    {
        $listResponse = $this->getJson('/api/student/homework?student_id=2');

        $listResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'total_count',
                'homeworks',
            ]);

        $homework = Homework::first();
        $this->assertNotNull($homework);

        $submitResponse = $this->postJson("/api/student/homework/{$homework->id}/submit");
        $submitResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'homework' => [
                    'status' => 'submitted',
                ],
            ]);

        $this->assertDatabaseHas('homeworks', [
            'id' => $homework->id,
            'status' => 'submitted',
        ]);
    }

    public function test_student_routine_and_exam_routine_endpoints(): void
    {
        $routineResponse = $this->getJson('/api/student/routine?student_id=2');
        $routineResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['grouped_routine', 'all_routines']);

        $examRoutineResponse = $this->getJson('/api/student/exam-routine?student_id=2');
        $examRoutineResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['exam_schedules']);
    }

    public function test_student_messages_and_mark_as_read(): void
    {
        $messagesResponse = $this->getJson('/api/student/messages?student_id=2');
        $messagesResponse->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['unread_count', 'messages']);

        $message = StudentMessage::where('student_id', 2)->first();
        $this->assertNotNull($message);

        $readResponse = $this->postJson("/api/student/messages/{$message->id}/read?student_id=2");
        $readResponse->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_messages', [
            'id' => $message->id,
            'is_read' => true,
        ]);
    }

    public function test_portal_data_endpoint_returns_bundled_data(): void
    {
        $response = $this->getJson('/api/student/portal-data?student_id=2');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'profile',
                'dashboard',
                'results',
                'homework',
                'routine',
                'exam_routine',
                'messages',
                'notices',
                'attendance',
            ]);
    }

    public function test_student_academic_results_endpoint_returns_all_ten_required_items(): void
    {
        $response = $this->getJson('/api/student/academic-results?student_id=2');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'exam_name', // 1. পরীক্ষার নাম
                    'exam_date', // 2. পরীক্ষার তারিখ
                    'subject_marks' => [ // 3. প্রতিটি বিষয়ের নম্বর
                        '*' => [
                            'subject',
                            'credits',
                            'total_marks',
                            'internal_marks',
                            'theory_marks',
                            'practical_marks',
                            'obtained_marks',
                            'grade',
                            'grade_point',
                        ],
                    ],
                    'total_marks_possible', // 4. পূর্ণমান
                    'total_marks_obtained', // 5. প্রাপ্ত নম্বর
                    'grade', // 6. গ্রেড
                    'gpa', // 7. GPA বা CGPA
                    'cgpa',
                    'total_marks', // 8. মোট নম্বর
                    'merit_position' => [ // 9. মেধাক্রম
                        'section_rank',
                        'class_rank',
                        'total_students_section',
                        'total_students_class',
                    ],
                    'pdf_marksheet_url', // 10. PDF মার্কশিট
                ],
            ]);

        // Check specific seeded values for Tanvir Ahmed (Child 2)
        $response->assertJsonPath('data.merit_position.section_rank', 1)
            ->assertJsonPath('data.merit_position.class_rank', 3);
    }

    public function test_student_can_view_marksheet_pdf(): void
    {
        $response = $this->get('/api/student/marksheet/2/pdf?student_id=2');

        $response->assertStatus(200)
            ->assertSee('Academic Transcript / Marksheet')
            ->assertSee('তানভীর আহমেদ')
            ->assertSee('Section Rank: 1')
            ->assertSee('Mathematics III');
    }

    public function test_student_navigation_hub_returns_all_five_buttons_data(): void
    {
        $response = $this->getJson('/api/student/navigation-hub?student_id=2');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'buttons' => [
                    'home' => ['id', 'title', 'bangla_title', 'icon', 'summary'],
                    'study' => ['id', 'title', 'bangla_title', 'icon', 'summary'],
                    'attendance' => ['id', 'title', 'bangla_title', 'icon', 'summary'],
                    'results' => ['id', 'title', 'bangla_title', 'icon', 'summary'],
                    'profile' => ['id', 'title', 'bangla_title', 'icon', 'summary'],
                ],
                'home_data',
                'study_data',
                'attendance_data',
                'results_data',
                'profile_data',
            ]);

        $response->assertJsonPath('buttons.home.title', 'Home')
            ->assertJsonPath('buttons.study.title', 'Study')
            ->assertJsonPath('buttons.attendance.title', 'Attendance')
            ->assertJsonPath('buttons.results.title', 'Results')
            ->assertJsonPath('buttons.profile.title', 'Profile');
    }

    public function test_student_study_endpoint_returns_subjects_and_curriculum(): void
    {
        $response = $this->getJson('/api/student/study?student_id=2');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'summary' => [
                    'total_subjects',
                    'due_homework_count',
                    'practicals_count',
                    'today_classes_count',
                ],
                'subjects' => [
                    '*' => [
                        'id',
                        'name',
                        'bangla_name',
                        'code',
                        'chapters_count',
                        'completed_chapters',
                        'progress',
                        'teacher_name',
                    ],
                ],
                'homeworks',
                'practicals',
                'routines',
            ]);
    }

    public function test_student_profile_hub_returns_all_ten_profile_sections(): void
    {
        $response = $this->getJson('/api/student/profile-hub?student_id=2');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'personal_info' => [
                    'id',
                    'name',
                    'bangla_name',
                    'father_name',
                    'mother_name',
                    'admission_number',
                    'roll_number',
                    'gender',
                    'dob',
                    'blood_group',
                    'religion',
                    'address',
                    'phone',
                    'email',
                    'emergency_contact',
                    'status',
                    'photo',
                ],
                'student_id' => [
                    'card_number',
                    'admission_number',
                    'roll_number',
                    'class_name',
                    'section_name',
                    'session',
                    'barcode',
                    'qr_data',
                    'valid_until',
                ],
                'institution_info' => [
                    'name',
                    'name_en',
                    'eiin',
                    'code',
                    'type',
                    'campus',
                    'principal_name',
                    'phone',
                    'email',
                    'website',
                ],
                'guardian_info' => [
                    'name',
                    'mother_name',
                    'relation',
                    'phone',
                    'occupation',
                    'address',
                ],
                'fees_and_payments' => [
                    'total_payable',
                    'total_paid',
                    'due_amount',
                    'status',
                    'recent_history',
                ],
                'notifications' => [
                    'unread_count',
                    'items',
                    'preferences',
                ],
                'messages' => [
                    'unread_count',
                    'items',
                ],
                'settings' => [
                    'current_language',
                    'languages',
                    'theme',
                    'biometric_auth',
                ],
                'help_and_support' => [
                    'hotline',
                    'support_phone',
                    'support_email',
                    'website',
                    'faqs',
                ],
                'logout' => [
                    'endpoint',
                    'confirmation_title',
                    'confirmation_message',
                ],
            ]);
    }

    public function test_student_logout_endpoint(): void
    {
        $response = $this->postJson('/api/student/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_student_can_register_for_class_1_and_receives_token(): void
    {
        $response = $this->postJson('/api/student/register', [
            'name' => 'Aaryan Hasan',
            'email' => 'aaryan.class1@eshikkha.edu.bd',
            'password' => 'secret123',
            'roll_number' => '01',
            'class_name' => 'Class 1',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('student.name', 'Aaryan Hasan')
            ->assertJsonPath('student.roll_number', 1)
            ->assertJsonStructure([
                'token',
                'student' => [
                    'id',
                    'name',
                    'roll_number',
                    'academic_class' => [
                        'name',
                        'numeric_level',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'aaryan.class1@eshikkha.edu.bd',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('students', [
            'name' => 'Aaryan Hasan',
            'roll_number' => '01',
        ]);
    }

    public function test_student_can_login_with_registered_email_and_password_for_class_1(): void
    {
        // 1. Register Class 1 student
        $this->postJson('/api/student/register', [
            'name' => 'Aaryan Hasan',
            'email' => 'aaryan.login@eshikkha.edu.bd',
            'password' => 'myClass1Pass',
            'roll_number' => '01',
            'class_name' => 'Class 1',
        ])->assertStatus(201);

        // 2. Login with email and password
        $loginResponse = $this->postJson('/api/student/login', [
            'email' => 'aaryan.login@eshikkha.edu.bd',
            'password' => 'myClass1Pass',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('student.name', 'Aaryan Hasan')
            ->assertJsonPath('student.roll_number', 1);

        $studentId = $loginResponse->json('student.id');

        // 3. Verify Home / Portal data reflects Class 1 student
        $portalResponse = $this->getJson("/api/student/portal-data?student_id={$studentId}");
        $portalResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('profile.name', 'Aaryan Hasan')
            ->assertJsonPath('profile.roll_number', 1);
    }

    public function test_available_classes_returns_class_1_through_class_12(): void
    {
        $response = $this->getJson('/api/student/available-classes');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('classes.0.id', 'Class 1')
            ->assertJsonPath('classes.0.level', 1);
    }

    public function test_class_1_student_receives_class_1_curriculum_in_study_hub(): void
    {
        $regResponse = $this->postJson('/api/student/register', [
            'name' => 'Muntaha Class 1',
            'email' => 'muntaha.class1@eshikkha.edu.bd',
            'password' => 'pass12345',
            'roll_number' => '05',
            'class_name' => 'Class 1',
        ])->assertStatus(201);

        $studentId = $regResponse->json('student.id');

        $studyResponse = $this->getJson("/api/student/study?student_id={$studentId}");
        $studyResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('subjects.0.name', 'Amar Bangla Boi')
            ->assertJsonPath('subjects.1.name', 'English for Today')
            ->assertJsonPath('subjects.2.name', 'Elementary Mathematics');
    }

    public function test_available_institutions_returns_19_types_with_full_management(): void
    {
        $response = $this->getJson('/api/student/available-institutions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'types_count' => 19,
                'management' => 'পূর্ণাঙ্গ',
            ])
            ->assertJsonPath('types.0.id', 'govt_primary_school')
            ->assertJsonPath('types.0.name_bn', 'সরকারি প্রাথমিক বিদ্যালয়')
            ->assertJsonPath('types.0.management', 'পূর্ণাঙ্গ');

        $typeIds = collect($response->json('types'))->pluck('id')->all();
        $this->assertContains('govt_primary_school', $typeIds);
        $this->assertContains('private_primary_school', $typeIds);
        $this->assertContains('govt_high_school', $typeIds);
        $this->assertContains('private_high_school', $typeIds);
        $this->assertContains('kindergarten', $typeIds);
        $this->assertContains('preschool_nursery', $typeIds);
        $this->assertContains('govt_college', $typeIds);
        $this->assertContains('private_college', $typeIds);
        $this->assertContains('madrasah', $typeIds);
        $this->assertContains('alia_madrasah', $typeIds);
        $this->assertContains('qawmi_madrasah', $typeIds);
        $this->assertContains('university', $typeIds);
        $this->assertContains('medical_college', $typeIds);
        $this->assertContains('engineering_college', $typeIds);
        $this->assertContains('polytechnic', $typeIds);
        $this->assertContains('vocational_training', $typeIds);
        $this->assertContains('coaching_center', $typeIds);
        $this->assertContains('english_medium', $typeIds);
        $this->assertContains('religious_other', $typeIds);
    }

    public function test_student_can_register_under_any_of_19_institution_types(): void
    {
        $response = $this->postJson('/api/student/register', [
            'name' => 'Fahim Primary Student',
            'email' => 'fahim.primary@eshikkha.edu.bd',
            'password' => 'secret123',
            'roll_number' => '07',
            'class_name' => 'Class 1 (প্রথম শ্রেণি)',
            'institution_type' => 'govt_primary_school',
            'institution_name' => 'ধানমন্ডি মডেল সরকারি প্রাথমিক বিদ্যালয়',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('student.name', 'Fahim Primary Student')
            ->assertJsonPath('student.roll_number', 7);

        $student = Student::with('institution')->find($response->json('student.id'));
        $this->assertNotNull($student);
        $this->assertEquals('ধানমন্ডি মডেল সরকারি প্রাথমিক বিদ্যালয়', $student->institution->name);
        $this->assertEquals('govt_primary_school', $student->institution->type);
        $this->assertEquals('পূর্ণাঙ্গ', $student->institution->management);
    }

    public function test_student_textbooks_endpoint_returns_exact_book_counts_per_class(): void
    {
        // 1. Class 1 (প্রথম শ্রেণি) - ৩টি
        $res1 = $this->getJson('/api/student/textbooks?class_level=1');
        $res1->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_books', 3);
        $this->assertCount(3, $res1->json('books'));
        $this->assertEquals('আমার বাংলা বই', $res1->json('books.0.title'));

        // 2. Class 2 (দ্বিতীয় শ্রেণি) - ৩টি
        $res2 = $this->getJson('/api/student/textbooks?class_level=2');
        $res2->assertStatus(200)
            ->assertJsonPath('total_books', 3);
        $this->assertCount(3, $res2->json('books'));

        // 3. Class 3 (তৃতীয় শ্রেণি) - ৯টি
        $res3 = $this->getJson('/api/student/textbooks?class_level=3');
        $res3->assertStatus(200)
            ->assertJsonPath('total_books', 9);
        $this->assertCount(9, $res3->json('books'));

        // 4. Class 4 (চতুর্থ শ্রেণি) - ৯টি
        $res4 = $this->getJson('/api/student/textbooks?class_level=4');
        $res4->assertStatus(200)
            ->assertJsonPath('total_books', 9);
        $this->assertCount(9, $res4->json('books'));

        // 5. Class 5 (পঞ্চম শ্রেণি) - ৯টি
        $res5 = $this->getJson('/api/student/textbooks?class_level=5');
        $res5->assertStatus(200)
            ->assertJsonPath('total_books', 9);
        $this->assertCount(9, $res5->json('books'));

        // 6. Class 6 (ষষ্ঠ শ্রেণি) - ২৩টি
        $res6 = $this->getJson('/api/student/textbooks?class_level=6');
        $res6->assertStatus(200)
            ->assertJsonPath('total_books', 23);
        $this->assertCount(23, $res6->json('books'));

        // 7. Class 7 (সপ্তম শ্রেণি) - ২২টি
        $res7 = $this->getJson('/api/student/textbooks?class_level=7');
        $res7->assertStatus(200)
            ->assertJsonPath('total_books', 22);
        $this->assertCount(22, $res7->json('books'));

        // 8. Class 8 (অষ্টম শ্রেণি) - ২২টি
        $res8 = $this->getJson('/api/student/textbooks?class_level=8');
        $res8->assertStatus(200)
            ->assertJsonPath('total_books', 22);
        $this->assertCount(22, $res8->json('books'));

        // 9. Class 9 (নবম শ্রেণি বিজ্ঞান বিভাগ) - ১৮টি (১৪ সাধারণ + ৪ বিজ্ঞান)
        $res9 = $this->getJson('/api/student/textbooks?class_level=9&group=science');
        $res9->assertStatus(200)
            ->assertJsonPath('total_books', 18);
        $this->assertCount(18, $res9->json('books'));

        // 10. Class 10 (দশম শ্রেণি বিজ্ঞান বিভাগ) - ১৮টি (১৪ সাধারণ + ৪ বিজ্ঞান)
        $res10 = $this->getJson('/api/student/textbooks?class_level=10&group=science');
        $res10->assertStatus(200)
            ->assertJsonPath('total_books', 18);
        $this->assertCount(18, $res10->json('books'));
    }

    public function test_textbook_contains_chapters_cover_colors_and_pdf_view(): void
    {
        $response = $this->getJson('/api/student/textbooks?class_level=1');
        $response->assertStatus(200);

        $firstBook = $response->json('books.0');
        $this->assertNotEmpty($firstBook['title']);
        $this->assertNotEmpty($firstBook['cover_color_start']);
        $this->assertTrue($firstBook['has_pdf']);
        $this->assertNotEmpty($firstBook['pdf_url']);
        $this->assertGreaterThanOrEqual(1, $firstBook['chapters_count']);
        $this->assertNotEmpty($firstBook['chapters'][0]['title']);

        // Verify PDF Viewer Endpoint
        $pdfResponse = $this->get('/api/student/textbooks/'.$firstBook['id'].'/pdf');
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee($firstBook['title']);
        $pdfResponse->assertSee('জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড');
    }
}
