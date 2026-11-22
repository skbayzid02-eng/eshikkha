<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentExamSummary;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionExamMarksheetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_view_institution_exams_and_marksheet_page(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $response = $this->actingAs($admin)->get('/institution/exams');

        $response->assertStatus(200);
        $response->assertSee('পরীক্ষা ও ফলাফল মার্কশিট');
        $response->assertSee('শ্রেণি নির্বাচন ও ডায়নামিক ক্লাস বাটন');
        $response->assertSee('রোল নম্বর অথবা শিক্ষার্থীর নাম দিয়ে খুঁজুন');
    }

    public function test_displays_student_marks_grade_and_gpa_in_class(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $student = Student::where('institution_id', $institution->id)->where('academic_class_id', $class->id)->first()
            ?? Student::factory()->create([
                'institution_id' => $institution->id,
                'academic_class_id' => $class->id,
                'name' => 'আব্দুর রহিম',
                'roll_number' => 10,
            ]);

        $exam = Exam::where('institution_id', $institution->id)->where('academic_class_id', $class->id)->first()
            ?? Exam::create([
                'institution_id' => $institution->id,
                'academic_class_id' => $class->id,
                'title' => 'বার্ষিক পরীক্ষা ২০২৬',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'session_year' => '2026',
            ]);

        ExamResult::create([
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_name' => 'পদার্থবিজ্ঞান',
            'total_marks' => 100,
            'obtained_marks' => 85,
            'grade' => 'A+',
            'grade_point' => 5.0,
        ]);

        $response = $this->actingAs($admin)->get('/institution/exams');

        $response->assertStatus(200);
        $response->assertSee($student->name);
        $response->assertSee($class->name);
    }

    public function test_can_store_new_exam_and_exam_result(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $student = Student::where('institution_id', $institution->id)->where('academic_class_id', $class->id)->first();

        // 1. Create Exam
        $res1 = $this->actingAs($admin)->post('/institution/exams', [
            'academic_class_id' => $class->id,
            'title' => 'নির্বাচনী মূল্যায়ন ২০২৬',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-27',
            'session_year' => '2026',
        ]);
        $res1->assertSessionHas('success');

        $this->assertDatabaseHas('exams', [
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'title' => 'নির্বাচনী মূল্যায়ন ২০২৬',
        ]);

        $exam = Exam::where('institution_id', $institution->id)->where('title', 'নির্বাচনী মূল্যায়ন ২০২৬')->first();

        // 2. Create Exam Result
        $res2 = $this->actingAs($admin)->post('/institution/exams/results', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_name' => 'উচ্চতর গণিত',
            'total_marks' => 100,
            'obtained_marks' => 92,
        ]);
        $res2->assertSessionHas('success');

        $this->assertDatabaseHas('exam_results', [
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_name' => 'উচ্চতর গণিত',
            'obtained_marks' => 92,
            'grade' => 'A+',
            'grade_point' => 5.0,
        ]);
    }

    public function test_students_are_ordered_by_roll_number_asc(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class = AcademicClass::create([
            'institution_id' => $institution->id,
            'name' => 'Class Nine Order Test',
            'numeric_level' => 9,
        ]);
        $section = $class->sections()->create([
            'institution_id' => $institution->id,
            'name' => 'Section A',
        ]);

        // Create 3 students out of order
        Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'ADM-ORD-45',
            'roll_number' => 45,
            'name' => 'Student Forty Five',
            'guardian_name' => 'Guardian 45',
            'guardian_phone' => '01700000045',
        ]);
        Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'ADM-ORD-02',
            'roll_number' => 2,
            'name' => 'Student Two',
            'guardian_name' => 'Guardian 02',
            'guardian_phone' => '01700000002',
        ]);
        Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'ADM-ORD-15',
            'roll_number' => 15,
            'name' => 'Student Fifteen',
            'guardian_name' => 'Guardian 15',
            'guardian_phone' => '01700000015',
        ]);

        $response = $this->actingAs($admin)->get('/institution/exams');
        $response->assertStatus(200);

        $classDataMap = $response->viewData('classResultsData');
        $this->assertArrayHasKey('Class Nine Order Test', $classDataMap);

        $students = $classDataMap['Class Nine Order Test']['students'];
        $this->assertCount(3, $students);
        $this->assertEquals(2, $students[0]['roll_number']);
        $this->assertEquals(15, $students[1]['roll_number']);
        $this->assertEquals(45, $students[2]['roll_number']);
    }

    public function test_can_store_dynamic_multi_row_marksheet_with_relational_architecture(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $section = $class->sections()->first();

        $student = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'ADM-DYN-01',
            'roll_number' => 1,
            'name' => 'Dynamic Student Jamal',
            'guardian_name' => 'Guardian Jamal',
            'guardian_phone' => '01700000111',
        ]);

        $examType = ExamType::create([
            'institution_id' => $institution->id,
            'name' => 'অর্ধবার্ষিক পরীক্ষা (Half Yearly)',
            'code' => 'half_yearly',
            'weight_percentage' => 100,
        ]);

        $exam = Exam::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'exam_type_id' => $examType->id,
            'title' => 'অর্ধবার্ষিক মূল্যায়ন ২০২৬',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'session_year' => '2026',
        ]);

        $subject1 = Subject::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'name' => 'বাংলা ১ম পত্র',
            'code' => '101',
            'full_marks' => 100,
            'pass_marks' => 33,
        ]);

        $response = $this->actingAs($admin)->post('/institution/exams/dynamic-marksheet', [
            'exam_id' => $exam->id,
            'exam_type_id' => $examType->id,
            'student_id' => $student->id,
            'rows' => [
                [
                    'subject_id' => $subject1->id,
                    'subject_name' => 'বাংলা ১ম পত্র',
                    'total_marks' => 100,
                    'obtained_marks' => 88,
                ],
                [
                    'subject_id' => null,
                    'subject_name' => 'সাধারণ গণিত',
                    'total_marks' => 100,
                    'obtained_marks' => 95,
                ],
                [
                    'subject_id' => null,
                    'subject_name' => 'ইংরেজি ১ম পত্র',
                    'total_marks' => 100,
                    'obtained_marks' => 74,
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        // Verify relational database records in exam_results
        $this->assertDatabaseHas('exam_results', [
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'exam_type_id' => $examType->id,
            'student_id' => $student->id,
            'subject_name' => 'বাংলা ১ম পত্র',
            'obtained_marks' => 88,
            'grade' => 'A+',
            'grade_point' => 5.0,
        ]);

        $this->assertDatabaseHas('exam_results', [
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'exam_type_id' => $examType->id,
            'student_id' => $student->id,
            'subject_name' => 'সাধারণ গণিত',
            'obtained_marks' => 95,
            'grade' => 'A+',
            'grade_point' => 5.0,
        ]);

        // Verify StudentExamSummary was computed and stored automatically
        $this->assertDatabaseHas('student_exam_summaries', [
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'total_marks_possible' => 300,
            'total_marks_obtained' => 257,
            'status' => 'passed',
        ]);

        $summary = StudentExamSummary::where('student_id', $student->id)->where('exam_id', $exam->id)->first();
        $this->assertNotNull($summary);
        $this->assertGreaterThanOrEqual(4.5, $summary->gpa);
    }

    public function test_get_student_marksheet_fetches_all_associated_subject_records(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $student = Student::where('institution_id', $institution->id)->where('academic_class_id', $class->id)->first()
            ?? Student::create([
                'institution_id' => $institution->id,
                'academic_class_id' => $class->id,
                'name' => 'জামাল উদ্দিন',
                'admission_number' => 'ADM-'.rand(1000, 9999),
                'roll_number' => 2,
                'guardian_name' => 'অভিভাবক',
                'guardian_phone' => '01700000000',
            ]);

        $exam = Exam::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'title' => 'অর্ধবার্ষিক মূল্যায়ন ২০২৬',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'session_year' => '2026',
        ]);

        $sub1 = ExamResult::create([
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_name' => 'বাংলা ১ম পত্র',
            'total_marks' => 100,
            'obtained_marks' => 85,
            'grade' => 'A+',
            'grade_point' => 5.0,
        ]);

        $sub2 = ExamResult::create([
            'institution_id' => $institution->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_name' => 'ইংরেজি ১ম পত্র',
            'total_marks' => 100,
            'obtained_marks' => 78,
            'grade' => 'A',
            'grade_point' => 4.0,
        ]);

        $response = $this->actingAs($admin)->getJson("/institution/exams/students/{$student->id}/marksheet?exam_id={$exam->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'roll_number' => $student->roll_number,
        ]);

        $records = $response->json('records');
        $this->assertCount(2, $records);
        $this->assertEquals('বাংলা ১ম পত্র', $records[0]['subject_name']);
        $this->assertEquals(85, $records[0]['obtained_marks']);
        $this->assertEquals('A+', $records[0]['grade']);
        $this->assertEquals('ইংরেজি ১ম পত্র', $records[1]['subject_name']);
        $this->assertEquals(78, $records[1]['obtained_marks']);
    }
}
