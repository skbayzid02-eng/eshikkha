<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Attendance;
use App\Models\Institution;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionAttendanceFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_view_institution_attendance_page_with_empty_or_null_query_params(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        // Simulating tricky/null/undefined query parameters from frontend or URL manipulations
        $response = $this->actingAs($admin)->get('/institution/attendance?class_id=null&section_id=undefined&date=invalid-date');

        $response->assertStatus(200);
        $response->assertViewHas('classes');
        $response->assertViewHas('students');
        $response->assertViewHas('selectedDate', Carbon::today()->toDateString());
    }

    public function test_filter_attendance_by_valid_class_and_section_without_null_errors(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class1 = AcademicClass::create([
            'institution_id' => $institution->id,
            'name' => 'Class Alpha',
            'numeric_level' => 9,
        ]);
        $section1 = Section::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class1->id,
            'name' => 'Section A',
        ]);
        $student1 = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class1->id,
            'section_id' => $section1->id,
            'admission_number' => 'ADM-ALPHA-01',
            'roll_number' => 101,
            'name' => 'Alpha Student Rahim',
            'guardian_name' => 'Guardian Rahim',
            'guardian_phone' => '01700000001',
        ]);

        $class2 = AcademicClass::create([
            'institution_id' => $institution->id,
            'name' => 'Class Beta',
            'numeric_level' => 10,
        ]);
        $section2 = Section::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class2->id,
            'name' => 'Section B',
        ]);
        $student2 = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class2->id,
            'section_id' => $section2->id,
            'admission_number' => 'ADM-BETA-02',
            'roll_number' => 102,
            'name' => 'Beta Student Karim',
            'guardian_name' => 'Guardian Karim',
            'guardian_phone' => '01700000002',
        ]);

        $response = $this->actingAs($admin)->get("/institution/attendance?class_id={$class1->id}&section_id={$section1->id}");

        $response->assertStatus(200);
        $response->assertSee('Alpha Student Rahim');
        $response->assertDontSee('Beta Student Karim');
    }

    public function test_can_store_attendance_records(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $section = $class->sections()->first() ?? Section::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'name' => 'Section Default',
        ]);
        $student = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'ADM-TEST-55',
            'roll_number' => 55,
            'name' => 'Test Student Attendance',
            'guardian_name' => 'Guardian Test',
            'guardian_phone' => '01700000099',
        ]);

        $today = Carbon::today()->toDateString();

        $response = $this->actingAs($admin)->post('/institution/attendance', [
            'date' => $today,
            'attendances' => [
                $student->id => 'present',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'institution_id' => $institution->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);
        $attendance = Attendance::where('student_id', $student->id)->latest()->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(Carbon::today()->toDateString(), Carbon::parse($attendance->date)->toDateString());
    }
}
