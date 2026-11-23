<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Institution;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guardian_can_login_via_api(): void
    {
        $response = $this->postJson('/api/guardian/login', [
            'identifier' => 'guardian@eshikkha.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'guardian']);
    }

    public function test_guardian_can_retrieve_all_four_children_with_nine_attributes(): void
    {
        $guardian = User::where('role', 'guardian')->first();

        $response = $this->actingAs($guardian, 'sanctum')->getJson('/api/guardian/children');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_children', 4)
            ->assertJsonStructure([
                'children' => [
                    '*' => [
                        'photo',
                        'name',
                        'institution' => ['name', 'type', 'code'],
                        'class_or_semester',
                        'today_attendance' => ['status'],
                        'latest_result' => ['gpa', 'letter_grade'],
                        'due_fees' => ['count', 'total_amount', 'has_dues'],
                        'new_notices_count',
                        'new_messages_count',
                    ],
                ],
            ]);
    }

    public function test_guardian_can_lookup_student_by_admission_id(): void
    {
        $guardian = User::where('role', 'guardian')->first();

        $response = $this->actingAs($guardian, 'sanctum')->postJson('/api/guardian/children/lookup', [
            'student_id' => 'DRMC-ADM-2024-001',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('student.name', 'তানভীর আহমেদ');
    }

    public function test_unauthenticated_guardian_request_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/guardian/children');

        $response->assertStatus(401);
    }

    public function test_guardian_cannot_access_unlinked_child(): void
    {
        $guardian = User::where('role', 'guardian')->first();

        // Create an unlinked student
        $institution = Institution::first();
        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $section = Section::where('academic_class_id', $class->id)->first();
        $unlinkedStudent = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'UNLINKED-001',
            'roll_number' => 88,
            'name' => 'অননুমোদিত শিক্ষার্থী',
            'gender' => 'male',
            'guardian_name' => 'অন্য অভিভাবক',
            'guardian_phone' => '01999887766',
            'status' => 'active',
        ]);

        $response = $this->actingAs($guardian, 'sanctum')->getJson("/api/guardian/children/{$unlinkedStudent->id}/dashboard");

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_guardian_can_request_link_and_verify_otp(): void
    {
        $guardian = User::where('role', 'guardian')->first();

        // Create a new unlinked student
        $institution = Institution::first();
        $class = AcademicClass::first();
        $section = Section::first();

        $newStudent = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'NEW-ADM-999',
            'roll_number' => 99,
            'name' => 'নতুন শিক্ষার্থী',
            'gender' => 'male',
            'guardian_name' => 'অভিভাবক',
            'guardian_phone' => '01799887766',
            'guardian_relation' => 'father',
            'status' => 'active',
        ]);

        // Request Link
        $reqResponse = $this->actingAs($guardian, 'sanctum')->postJson('/api/guardian/children/request-link', [
            'student_id' => $newStudent->id,
            'relationship' => 'father',
        ]);

        $reqResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify OTP
        $verifyResponse = $this->actingAs($guardian, 'sanctum')->postJson('/api/guardian/children/verify-otp', [
            'student_id' => $newStudent->id,
            'otp' => '123456',
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_guardian_can_view_child_academic_results_and_marksheet(): void
    {
        $guardian = User::where('role', 'guardian')->first();

        // Tanvir Ahmed (student_id = 2)
        $response = $this->actingAs($guardian, 'sanctum')->getJson('/api/guardian/children/2/academic-results');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'exam_name',
                    'exam_date',
                    'subject_marks',
                    'total_marks_possible',
                    'total_marks_obtained',
                    'grade',
                    'gpa',
                    'cgpa',
                    'total_marks',
                    'merit_position' => [
                        'section_rank',
                        'class_rank',
                    ],
                    'pdf_marksheet_url',
                ],
            ]);

        $pdfResponse = $this->actingAs($guardian, 'sanctum')->get('/api/guardian/children/2/marksheet/2/pdf');
        $pdfResponse->assertStatus(200)
            ->assertSee('Academic Transcript / Marksheet')
            ->assertSee('তানভীর আহমেদ');
    }
}
