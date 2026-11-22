<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Institution;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionStudentAdmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_admit_student_with_manual_class_and_section_text(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $response = $this->actingAs($admin)->post('/institution/students', [
            'name' => 'রাকিব হাসান (Rakib Hasan)',
            'class_name' => 'নবম শ্রেণি (Class 9)',
            'section_name' => 'মেঘনা (Meghna)',
            'roll_number' => 12,
            'gender' => 'male',
            'blood_group' => 'B+',
            'father_name' => 'মোঃ কামাল হোসেন',
            'mother_name' => 'রাশেদা বেগম',
            'guardian_phone' => '01712000000',
            'dob' => '2010-05-15',
            'present_village' => 'বাসা ১২, রোড ৩',
            'present_post_office' => 'ধানমন্ডি - ১২০৫',
            'present_upazila' => 'ধানমন্ডি',
            'present_district' => 'ঢাকা',
            'permanent_village' => 'গ্রাম: শিবপুর',
            'permanent_post_office' => 'শিবপুর বাজার',
            'permanent_upazila' => 'শিবপুর',
            'permanent_district' => 'নরসিংদী',
        ]);

        $response->assertSessionHas('success');

        // Verify AcademicClass was dynamically created/resolved
        $academicClass = AcademicClass::where('institution_id', $institution->id)
            ->where('name', 'নবম শ্রেণি (Class 9)')
            ->first();
        $this->assertNotNull($academicClass);
        $this->assertEquals(9, $academicClass->numeric_level);

        // Verify Section was dynamically created
        $section = Section::where('institution_id', $institution->id)
            ->where('academic_class_id', $academicClass->id)
            ->where('name', 'মেঘনা (Meghna)')
            ->first();
        $this->assertNotNull($section);

        // Verify Student was saved with all fields and fallback guardian_name
        $student = Student::where('name', 'রাকিব হাসান (Rakib Hasan)')->first();
        $this->assertNotNull($student);
        $this->assertEquals($academicClass->id, $student->academic_class_id);
        $this->assertEquals($section->id, $student->section_id);
        $this->assertEquals(12, $student->roll_number);
        $this->assertEquals('male', $student->gender);
        $this->assertEquals('B+', $student->blood_group);
        $this->assertEquals('মোঃ কামাল হোসেন', $student->father_name);
        $this->assertEquals('রাশেদা বেগম', $student->mother_name);
        $this->assertEquals('মোঃ কামাল হোসেন', $student->guardian_name);
        $this->assertEquals('বাসা ১২, রোড ৩', $student->present_village);
        $this->assertEquals('ধানমন্ডি - ১২০৫', $student->present_post_office);
        $this->assertEquals('ধানমন্ডি', $student->present_upazila);
        $this->assertEquals('ঢাকা', $student->present_district);
        $this->assertEquals('গ্রাম: শিবপুর', $student->permanent_village);
        $this->assertEquals('শিবপুর বাজার', $student->permanent_post_office);
        $this->assertEquals('শিবপুর', $student->permanent_upazila);
        $this->assertEquals('নরসিংদী', $student->permanent_district);
    }

    public function test_can_admit_student_without_section_and_without_guardian_name(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $response = $this->actingAs($admin)->post('/institution/students', [
            'name' => 'নুসরাত জাহান (Nusrat Jahan)',
            'class_name' => 'দশম শ্রেণি (Class 10)',
            'roll_number' => 5,
            'gender' => 'female',
            'blood_group' => 'O+',
            'mother_name' => 'সালমা বেগম',
            'guardian_phone' => '01812000000',
            'dob' => '2009-08-20',
            'present_village' => 'বাড়ি ৪, ব্লক সি',
            'present_post_office' => 'উত্তরা - ১২৩০',
            'present_upazila' => 'উত্তরা',
            'present_district' => 'ঢাকা',
        ]);

        $response->assertSessionHas('success');

        $student = Student::where('name', 'নুসরাত জাহান (Nusrat Jahan)')->first();
        $this->assertNotNull($student);
        $this->assertEquals('female', $student->gender);
        $this->assertEquals('O+', $student->blood_group);
        $this->assertEquals('সালমা বেগম', $student->guardian_name);
        $this->assertNotNull($student->section_id);
    }
}
