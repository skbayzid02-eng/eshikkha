<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
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
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentExamSummary;
use App\Models\StudentLeave;
use App\Models\StudentMessage;
use App\Models\SubjectInternalMark;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        $today = Carbon::today();

        // 1. Super Admin
        User::create([
            'name' => 'Engr. Shafiur Rahman (Super Admin)',
            'email' => 'superadmin@eshikkha.com',
            'password' => $password,
            'role' => 'super_admin',
            'phone' => '01700000001',
            'status' => 'active',
        ]);

        // 2. Institutions across Bangladesh
        $rajshahiCollegiate = Institution::create([
            'name' => 'রাজশাহী কলেজিয়েট স্কুল',
            'slug' => 'rajshahi-collegiate',
            'code' => 'RCS-303',
            'eiin' => '126485',
            'type' => 'school',
            'division' => 'Rajshahi',
            'district' => 'Rajshahi',
            'address' => 'Boalia, Rajshahi-6000',
            'phone' => '01711556677',
            'email' => 'info@rcs.edu.bd',
            'status' => 'active',
            'subscription_plan' => 'standard',
            'student_capacity' => 2000,
        ]);

        $drmc = Institution::create([
            'name' => 'ঢাকা রেসিডেনসিয়াল মডেল কলেজ (DRMC)',
            'slug' => 'drmc',
            'code' => 'DRMC-101',
            'eiin' => '108252',
            'type' => 'college',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'address' => 'Mirpur Road, Mohammadpur, Dhaka-1207',
            'phone' => '01711223344',
            'email' => 'info@drmc.edu.bd',
            'status' => 'active',
            'subscription_plan' => 'enterprise',
            'student_capacity' => 4500,
        ]);

        $tamirulMillat = Institution::create([
            'name' => 'তা’মীরুল মিল্লাত কামিল মাদ্রাসা',
            'slug' => 'tamirul-millat',
            'code' => 'TMKM-202',
            'eiin' => '108170',
            'type' => 'madrasah',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'address' => 'Mirpur-1, Dhaka-1216',
            'phone' => '01811334455',
            'email' => 'info@tamirulmillat.edu.bd',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'student_capacity' => 3200,
        ]);

        $sust = Institution::create([
            'name' => 'শাহজালাল বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয় (Engineering Wing)',
            'slug' => 'sust-wing',
            'code' => 'SUST-404',
            'eiin' => '136001',
            'type' => 'university',
            'division' => 'Sylhet',
            'district' => 'Sylhet',
            'address' => 'Kumargaon, Sylhet-3114',
            'phone' => '01911778899',
            'email' => 'registrar@sust.edu',
            'status' => 'active',
            'subscription_plan' => 'enterprise',
            'student_capacity' => 6000,
        ]);

        $chittagongGovt = Institution::create([
            'name' => 'চট্টগ্রাম সরকারি উচ্চ বিদ্যালয়',
            'slug' => 'ctg-govt-school',
            'code' => 'CGHS-505',
            'eiin' => '104050',
            'type' => 'school',
            'division' => 'Chittagong',
            'district' => 'Chittagong',
            'address' => 'College Road, Chawkbazar, Chittagong',
            'phone' => '01811998877',
            'email' => 'info@cghs.edu.bd',
            'status' => 'pending',
            'subscription_plan' => 'starter',
            'student_capacity' => 1500,
        ]);

        // 3. Institution Admins
        User::create([
            'institution_id' => $drmc->id,
            'name' => 'Brig. Gen. Kazi Shameem (Principal)',
            'email' => 'admin@drmc.edu.bd',
            'password' => $password,
            'role' => 'institution_admin',
            'phone' => '01712000001',
            'status' => 'active',
        ]);

        User::create([
            'institution_id' => $tamirulMillat->id,
            'name' => 'Dr. Muhammad Abu Yusuf Khan (Principal)',
            'email' => 'admin@tamirulmillat.edu.bd',
            'password' => $password,
            'role' => 'institution_admin',
            'phone' => '01812000001',
            'status' => 'active',
        ]);

        // 4. Academic Classes & Sections for the 4 Children
        // Child 1: School - Class 8 (রাজশাহী কলেজিয়েট স্কুল)
        $class8Rcs = AcademicClass::create([
            'institution_id' => $rajshahiCollegiate->id,
            'name' => 'অষ্টম শ্রেণি (Class 8)',
            'numeric_level' => 8,
            'medium' => 'bangla',
        ]);
        $secRcs = Section::create([
            'institution_id' => $rajshahiCollegiate->id,
            'academic_class_id' => $class8Rcs->id,
            'name' => 'পদ্মা (Section Padma)',
            'shift' => 'morning',
            'room_number' => 'Building 1 - Room 202',
        ]);

        // Child 2: College - Class 11 (DRMC)
        $class11Drmc = AcademicClass::create([
            'institution_id' => $drmc->id,
            'name' => 'একাদশ শ্রেণি (HSC Science)',
            'numeric_level' => 11,
            'medium' => 'bangla',
        ]);
        $secDrmc = Section::create([
            'institution_id' => $drmc->id,
            'academic_class_id' => $class11Drmc->id,
            'name' => 'বিজ্ঞান-১ (Science A)',
            'shift' => 'morning',
            'room_number' => 'College Bhaban - 301',
        ]);

        // Child 3: Madrasah - Class 6 (তা’মীরুল মিল্লাত)
        $class6Tmkm = AcademicClass::create([
            'institution_id' => $tamirulMillat->id,
            'name' => 'দাখিল ৬ষ্ঠ শ্রেণি (Class 6)',
            'numeric_level' => 6,
            'medium' => 'arabic',
        ]);
        $secTmkm = Section::create([
            'institution_id' => $tamirulMillat->id,
            'academic_class_id' => $class6Tmkm->id,
            'name' => 'শাখা আলিফ (Section Alif)',
            'shift' => 'morning',
            'room_number' => 'Madrasah Bhaban - 105',
        ]);

        // Child 4: University - 2nd Semester (SUST)
        $classSust = AcademicClass::create([
            'institution_id' => $sust->id,
            'name' => 'কম্পিউটার সায়েন্স ২য় সেমিস্টার (CSE 2nd Sem)',
            'numeric_level' => 14,
            'medium' => 'english',
        ]);
        $secSust = Section::create([
            'institution_id' => $sust->id,
            'academic_class_id' => $classSust->id,
            'name' => 'ব্যাচ ২৬ (Section A)',
            'shift' => 'day',
            'room_number' => 'CSE Building - Lab 3',
        ]);

        // 5. Teachers & Teacher User
        $teacherUser1 = User::create([
            'institution_id' => $drmc->id,
            'name' => 'মুহাম্মদ রফিকুল ইসলাম (Teacher)',
            'email' => 'teacher@drmc.edu.bd',
            'phone' => '01715000001',
            'password' => $password,
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $teacher1 = Teacher::create([
            'institution_id' => $drmc->id,
            'user_id' => $teacherUser1->id,
            'employee_id' => 'EMP-DRMC-01',
            'name' => 'মুহাম্মদ রফিকুল ইসলাম',
            'designation' => 'সিনিয়র সহকারী শিক্ষক (গণিত)',
            'department' => 'Mathematics',
            'phone' => '01715000001',
            'email' => 'teacher@drmc.edu.bd',
            'status' => 'active',
        ]);

        // 6. The Parent / Guardian Account
        $guardian = User::create([
            'name' => 'মোঃ রফিকুল ইসলাম চৌধুরী',
            'email' => 'guardian@eshikkha.com',
            'phone' => '01711000000',
            'password' => $password,
            'role' => 'guardian',
            'status' => 'active',
        ]);

        // 7. Student Users and the 4 Children across 4 Institutions
        // Child 1 (School, Class 8 - Farhan Ahmed)
        $userChild1 = User::create([
            'institution_id' => $rajshahiCollegiate->id,
            'name' => 'ফারহান আহমেদ (Farhan Ahmed)',
            'email' => 'student1@rcs.edu.bd',
            'phone' => '01719000001',
            'password' => $password,
            'role' => 'student',
            'status' => 'active',
        ]);
        $child1 = Student::create([
            'institution_id' => $rajshahiCollegiate->id,
            'user_id' => $userChild1->id,
            'academic_class_id' => $class8Rcs->id,
            'section_id' => $secRcs->id,
            'admission_number' => 'RCS-ADM-2024-008',
            'roll_number' => 5,
            'name' => 'ফারহান আহমেদ',
            'gender' => 'male',
            'dob' => '2011-04-10',
            'blood_group' => 'A+',
            'guardian_name' => $guardian->name,
            'guardian_phone' => $guardian->phone,
            'guardian_relation' => 'father',
            'address' => 'Boalia, Rajshahi',
            'status' => 'active',
        ]);

        // Child 2 (College, Class 11 - Tanvir Ahmed)
        $userChild2 = User::create([
            'institution_id' => $drmc->id,
            'name' => 'তানভীর আহমেদ (Tanvir Ahmed)',
            'email' => 'student@drmc.edu.bd',
            'phone' => '01719000002',
            'password' => $password,
            'role' => 'student',
            'status' => 'active',
        ]);
        $child2 = Student::create([
            'institution_id' => $drmc->id,
            'user_id' => $userChild2->id,
            'academic_class_id' => $class11Drmc->id,
            'section_id' => $secDrmc->id,
            'admission_number' => 'DRMC-ADM-2024-001',
            'roll_number' => 1,
            'name' => 'তানভীর আহমেদ',
            'gender' => 'male',
            'dob' => '2009-08-14',
            'blood_group' => 'B+',
            'guardian_name' => $guardian->name,
            'guardian_phone' => $guardian->phone,
            'guardian_relation' => 'father',
            'address' => 'Dhanmondi, Dhaka',
            'status' => 'active',
        ]);

        // Child 3 (Madrasah, Class 6 - Hafiz Abdullah)
        $userChild3 = User::create([
            'institution_id' => $tamirulMillat->id,
            'name' => 'হাফেজ আব্দুল্লাহ (Hafiz Abdullah)',
            'email' => 'student3@tmkm.edu.bd',
            'phone' => '01719000003',
            'password' => $password,
            'role' => 'student',
            'status' => 'active',
        ]);
        $child3 = Student::create([
            'institution_id' => $tamirulMillat->id,
            'user_id' => $userChild3->id,
            'academic_class_id' => $class6Tmkm->id,
            'section_id' => $secTmkm->id,
            'admission_number' => 'TMKM-ADM-2024-006',
            'roll_number' => 3,
            'name' => 'হাফেজ আব্দুল্লাহ আল নোমান',
            'gender' => 'male',
            'dob' => '2013-02-18',
            'blood_group' => 'O+',
            'guardian_name' => $guardian->name,
            'guardian_phone' => $guardian->phone,
            'guardian_relation' => 'father',
            'address' => 'Mirpur, Dhaka',
            'status' => 'active',
        ]);

        // Child 4 (University, 2nd Semester - Sumaiya Akter)
        $userChild4 = User::create([
            'institution_id' => $sust->id,
            'name' => 'সুমাইয়া আক্তার (Sumaiya Akter)',
            'email' => 'student4@sust.edu',
            'phone' => '01719000004',
            'password' => $password,
            'role' => 'student',
            'status' => 'active',
        ]);
        $child4 = Student::create([
            'institution_id' => $sust->id,
            'user_id' => $userChild4->id,
            'academic_class_id' => $classSust->id,
            'section_id' => $secSust->id,
            'admission_number' => 'SUST-ADM-2024-202',
            'roll_number' => 12,
            'name' => 'সুমাইয়া আক্তার',
            'gender' => 'female',
            'dob' => '2005-11-25',
            'blood_group' => 'AB+',
            'guardian_name' => $guardian->name,
            'guardian_phone' => $guardian->phone,
            'guardian_relation' => 'father',
            'address' => 'Kumargaon, Sylhet',
            'status' => 'active',
        ]);

        // 8. Link the 4 Children to the Guardian
        $children = [$child1, $child2, $child3, $child4];
        foreach ($children as $c) {
            GuardianStudent::create([
                'institution_id' => $c->institution_id,
                'guardian_user_id' => $guardian->id,
                'student_id' => $c->id,
                'relationship' => 'father',
                'status' => 'approved',
                'verified_at' => $today,
            ]);
        }

        // 8.1 Create Academic Years & Student Enrollments (Point 3)
        foreach ([$rajshahiCollegiate, $drmc, $tamirulMillat, $sust] as $inst) {
            $ay2026 = AcademicYear::firstOrCreate(
                ['institution_id' => $inst->id, 'name' => '2026'],
                ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true, 'status' => 'active']
            );
            $ay2025 = AcademicYear::firstOrCreate(
                ['institution_id' => $inst->id, 'name' => '2025'],
                ['start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'is_current' => false, 'status' => 'completed']
            );

            foreach ($inst->students as $st) {
                StudentEnrollment::firstOrCreate(
                    ['institution_id' => $inst->id, 'academic_year_id' => $ay2026->id, 'student_id' => $st->id],
                    [
                        'academic_class_id' => $st->academic_class_id,
                        'section_id' => $st->section_id,
                        'roll_number' => $st->roll_number,
                        'enrollment_date' => '2026-01-01',
                        'status' => 'enrolled',
                    ]
                );
            }
        }

        // 8.2 Gate Attendance & Class Attendance (Point 4)
        foreach ($children as $c) {
            GateAttendance::create([
                'institution_id' => $c->institution_id,
                'student_id' => $c->id,
                'date' => $today->toDateString(),
                'entry_time' => '07:45:00',
                'exit_time' => '13:30:00',
                'status' => 'present',
                'gate_number' => 'Gate 1',
                'remarks' => 'RFID Smart Gate Scan',
            ]);

            ClassAttendance::create([
                'institution_id' => $c->institution_id,
                'student_id' => $c->id,
                'academic_class_id' => $c->academic_class_id,
                'section_id' => $c->section_id,
                'period_number' => 1,
                'subject_name' => 'বাংলা ১ম পত্র',
                'date' => $today->toDateString(),
                'status' => 'present',
                'remarks' => ' উপস্থিত',
            ]);
        }

        // 8.3 Class Report & Cancellation Sample (Point 5)
        ClassReport::create([
            'institution_id' => $drmc->id,
            'teacher_id' => $teacher1->id,
            'academic_class_id' => $class11Drmc->id,
            'section_id' => $secDrmc->id,
            'subject_name' => 'সাধারণ গণিত',
            'period_number' => 1,
            'date' => $today->toDateString(),
            'class_conducted' => true,
            'topics_covered' => 'অধ্যায় ৩: বীজগণিতীয় রাশি ও সূত্রাবলী আলোচনা সম্পন্ন',
            'homework_assigned' => 'অনুশীলনী ৩.১ এর ৫ ও ৬ নম্বর সমস্যা সমাধান',
            'present_count' => 42,
            'absent_count' => 3,
            'remarks' => 'শিক্ষার্থীরা মনোযোগের সাথে অংশগ্রহণ করেছে',
        ]);

        // 9. Today's Attendance & Comprehensive Attendance History for all Children
        Attendance::create([
            'institution_id' => $child1->institution_id,
            'student_id' => $child1->id,
            'date' => $today->toDateString(),
            'status' => 'present',
            'remarks' => 'RFID Entry: 07:50 AM',
        ]);

        // Historical Attendance Dataset for Child 2 (Tanvir Ahmed)
        $historyData = [
            ['date' => $today->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:54 AM'],
            ['date' => $today->copy()->subDays(1)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:51 AM'],
            ['date' => $today->copy()->subDays(2)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:48 AM'],
            ['date' => $today->copy()->subDays(3)->toDateString(), 'status' => 'late', 'remarks' => 'Gate Entry: 08:05 AM (বাস বিলম্ব - ৫ মিনিট দেরি)'],
            ['date' => $today->copy()->subDays(4)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:55 AM'],
            ['date' => $today->copy()->subDays(6)->toDateString(), 'status' => 'leave', 'remarks' => 'জাতীয় শিক্ষা সপ্তাহ প্রস্তুতিমূলক ছুটি'],
            ['date' => $today->copy()->subDays(7)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:50 AM'],
            ['date' => $today->copy()->subDays(8)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:52 AM'],
            ['date' => $today->copy()->subDays(9)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:56 AM'],
            ['date' => $today->copy()->subDays(10)->toDateString(), 'status' => 'late', 'remarks' => 'Gate Entry: 08:15 AM (বৃষ্টির কারণে বিলম্ব - ১৫ মিনিট দেরি)'],
            ['date' => $today->copy()->subDays(12)->toDateString(), 'status' => 'leave', 'remarks' => 'পারিবারিক অনুষ্ঠান ও সামাজিক ছুটি'],
            ['date' => $today->copy()->subDays(13)->toDateString(), 'status' => 'leave', 'remarks' => 'পারিবারিক অনুষ্ঠান ও সামাজিক ছুটি'],
            ['date' => $today->copy()->subDays(14)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:53 AM'],
            ['date' => $today->copy()->subDays(15)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:49 AM'],
            ['date' => $today->copy()->subDays(16)->toDateString(), 'status' => 'absent', 'remarks' => 'অনুপস্থিত (তীব্র মাথাব্যথা ও অসুস্থতা)'],
            ['date' => $today->copy()->subDays(17)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:50 AM'],
            ['date' => $today->copy()->subDays(18)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:52 AM'],
            ['date' => $today->copy()->subDays(20)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:48 AM'],
            ['date' => $today->copy()->subDays(21)->toDateString(), 'status' => 'late', 'remarks' => 'Gate Entry: 08:25 AM (মিরপুর রোডে তীব্র যানজট - ২৫ মিনিট দেরি)'],
            ['date' => $today->copy()->subDays(22)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:55 AM'],
            ['date' => $today->copy()->subDays(23)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:50 AM'],
            ['date' => $today->copy()->subDays(24)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:53 AM'],
            ['date' => $today->copy()->subDays(27)->toDateString(), 'status' => 'leave', 'remarks' => 'অনুমোদিত চিকিৎসাজনিত ছুটি (Medical Leave)'],
            ['date' => $today->copy()->subDays(28)->toDateString(), 'status' => 'leave', 'remarks' => 'অনুমোদিত চিকিৎসাজনিত ছুটি (Medical Leave)'],
            ['date' => $today->copy()->subDays(29)->toDateString(), 'status' => 'leave', 'remarks' => 'অনুমোদিত চিকিৎসাজনিত ছুটি (Medical Leave)'],
            ['date' => $today->copy()->subDays(30)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:50 AM'],
            ['date' => $today->copy()->subDays(31)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:52 AM'],
            ['date' => $today->copy()->subDays(34)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:49 AM'],
            ['date' => $today->copy()->subDays(35)->toDateString(), 'status' => 'absent', 'remarks' => 'অনুপস্থিত (অনির্দিষ্ট অনুপস্থিতি / Uninformed Absence)'],
            ['date' => $today->copy()->subDays(36)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:54 AM'],
            ['date' => $today->copy()->subDays(37)->toDateString(), 'status' => 'present', 'remarks' => 'RFID Gate Entry: 07:51 AM'],
        ];

        foreach ($historyData as $record) {
            Attendance::create([
                'institution_id' => $child2->institution_id,
                'student_id' => $child2->id,
                'date' => $record['date'],
                'status' => $record['status'],
                'remarks' => $record['remarks'],
            ]);
        }

        // Student Leaves for Child 2 (Tanvir Ahmed)
        StudentLeave::create([
            'institution_id' => $child2->institution_id,
            'student_id' => $child2->id,
            'leave_type' => 'medical',
            'title' => 'চিকিৎসাজনিত ছুটি (Medical Leave - Viral Fever)',
            'start_date' => $today->copy()->subDays(29)->toDateString(),
            'end_date' => $today->copy()->subDays(27)->toDateString(),
            'days_count' => 3,
            'reason' => 'Doctor prescribed complete rest and medication for seasonal viral fever.',
            'status' => 'approved',
            'approved_by' => 'Brig. Gen. Kazi Shameem (Principal)',
        ]);

        StudentLeave::create([
            'institution_id' => $child2->institution_id,
            'student_id' => $child2->id,
            'leave_type' => 'family',
            'title' => 'পারিবারিক অনুষ্ঠান ও সামাজিক ছুটি (Family Wedding)',
            'start_date' => $today->copy()->subDays(13)->toDateString(),
            'end_date' => $today->copy()->subDays(12)->toDateString(),
            'days_count' => 2,
            'reason' => 'Elder sister wedding reception at native village home.',
            'status' => 'approved',
            'approved_by' => 'Muhammad Rafiqul Islam (Class Teacher)',
        ]);

        StudentLeave::create([
            'institution_id' => $child2->institution_id,
            'student_id' => $child2->id,
            'leave_type' => 'holiday',
            'title' => 'জাতীয় শিক্ষা সপ্তাহ প্রস্তুতিমূলক ছুটি (Debate Competition)',
            'start_date' => $today->copy()->subDays(6)->toDateString(),
            'end_date' => $today->copy()->subDays(6)->toDateString(),
            'days_count' => 1,
            'reason' => 'Representing College in District Level Inter-College Debate Final.',
            'status' => 'approved',
            'approved_by' => 'Academic Coordinator',
        ]);

        Attendance::create([
            'institution_id' => $child3->institution_id,
            'student_id' => $child3->id,
            'date' => $today->toDateString(),
            'status' => 'present',
            'remarks' => 'Biometric Punch: 07:45 AM',
        ]);

        Attendance::create([
            'institution_id' => $child4->institution_id,
            'student_id' => $child4->id,
            'date' => $today->toDateString(),
            'status' => 'present',
            'remarks' => 'Lab Terminal Login: 08:30 AM',
        ]);

        // 10. Exam Results for the 4 Children
        // Child 1 (School)
        $exam1 = Exam::create([
            'institution_id' => $child1->institution_id,
            'academic_class_id' => $class8Rcs->id,
            'title' => 'অর্ধবার্ষিক মূল্যায়ন ২০২৬',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-15',
            'session_year' => '2026',
        ]);
        ExamResult::create([
            'institution_id' => $child1->institution_id,
            'exam_id' => $exam1->id,
            'student_id' => $child1->id,
            'subject_name' => 'গণিত ও সাধারণ বিজ্ঞান',
            'total_marks' => 100,
            'obtained_marks' => 88,
            'grade' => 'A+',
            'grade_point' => 5.00,
        ]);
        StudentExamSummary::create([
            'institution_id' => $child1->institution_id,
            'exam_id' => $exam1->id,
            'student_id' => $child1->id,
            'total_marks_possible' => 100,
            'total_marks_obtained' => 88,
            'percentage' => 88.0,
            'gpa' => 5.00,
            'cgpa' => 5.00,
            'grade' => 'A+',
            'section_merit_rank' => 2,
            'class_merit_rank' => 5,
            'total_students_section' => 50,
            'total_students_class' => 200,
            'status' => 'promoted',
            'remarks' => 'অসাধারণ ফলাফল! মেধাক্রমে ২য় স্থান।',
        ]);

        // Child 2 (College)
        $exam2 = Exam::create([
            'institution_id' => $child2->institution_id,
            'academic_class_id' => $class11Drmc->id,
            'title' => '1st Midterm Examination 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-18',
            'session_year' => '2026',
        ]);

        $subMaths = ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'Mathematics III',
            'credits' => 4,
            'total_marks' => 100,
            'theory_marks' => 47,
            'practical_marks' => 0,
            'internal_marks' => 38,
            'obtained_marks' => 85,
            'highest_marks' => 92,
            'grade' => 'A',
            'grade_point' => 4.00,
        ]);

        // Internal Assessment Breakdown for Mathematics III matching screenshot
        SubjectInternalMark::create([
            'exam_result_id' => $subMaths->id,
            'item_name' => 'Assignment 1',
            'marks_obtained' => 5,
            'total_marks' => 5,
            'attendance_rate' => 85,
        ]);
        SubjectInternalMark::create([
            'exam_result_id' => $subMaths->id,
            'item_name' => 'Assignment 2',
            'marks_obtained' => 5,
            'total_marks' => 5,
            'attendance_rate' => 85,
        ]);
        SubjectInternalMark::create([
            'exam_result_id' => $subMaths->id,
            'item_name' => 'Assignment 3',
            'marks_obtained' => 4,
            'total_marks' => 5,
            'attendance_rate' => 85,
        ]);
        SubjectInternalMark::create([
            'exam_result_id' => $subMaths->id,
            'item_name' => 'Assignment 4',
            'marks_obtained' => 3,
            'total_marks' => 5,
            'attendance_rate' => 85,
        ]);
        SubjectInternalMark::create([
            'exam_result_id' => $subMaths->id,
            'item_name' => 'Sessional 1',
            'marks_obtained' => 13,
            'total_marks' => 15,
            'attendance_rate' => 85,
        ]);
        SubjectInternalMark::create([
            'exam_result_id' => $subMaths->id,
            'item_name' => 'Sessional 2',
            'marks_obtained' => 11,
            'total_marks' => 15,
            'attendance_rate' => 85,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'MOS (Mechanics of Solids)',
            'credits' => 3,
            'total_marks' => 100,
            'theory_marks' => 43,
            'practical_marks' => 0,
            'internal_marks' => 49,
            'obtained_marks' => 92,
            'highest_marks' => 96,
            'grade' => 'A+',
            'grade_point' => 5.00,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'BME (Basic Mechanical Eng)',
            'credits' => 3,
            'total_marks' => 100,
            'theory_marks' => 31,
            'practical_marks' => 0,
            'internal_marks' => 37,
            'obtained_marks' => 68,
            'highest_marks' => 88,
            'grade' => 'B',
            'grade_point' => 3.00,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'Physics',
            'credits' => 3,
            'total_marks' => 100,
            'theory_marks' => 32,
            'practical_marks' => 0,
            'internal_marks' => 38,
            'obtained_marks' => 70,
            'highest_marks' => 85,
            'grade' => 'B',
            'grade_point' => 3.00,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'BE (Basic Electrical)',
            'credits' => 3,
            'total_marks' => 100,
            'theory_marks' => 40,
            'practical_marks' => 0,
            'internal_marks' => 40,
            'obtained_marks' => 80,
            'highest_marks' => 90,
            'grade' => 'A',
            'grade_point' => 4.00,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'English',
            'credits' => 3,
            'total_marks' => 100,
            'theory_marks' => 42,
            'practical_marks' => 0,
            'internal_marks' => 42,
            'obtained_marks' => 84,
            'highest_marks' => 88,
            'grade' => 'A',
            'grade_point' => 4.00,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'EG (Engineering Graphics)',
            'credits' => 3,
            'total_marks' => 100,
            'theory_marks' => 48,
            'practical_marks' => 0,
            'internal_marks' => 46,
            'obtained_marks' => 94,
            'highest_marks' => 98,
            'grade' => 'A+',
            'grade_point' => 5.00,
        ]);

        ExamResult::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'subject_name' => 'Workshop',
            'credits' => 1,
            'total_marks' => 100,
            'theory_marks' => 42,
            'practical_marks' => 0,
            'internal_marks' => 43,
            'obtained_marks' => 85,
            'highest_marks' => 90,
            'grade' => 'A',
            'grade_point' => 4.00,
        ]);

        // Overall Exam Summary for Child 2 with Merit Position
        StudentExamSummary::create([
            'institution_id' => $child2->institution_id,
            'exam_id' => $exam2->id,
            'student_id' => $child2->id,
            'total_marks_possible' => 800,
            'total_marks_obtained' => 658,
            'percentage' => 82.25,
            'gpa' => 4.15,
            'cgpa' => 8.64,
            'grade' => 'A+',
            'section_merit_rank' => 1,
            'class_merit_rank' => 3,
            'total_students_section' => 45,
            'total_students_class' => 180,
            'status' => 'promoted',
            'remarks' => 'অসাধারণ ফলাফল! মেধাক্রমে সেকশনে ১ম এবং কলেজে ৩য় স্থান অধিকার করেছে।',
        ]);

        // Child 3 (Madrasah)
        $exam3 = Exam::create([
            'institution_id' => $child3->institution_id,
            'academic_class_id' => $class6Tmkm->id,
            'title' => 'প্রথম সাময়িক মূল্যায়ন',
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-25',
            'session_year' => '2026',
        ]);
        ExamResult::create([
            'institution_id' => $child3->institution_id,
            'exam_id' => $exam3->id,
            'student_id' => $child3->id,
            'subject_name' => 'কুরআন মাজিদ ও তাজভিদ',
            'total_marks' => 100,
            'obtained_marks' => 96,
            'grade' => 'A+',
            'grade_point' => 5.00,
        ]);
        StudentExamSummary::create([
            'institution_id' => $child3->institution_id,
            'exam_id' => $exam3->id,
            'student_id' => $child3->id,
            'total_marks_possible' => 100,
            'total_marks_obtained' => 96,
            'percentage' => 96.0,
            'gpa' => 5.00,
            'cgpa' => 5.00,
            'grade' => 'A+',
            'section_merit_rank' => 1,
            'class_merit_rank' => 1,
            'total_students_section' => 40,
            'total_students_class' => 120,
            'status' => 'promoted',
            'remarks' => 'মুমতাজ (সর্বোচ্চ কৃতিত্ব)। মেধাক্রমে ১ম স্থান।',
        ]);

        // Child 4 (University)
        $exam4 = Exam::create([
            'institution_id' => $child4->institution_id,
            'academic_class_id' => $classSust->id,
            'title' => '১ম সেমিস্টার ফাইনাল এক্সাম',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-20',
            'session_year' => '2026',
        ]);
        ExamResult::create([
            'institution_id' => $child4->institution_id,
            'exam_id' => $exam4->id,
            'student_id' => $child4->id,
            'subject_name' => 'Data Structures & Algorithms',
            'total_marks' => 100,
            'obtained_marks' => 89,
            'grade' => 'A+',
            'grade_point' => 4.00,
        ]);
        StudentExamSummary::create([
            'institution_id' => $child4->institution_id,
            'exam_id' => $exam4->id,
            'student_id' => $child4->id,
            'total_marks_possible' => 100,
            'total_marks_obtained' => 89,
            'percentage' => 89.0,
            'gpa' => 4.00,
            'cgpa' => 3.88,
            'grade' => 'A+',
            'section_merit_rank' => 4,
            'class_merit_rank' => 8,
            'total_students_section' => 60,
            'total_students_class' => 180,
            'status' => 'promoted',
            'remarks' => 'Dean\'s Honor List candidate.',
        ]);

        // 11. Fees for the 4 Children
        Fee::create([
            'institution_id' => $child1->institution_id,
            'student_id' => $child1->id,
            'invoice_no' => 'INV-RCS-2026-001',
            'title' => 'মাসিক বেতন - অক্টোবর ২০২৬',
            'amount' => 1200.00,
            'due_date' => $today->copy()->addDays(5)->toDateString(),
            'status' => 'unpaid',
            'paid_amount' => 0.00,
        ]);

        Fee::create([
            'institution_id' => $child2->institution_id,
            'student_id' => $child2->id,
            'invoice_no' => 'INV-DRMC-2026-1002',
            'title' => 'মডেল টেস্ট ও ল্যাব ফি',
            'amount' => 1800.00,
            'due_date' => $today->copy()->addDays(8)->toDateString(),
            'status' => 'unpaid',
            'paid_amount' => 0.00,
        ]);

        Fee::create([
            'institution_id' => $child3->institution_id,
            'student_id' => $child3->id,
            'invoice_no' => 'INV-TMKM-2026-003',
            'title' => 'মাদ্রাসা শিক্ষাফি - অক্টোবর ২০২৬',
            'amount' => 800.00,
            'due_date' => $today->copy()->subDays(3)->toDateString(),
            'status' => 'paid',
            'paid_amount' => 800.00,
            'payment_method' => 'bKash',
            'transaction_id' => 'BKX78491823',
            'paid_at' => $today->copy()->subDays(1),
        ]);

        Fee::create([
            'institution_id' => $child4->institution_id,
            'student_id' => $child4->id,
            'invoice_no' => 'INV-SUST-2026-202',
            'title' => '২য় সেমিস্টার রেজিস্ট্রেশন ফি',
            'amount' => 6500.00,
            'due_date' => $today->copy()->addDays(14)->toDateString(),
            'status' => 'unpaid',
            'paid_amount' => 0.00,
        ]);

        // 12. Messages for the 4 Children
        StudentMessage::create([
            'student_id' => $child1->id,
            'institution_id' => $child1->institution_id,
            'sender_name' => 'মোঃ মোখলেসুর রহমান (শ্রেণি শিক্ষক)',
            'sender_role' => 'teacher',
            'title' => 'বিজ্ঞান অলিম্পিয়াড প্রস্তুতি',
            'message' => 'ফারহানকে আগামী রবিবারের মধ্যে প্রজেক্টের সারসংক্ষেপ জমা দিতে বলা হয়েছে।',
            'is_read' => false,
        ]);

        StudentMessage::create([
            'student_id' => $child2->id,
            'institution_id' => $child2->institution_id,
            'sender_name' => 'মুহাম্মদ রফিকুল ইসলাম (সিনিয়র শিক্ষক)',
            'sender_role' => 'teacher',
            'title' => 'আন্তঃহাউস ক্রীড়া প্রতিযোগিতা',
            'message' => 'তানভীর আহমেদ কলেজ ক্রিকেট টুর্নামেন্টে নাজির হাউস দলের অধিনায়ক নির্বাচিত হয়েছে।',
            'is_read' => false,
        ]);

        StudentMessage::create([
            'student_id' => $child3->id,
            'institution_id' => $child3->institution_id,
            'sender_name' => 'মাওলানা আব্দুল হান্নান (মুহাদ্দিস)',
            'sender_role' => 'teacher',
            'title' => 'হিফজুল কুরআন প্রতিযোগিতা',
            'message' => 'আব্দুল্লাহ চমৎকার কেরাত তেলাওয়াত প্রদর্শন করেছে এবং পরবর্তী রাউন্ডে উত্তীর্ণ হয়েছে।',
            'is_read' => false,
        ]);

        StudentMessage::create([
            'student_id' => $child4->id,
            'institution_id' => $child4->institution_id,
            'sender_name' => 'ড. মোস্তাফিজুর রহমান (বিভাগীয় প্রধান, CSE)',
            'sender_role' => 'principal',
            'title' => 'সেমিস্টার প্রজেক্ট উপস্থাপন',
            'message' => 'সুমাইয়ার টিম আগামী বুধবার সকাল ১০টায় প্রজেক্ট ডেমনস্ট্রেশন করবে।',
            'is_read' => false,
        ]);

        // 13. Notices
        Notice::create([
            'institution_id' => null, // Global
            'title' => '📢 সমগ্র বাংলাদেশে ই-শিক্ষা প্ল্যাটফর্মে জাতীয় শিক্ষা সপ্তাহ ২০২৬ নিবন্ধন শুরু',
            'content' => 'মাধ্যমিক ও উচ্চশিক্ষা অধিদপ্তর (মাউশি) এবং মাদ্রাসা শিক্ষা বোর্ডের নির্দেশনানুযায়ী সকল শিক্ষা প্রতিষ্ঠানে ডিজিটাল শিক্ষা সপ্তাহ ২০২৬ যথাযথভাবে পালনের আহ্বান জানানো হচ্ছে।',
            'audience' => 'all',
            'published_at' => $today->toDateString(),
            'is_pinned' => true,
        ]);

        Notice::create([
            'institution_id' => $drmc->id,
            'title' => '🏛️ কলেজ ক্যাম্পাসে বার্ষিক বিজ্ঞান মেলা ও রোবোটিক্স উৎসব ২০২৬',
            'content' => 'আগামী ১৫ নভেম্বর থেকে তিন দিনব্যাপী বার্ষিক বিজ্ঞান মেলা ও রোবোটিক্স প্রদর্শনীর আয়োজন করা হয়েছে।',
            'audience' => 'students',
            'published_at' => $today->toDateString(),
            'is_pinned' => true,
        ]);

        Notice::create([
            'institution_id' => $tamirulMillat->id,
            'title' => '📖 আন্তর্জাতিক হিফজুল কুরআন প্রতিযোগিতা ও বিশেষ দোয়া মাহফিল',
            'content' => 'তা’মীরুল মিল্লাত কেন্দ্রীয় অডিটোরিয়ামে আগামী শুক্রবার বিশেষ দোয়া মাহফিল অনুষ্ঠিত হবে।',
            'audience' => 'all',
            'published_at' => $today->toDateString(),
            'is_pinned' => true,
        ]);

        // 14. Class Routine for Child 2 (DRMC)
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        $schedule = [
            ['subject' => 'উচ্চতর গণিত', 'start' => '08:00 AM', 'end' => '08:45 AM', 'room' => 'Room 301'],
            ['subject' => 'পদার্থবিজ্ঞান', 'start' => '08:50 AM', 'end' => '09:35 AM', 'room' => 'Lab 2'],
            ['subject' => 'ইংরেজি', 'start' => '09:40 AM', 'end' => '10:25 AM', 'room' => 'Room 301'],
            ['subject' => 'টিফিন বিরতি', 'start' => '10:25 AM', 'end' => '11:00 AM', 'room' => 'Cafeteria'],
            ['subject' => 'রসায়ন', 'start' => '11:00 AM', 'end' => '11:45 AM', 'room' => 'Lab 1'],
            ['subject' => 'ICT', 'start' => '11:50 AM', 'end' => '12:35 PM', 'room' => 'Computer Lab'],
        ];

        foreach ($days as $day) {
            foreach ($schedule as $period) {
                ClassRoutine::create([
                    'institution_id' => $drmc->id,
                    'academic_class_id' => $class11Drmc->id,
                    'section_id' => $secDrmc->id,
                    'teacher_id' => $teacher1->id,
                    'day' => $day,
                    'subject' => $period['subject'],
                    'start_time' => $period['start'],
                    'end_time' => $period['end'],
                    'room' => $period['room'],
                ]);
            }
        }

        // 15. Homework / Assignments matching Student Portal UI
        Homework::create([
            'institution_id' => $drmc->id,
            'academic_class_id' => $class11Drmc->id,
            'section_id' => $secDrmc->id,
            'teacher_id' => $teacher1->id,
            'subject' => 'English',
            'title' => 'Composition on Emerging AI Technologies in Bangladesh',
            'description' => 'Write a structured essay covering opportunities, challenges, and ethical standards in education.',
            'due_date' => Carbon::now()->addDays(5)->toDateString(),
            'total_marks' => 25,
            'status' => 'due',
            'color_theme' => 'orange',
        ]);

        Homework::create([
            'institution_id' => $drmc->id,
            'academic_class_id' => $class11Drmc->id,
            'section_id' => $secDrmc->id,
            'teacher_id' => $teacher1->id,
            'subject' => 'Maths',
            'title' => 'Linear Equations & Matrix Inverse Practice Set',
            'description' => 'Complete exercises 4.2 to 4.5 from the prescribed higher mathematics textbook.',
            'due_date' => Carbon::now()->subDays(2)->toDateString(),
            'total_marks' => 30,
            'status' => 'overdue',
            'color_theme' => 'blue',
        ]);

        Homework::create([
            'institution_id' => $drmc->id,
            'academic_class_id' => $class11Drmc->id,
            'section_id' => $secDrmc->id,
            'teacher_id' => $teacher1->id,
            'subject' => 'Science',
            'title' => 'Chemistry Titration & Acid-Base Practical Notes',
            'description' => 'Record chemical indicators and titration calculations in the practical notebook.',
            'due_date' => Carbon::now()->addDays(3)->toDateString(),
            'total_marks' => 20,
            'status' => 'due',
            'color_theme' => 'amber',
        ]);

        Homework::create([
            'institution_id' => $drmc->id,
            'academic_class_id' => $class11Drmc->id,
            'section_id' => $secDrmc->id,
            'teacher_id' => $teacher1->id,
            'subject' => 'Hindi / ICT',
            'title' => 'Relational Database Management & SQL Queries',
            'description' => 'Write SQL statements for student enrollment, subject grades, and faculty tables.',
            'due_date' => Carbon::now()->addDays(7)->toDateString(),
            'total_marks' => 25,
            'status' => 'due',
            'color_theme' => 'teal',
        ]);

        // 16. Upcoming Exam Routine / Schedule
        $examSchedules = [
            ['subject' => 'Chemistry 1', 'date' => Carbon::now()->addDays(7)->toDateString(), 'start' => '10:00 AM', 'end' => '01:00 PM', 'room' => 'Science Hall 401', 'marks' => 100],
            ['subject' => 'Mathematics', 'date' => Carbon::now()->addDays(9)->toDateString(), 'start' => '10:00 AM', 'end' => '01:00 PM', 'room' => 'Main Exam Hall', 'marks' => 100],
            ['subject' => 'Physics', 'date' => Carbon::now()->addDays(12)->toDateString(), 'start' => '10:00 AM', 'end' => '01:00 PM', 'room' => 'Science Hall 402', 'marks' => 100],
            ['subject' => 'English', 'date' => Carbon::now()->addDays(14)->toDateString(), 'start' => '10:00 AM', 'end' => '01:00 PM', 'room' => 'Room 301', 'marks' => 100],
            ['subject' => 'ICT & Computer Science', 'date' => Carbon::now()->addDays(16)->toDateString(), 'start' => '10:00 AM', 'end' => '01:00 PM', 'room' => 'Computer Lab 1', 'marks' => 100],
        ];

        foreach ($examSchedules as $item) {
            ExamSchedule::create([
                'institution_id' => $drmc->id,
                'exam_id' => $exam2->id,
                'academic_class_id' => $class11Drmc->id,
                'subject' => $item['subject'],
                'exam_date' => $item['date'],
                'start_time' => $item['start'],
                'end_time' => $item['end'],
                'room' => $item['room'],
                'marks' => $item['marks'],
            ]);
        }

        // 17. NCTB Textbooks & Chapters for Class 1 to 10
        $this->call(TextbookSeeder::class);
    }
}
