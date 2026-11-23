<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\GuardianStudent;
use App\Models\Homework;
use App\Models\Institution;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Point 1: Fallback system disabled & no data sharing.
     */
    public function test_fallback_system_is_completely_disabled_for_student_and_guardian(): void
    {
        // 1. Unauthenticated request must return 401
        $response = $this->getJson('/api/student/dashboard');
        $response->assertStatus(401);

        $guardianResponse = $this->getJson('/api/guardian/children');
        $guardianResponse->assertStatus(401);

        // 2. Unauthenticated request with student_id param must NOT fallback to demo data
        $responseWithParam = $this->getJson('/api/student/profile?student_id=2');
        $responseWithParam->assertStatus(401);

        // 3. Guardian cannot see children of other guardians
        $guardian = User::where('role', 'guardian')->first();
        $otherInst = Institution::where('id', '!=', 1)->first();
        $otherClass = AcademicClass::where('institution_id', $otherInst->id)->first();
        $otherSection = Section::where('academic_class_id', $otherClass->id)->first();

        $unlinkedStudent = Student::create([
            'institution_id' => $otherInst->id,
            'academic_class_id' => $otherClass->id,
            'section_id' => $otherSection->id,
            'admission_number' => 'UNLINKED-999',
            'roll_number' => 77,
            'name' => 'অন্য পরিবারের সন্তান',
            'gender' => 'male',
            'guardian_name' => 'অন্য অভিভাবক',
            'guardian_phone' => '01999999999',
            'status' => 'active',
        ]);

        $childDash = $this->actingAs($guardian, 'sanctum')->getJson("/api/guardian/children/{$unlinkedStudent->id}/dashboard");
        $childDash->assertStatus(403);
    }

    /**
     * Point 2: institution_id on all records & multi-tenancy isolation.
     */
    public function test_institution_id_isolation_across_all_records(): void
    {
        $teacherUser = User::where('role', 'teacher')->first();
        $inst1 = Institution::find($teacherUser->institution_id);
        $inst2 = Institution::where('id', '!=', $inst1->id)->first();

        $guardian = User::where('role', 'guardian')->first();
        $link = GuardianStudent::where('guardian_user_id', $guardian->id)->first();

        $this->assertNotNull($link->institution_id);

        $this->actingAs($teacherUser, 'sanctum');

        $studentsResponse = $this->getJson('/api/teacher/students');
        $studentsResponse->assertStatus(200);

        foreach ($studentsResponse->json('students') as $st) {
            $this->assertEquals($inst1->id, $st['institution_id']);
        }
    }

    /**
     * Point 3: Academic years separate table & student enrollments without deleting history.
     */
    public function test_can_manage_academic_years_and_enrollments_without_deleting_history(): void
    {
        $institution = Institution::first();
        $class = AcademicClass::where('institution_id', $institution->id)->first();
        $section = Section::where('academic_class_id', $class->id)->first();
        $student = Student::create([
            'institution_id' => $institution->id,
            'academic_class_id' => $class->id,
            'section_id' => $section->id,
            'admission_number' => 'HIST-ADM-999',
            'roll_number' => 88,
            'name' => 'ইতিহাস শিক্ষার্থী',
            'gender' => 'male',
            'guardian_name' => 'অভিভাবক',
            'guardian_phone' => '01711223344',
            'status' => 'active',
        ]);

        // 1. Previous year 2025 exists
        $year2025 = AcademicYear::firstOrCreate(
            ['institution_id' => $institution->id, 'name' => '2025'],
            ['start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'is_current' => false, 'status' => 'completed']
        );

        StudentEnrollment::create([
            'institution_id' => $institution->id,
            'academic_year_id' => $year2025->id,
            'student_id' => $student->id,
            'academic_class_id' => $class->id,
            'roll_number' => 10,
            'enrollment_date' => '2025-01-01',
            'status' => 'promoted',
        ]);

        // 2. Start new year 2026 without deleting 2025
        $year2026 = AcademicYear::firstOrCreate(
            ['institution_id' => $institution->id, 'name' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true, 'status' => 'active']
        );

        StudentEnrollment::create([
            'institution_id' => $institution->id,
            'academic_year_id' => $year2026->id,
            'student_id' => $student->id,
            'academic_class_id' => $class->id,
            'roll_number' => 5,
            'enrollment_date' => '2026-01-01',
            'status' => 'enrolled',
        ]);

        // Verify both enrollments exist in the database for the same student
        $enrollments = StudentEnrollment::where('student_id', $student->id)->get();
        $this->assertCount(2, $enrollments);
        $this->assertTrue($enrollments->contains('academic_year_id', $year2025->id));
        $this->assertTrue($enrollments->contains('academic_year_id', $year2026->id));
    }

    /**
     * Point 4: Gate attendance and class attendance stored in separate tables.
     */
    public function test_gate_and_class_attendances_are_stored_in_separate_tables(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = Student::where('institution_id', $teacher->institution_id)->first();

        // 1. Submit Gate Attendance
        $gateResponse = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/gate-attendance', [
            'date' => '2026-11-22',
            'attendances' => [
                [
                    'student_id' => $student->id,
                    'status' => 'present',
                    'entry_time' => '07:45:00',
                    'gate_number' => 'Gate 1',
                ],
            ],
        ]);

        $gateResponse->assertStatus(200);
        $this->assertDatabaseHas('gate_attendances', [
            'institution_id' => $teacher->institution_id,
            'student_id' => $student->id,
            'date' => '2026-11-22',
            'status' => 'present',
            'gate_number' => 'Gate 1',
        ]);

        // 2. Submit Class Attendance
        $classResponse = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/class-attendance', [
            'academic_class_id' => $student->academic_class_id,
            'period_number' => 2,
            'subject_name' => 'ইংরেজি ১ম পত্র',
            'date' => '2026-11-22',
            'attendances' => [
                [
                    'student_id' => $student->id,
                    'status' => 'present',
                ],
            ],
        ]);

        $classResponse->assertStatus(200);
        $this->assertDatabaseHas('class_attendances', [
            'institution_id' => $teacher->institution_id,
            'student_id' => $student->id,
            'academic_class_id' => $student->academic_class_id,
            'period_number' => 2,
            'subject_name' => 'ইংরেজি ১ম পত্র',
            'date' => '2026-11-22',
            'status' => 'present',
        ]);
    }

    /**
     * Point 5: Teacher daily class report, homework, and class cancellation reason.
     */
    public function test_teacher_can_submit_class_report_homework_and_cancellation_reason(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $class = AcademicClass::where('institution_id', $teacher->institution_id)->first();

        // 1. Submit Normal Class Report
        $reportResponse = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/class-reports', [
            'academic_class_id' => $class->id,
            'subject_name' => 'পদার্থবিজ্ঞান',
            'period_number' => 3,
            'date' => '2026-11-22',
            'class_conducted' => true,
            'topics_covered' => 'গতি ও বল সূত্র আলোচনা',
            'homework_assigned' => 'অনুশীলনী ৫ সমাধান',
            'present_count' => 38,
            'absent_count' => 2,
        ]);

        $reportResponse->assertStatus(200);
        $this->assertDatabaseHas('class_reports', [
            'institution_id' => $teacher->institution_id,
            'academic_class_id' => $class->id,
            'subject_name' => 'পদার্থবিজ্ঞান',
            'class_conducted' => true,
            'topics_covered' => 'গতি ও বল সূত্র আলোচনা',
        ]);

        // 2. Submit Class Cancellation with Reason
        $cancelResponse = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/class-reports', [
            'academic_class_id' => $class->id,
            'subject_name' => 'রসায়ন ল্যাব',
            'period_number' => 4,
            'date' => '2026-11-22',
            'class_conducted' => false,
            'cancellation_reason' => 'শিক্ষকের হঠাৎ অসুস্থতা ও ল্যাব পরিষ্কার কার্যক্রমের কারণে',
        ]);

        $cancelResponse->assertStatus(200);
        $this->assertDatabaseHas('class_reports', [
            'institution_id' => $teacher->institution_id,
            'academic_class_id' => $class->id,
            'subject_name' => 'রসায়ন ল্যাব',
            'class_conducted' => false,
            'cancellation_reason' => 'শিক্ষকের হঠাৎ অসুস্থতা ও ল্যাব পরিষ্কার কার্যক্রমের কারণে',
        ]);

        // 3. Submit Homework
        $hwResponse = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/homework', [
            'academic_class_id' => $class->id,
            'subject' => 'পদার্থবিজ্ঞান',
            'title' => 'নিউটনের গতির সূত্রাবলী অ্যাসাইনমেন্ট',
            'description' => '১ম, ২য় ও ৩য় সূত্রের বাস্তব উদাহরণসহ প্রতিবেদন লিখ।',
            'due_date' => '2026-11-28',
            'total_marks' => 20,
        ]);

        $hwResponse->assertStatus(200);
        $this->assertDatabaseHas('homeworks', [
            'institution_id' => $teacher->institution_id,
            'academic_class_id' => $class->id,
            'title' => 'নিউটনের গতির সূত্রাবলী অ্যাসাইনমেন্ট',
        ]);
    }

    /**
     * Point 6 & 7: Student and Guardian receive Push Notifications & strict permissions.
     */
    public function test_push_notifications_are_created_for_authorized_users(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = Student::where('institution_id', $teacher->institution_id)->first();
        $guardian = User::where('role', 'guardian')->first();

        // Ensure link exists
        GuardianStudent::firstOrCreate([
            'institution_id' => $student->institution_id,
            'guardian_user_id' => $guardian->id,
            'student_id' => $student->id,
        ], [
            'relationship' => 'father',
            'status' => 'approved',
            'verified_at' => now(),
        ]);

        // When gate attendance is marked, notification is auto-dispatched
        $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/gate-attendance', [
            'date' => '2026-11-22',
            'attendances' => [
                [
                    'student_id' => $student->id,
                    'status' => 'present',
                    'entry_time' => '07:50:00',
                ],
            ],
        ]);

        $this->assertDatabaseHas('push_notifications', [
            'institution_id' => $student->institution_id,
            'user_id' => $guardian->id,
            'type' => 'gate_entry',
        ]);

        // Guardian can view their notifications
        $guardianNotifs = $this->actingAs($guardian, 'sanctum')->getJson('/api/guardian/notifications');
        $guardianNotifs->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
