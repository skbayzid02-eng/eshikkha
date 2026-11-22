<?php

namespace Tests\Feature;

use App\Models\ClassRoutine;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionRoutineManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_view_institution_routines_page(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $response = $this->actingAs($admin)->get('/institution/routines');

        $response->assertStatus(200);
        $response->assertSee('শিক্ষকগণ ও স্টুডেন্টগণ এর সাপ্তাহিক ক্লাস রুটিন');
        $response->assertSee('শিক্ষক রুটিন');
        $response->assertSee('স্টুডেন্ট রুটিন');
        $response->assertSee('স্টুডেন্ট ক্লাস রুটিন');
        $response->assertSee('শিক্ষক ক্লাস রুটিন');
        $response->assertSee('শনিবার');
        $response->assertSee('বৃহস্পতিবার');
    }

    public function test_can_store_routine_using_simple_text_fields(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $response = $this->actingAs($admin)->post('/institution/routines', [
            'day' => 'Saturday',
            'period_number' => 2,
            'start_time' => '09:00 AM',
            'end_time' => '09:45 AM',
            'class_name' => 'দশম শ্রেণি (Class 10)',
            'section_name' => 'শাখা - ক',
            'subject' => 'পদার্থবিজ্ঞান',
            'teacher_name' => 'মোঃ মোখলেসুর রহমান',
            'room' => 'Lab 1',
            'is_break' => 0,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'period_number' => 2,
            'subject' => 'পদার্থবিজ্ঞান',
            'class_name' => 'দশম শ্রেণি (Class 10)',
            'teacher_name' => 'মোঃ মোখলেসুর রহমান',
            'room' => 'Lab 1',
        ]);
    }

    public function test_can_import_weekly_routine_from_json(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $weeklyJson = json_encode([
            'institution_name' => $institution->name,
            'days' => [
                'Saturday' => [
                    'day_name_bn' => 'শনিবার',
                    'periods' => [
                        [
                            'time' => '09:00 AM - 09:45 AM',
                            'class_name' => 'Class 9',
                            'section' => 'Section A',
                            'subject' => 'সাধারণ গণিত',
                            'teacher_name' => 'আহমেদ কবির',
                            'room_number' => 'Room 102',
                            'is_break' => false,
                        ],
                    ],
                ],
                'Sunday' => [
                    'day_name_bn' => 'রবিবার',
                    'periods' => [
                        [
                            'time' => '10:00 AM - 10:45 AM',
                            'class_name' => 'Class 9',
                            'section' => 'Section A',
                            'subject' => 'ইংরেজি',
                            'teacher_name' => 'ফারহানা আক্তার',
                            'room_number' => 'Room 102',
                            'is_break' => false,
                        ],
                    ],
                ],
                'Thursday' => [
                    'day_name_bn' => 'বৃহস্পতিবার',
                    'periods' => [
                        [
                            'time' => '11:15 AM - 11:45 AM',
                            'class_name' => 'সকল শ্রেণি',
                            'section' => '',
                            'subject' => 'টিফিন বিরতি',
                            'teacher_name' => '-',
                            'room_number' => 'ক্যান্টিন',
                            'is_break' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->post('/institution/routines/import-json', [
            'json_content' => $weeklyJson,
            'replace_existing' => 1,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'subject' => 'সাধারণ গণিত',
            'class_name' => 'Class 9',
            'teacher_name' => 'আহমেদ কবির',
        ]);

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Thursday',
            'subject' => 'টিফিন বিরতি',
            'is_break' => 1,
        ]);
    }

    public function test_can_export_routine_as_json(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $response = $this->actingAs($admin)->get('/institution/routines/export-json');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
        $json = $response->json();
        $this->assertArrayHasKey('days', $json);
        $this->assertArrayHasKey('Saturday', $json['days']);
        $this->assertArrayHasKey('Thursday', $json['days']);
    }

    public function test_can_delete_routine_period(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $routine = ClassRoutine::where('institution_id', $institution->id)->first()
            ?? ClassRoutine::create([
                'institution_id' => $institution->id,
                'academic_class_id' => $institution->academicClasses()->first()->id,
                'section_id' => $institution->academicClasses()->first()->sections()->first()->id,
                'day' => 'Saturday',
                'subject' => 'টেস্ট বিষয়',
                'start_time' => '09:00 AM',
                'end_time' => '09:45 AM',
            ]);

        $response = $this->actingAs($admin)->delete("/institution/routines/{$routine->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('class_routines', ['id' => $routine->id]);
    }

    public function test_can_save_routine_grid_with_dynamic_rows_and_columns(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $gridRows = [
            [
                'class_name' => 'ষষ্ঠ শ্রেণি (Class 6)',
                'periods' => [
                    [
                        'period_number' => 1,
                        'start_time' => '09:00 AM',
                        'end_time' => '09:45 AM',
                        'subject' => 'বাংলা সাহিত্য ও ব্যাকরণ',
                        'teacher_name' => 'রফিকুল ইসলাম',
                        'room' => '101',
                        'is_break' => false,
                    ],
                    [
                        'period_number' => 2,
                        'start_time' => '09:45 AM',
                        'end_time' => '10:30 AM',
                        'subject' => 'টিফিন বিরতি',
                        'teacher_name' => '',
                        'room' => 'ক্যান্টিন',
                        'is_break' => true,
                    ],
                ],
            ],
            [
                'class_name' => 'সপ্তম শ্রেণি (Class 7)',
                'periods' => [
                    [
                        'period_number' => 1,
                        'start_time' => '09:00 AM',
                        'end_time' => '09:45 AM',
                        'subject' => 'সাধারণ বিজ্ঞান ও অনুসন্ধান',
                        'teacher_name' => 'ড. নাসরিন জাহান',
                        'room' => '102',
                        'is_break' => false,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post('/institution/routines/save-grid', [
            'day' => 'Saturday',
            'grid_json' => json_encode($gridRows),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'class_name' => 'ষষ্ঠ শ্রেণি (Class 6)',
            'subject' => 'বাংলা সাহিত্য ও ব্যাকরণ',
            'period_number' => 1,
        ]);

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'class_name' => 'সপ্তম শ্রেণি (Class 7)',
            'subject' => 'সাধারণ বিজ্ঞান ও অনুসন্ধান',
            'period_number' => 1,
        ]);
    }

    public function test_can_save_teacher_routine_grid(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        $teacherRows = [
            [
                'teacher_name' => 'মোঃ মোখলেসুর রহমান',
                'periods' => [
                    [
                        'period_number' => 1,
                        'start_time' => '09:00 AM',
                        'end_time' => '09:45 AM',
                        'subject' => 'পদার্থবিজ্ঞান',
                        'class_name' => 'দশম শ্রেণি',
                        'room' => 'Lab 1',
                        'is_break' => false,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post('/institution/routines/save-grid', [
            'day' => 'Saturday',
            'grid_json' => json_encode($teacherRows),
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'teacher_name' => 'মোঃ মোখলেসুর রহমান',
            'class_name' => 'দশম শ্রেণি',
            'subject' => 'পদার্থবিজ্ঞান',
            'period_number' => 1,
        ]);
    }

    public function test_can_save_dynamic_student_class_routine_separately(): void
    {
        $institution = Institution::first();
        $admin = User::where('role', 'institution_admin')->where('institution_id', $institution->id)->first()
            ?? User::factory()->create([
                'institution_id' => $institution->id,
                'role' => 'institution_admin',
            ]);

        // 1. Save routine for 'ক্লাস নাইন'
        $classNineRows = [
            [
                'row_title' => 'শাখা - ক',
                'periods' => [
                    [
                        'period_number' => 1,
                        'start_time' => '09:00 AM',
                        'end_time' => '09:45 AM',
                        'subject' => 'উচ্চতর গণিত',
                        'teacher_name' => 'মোখলেসুর রহমান',
                        'room' => '201',
                        'is_break' => false,
                    ],
                ],
            ],
        ];

        $res1 = $this->actingAs($admin)->post('/institution/routines/save-grid', [
            'day' => 'Saturday',
            'class_name' => 'ক্লাস নাইন',
            'grid_json' => json_encode($classNineRows),
        ]);
        $res1->assertSessionHas('success');

        // 2. Save routine for 'অনার্স ফার্স্ট ইয়ার' on the same day
        $honoursRows = [
            [
                'row_title' => 'শাখা - ক',
                'periods' => [
                    [
                        'period_number' => 1,
                        'start_time' => '10:00 AM',
                        'end_time' => '10:45 AM',
                        'subject' => 'ম্যাক্রোইকোনমিক্স',
                        'teacher_name' => 'ড. নাসরিন জাহান',
                        'room' => '305',
                        'is_break' => false,
                    ],
                ],
            ],
        ];

        $res2 = $this->actingAs($admin)->post('/institution/routines/save-grid', [
            'day' => 'Saturday',
            'class_name' => 'অনার্স ফার্স্ট ইয়ার',
            'grid_json' => json_encode($honoursRows),
        ]);
        $res2->assertSessionHas('success');

        // Verify both classes are stored without overwriting each other
        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'class_name' => 'ক্লাস নাইন',
            'subject' => 'উচ্চতর গণিত',
        ]);

        $this->assertDatabaseHas('class_routines', [
            'institution_id' => $institution->id,
            'day' => 'Saturday',
            'class_name' => 'অনার্স ফার্স্ট ইয়ার',
            'subject' => 'ম্যাক্রোইকোনমিক্স',
        ]);
    }
}
