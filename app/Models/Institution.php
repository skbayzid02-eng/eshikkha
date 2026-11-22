<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'eiin',
        'type',
        'division',
        'district',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'status',
        'subscription_plan',
        'student_capacity',
    ];

    /**
     * Complete list of all 19 supported institution types with Full Management (পূর্ণাঙ্গ ব্যবস্থাপনা)
     *
     * @return array<string, array<string, mixed>>
     */
    public static function supportedTypes(): array
    {
        return [
            'govt_primary_school' => [
                'name_bn' => 'সরকারি প্রাথমিক বিদ্যালয়',
                'name_en' => 'Government Primary School',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-school',
                'classes' => [
                    'Class 1 (প্রথম শ্রেণি)',
                    'Class 2 (দ্বিতীয় শ্রেণি)',
                    'Class 3 (তৃতীয় শ্রেণি)',
                    'Class 4 (চতুর্থ শ্রেণি)',
                    'Class 5 (পঞ্চম শ্রেণি)',
                    'প্রাক-প্রাথমিক / শিশু শ্রেণি',
                ],
            ],
            'private_primary_school' => [
                'name_bn' => 'বেসরকারি প্রাথমিক বিদ্যালয়',
                'name_en' => 'Private Primary School',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-school-flag',
                'classes' => [
                    'প্লে গ্রুপ (Play)',
                    'নার্সারি (Nursery)',
                    'কেজি (KG)',
                    'Class 1 (প্রথম শ্রেণি)',
                    'Class 2 (দ্বিতীয় শ্রেণি)',
                    'Class 3 (তৃতীয় শ্রেণি)',
                    'Class 4 (চতুর্থ শ্রেণি)',
                    'Class 5 (পঞ্চম শ্রেণি)',
                ],
            ],
            'govt_high_school' => [
                'name_bn' => 'সরকারি উচ্চ বিদ্যালয়',
                'name_en' => 'Government High School',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-building-columns',
                'classes' => [
                    'Class 6 (ষষ্ঠ শ্রেণি)',
                    'Class 7 (সপ্তম শ্রেণি)',
                    'Class 8 (অষ্টম শ্রেণি)',
                    'Class 9 (নবম শ্রেণি)',
                    'Class 10 (দশম শ্রেণি)',
                    'SSC পরীক্ষার্থী ব্যাচ',
                ],
            ],
            'private_high_school' => [
                'name_bn' => 'বেসরকারি উচ্চ বিদ্যালয়',
                'name_en' => 'Private High School',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-school',
                'classes' => [
                    'Class 6 (ষষ্ঠ শ্রেণি)',
                    'Class 7 (সপ্তম শ্রেণি)',
                    'Class 8 (অষ্টম শ্রেণি)',
                    'Class 9 (নবম শ্রেণি)',
                    'Class 10 (দশম শ্রেণি)',
                    'SSC পরীক্ষার্থী ব্যাচ',
                ],
            ],
            'kindergarten' => [
                'name_bn' => 'কিন্ডারগার্টেন',
                'name_en' => 'Kindergarten',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-shapes',
                'classes' => [
                    'প্লে গ্রুপ (Play)',
                    'নার্সারি (Nursery)',
                    'কেজি (KG)',
                    'Class 1 (প্রথম শ্রেণি)',
                    'Class 2 (দ্বিতীয় শ্রেণি)',
                    'Class 3 (তৃতীয় শ্রেণি)',
                    'Class 4 (চতুর্থ শ্রেণি)',
                    'Class 5 (পঞ্চম শ্রেণি)',
                ],
            ],
            'preschool_nursery' => [
                'name_bn' => 'প্রি-স্কুল ও নার্সারি',
                'name_en' => 'Pre-School & Nursery',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-child',
                'classes' => [
                    'টডলার (Toddler)',
                    'প্লে গ্রুপ (Play)',
                    'নার্সারি (Nursery)',
                    'জুনিয়র কেজি (Jr. KG)',
                    'সিনিয়র কেজি (Sr. KG)',
                    'Class 1 (প্রথম শ্রেণি)',
                ],
            ],
            'govt_college' => [
                'name_bn' => 'সরকারি কলেজ',
                'name_en' => 'Government College',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-landmark',
                'classes' => [
                    'একাদশ শ্রেণি (HSC 1st Year)',
                    'দ্বাদশ শ্রেণি (HSC 2nd Year)',
                    'ডিগ্রি ১ম বর্ষ',
                    'ডিগ্রি ২য় বর্ষ',
                    'ডিগ্রি ৩য় বর্ষ',
                    'অনার্স ১ম বর্ষ',
                ],
            ],
            'private_college' => [
                'name_bn' => 'বেসরকারি কলেজ',
                'name_en' => 'Private College',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-graduation-cap',
                'classes' => [
                    'একাদশ শ্রেণি বিজ্ঞান (HSC Science)',
                    'একাদশ শ্রেণি মানবিক (HSC Humanities)',
                    'একাদশ শ্রেণি ব্যবসায় শিক্ষা (HSC Business Studies)',
                    'দ্বাদশ শ্রেণি (HSC 2nd Year)',
                    'ডিগ্রি / পাস কোর্স',
                ],
            ],
            'madrasah' => [
                'name_bn' => 'মাদ্রাসা',
                'name_en' => 'Madrasah',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-mosque',
                'classes' => [
                    'ইবতেদায়ী ১ম শ্রেণি (Class 1)',
                    'ইবতেদায়ী ৫ম শ্রেণি (Class 5)',
                    'দাখিল ৬ষ্ঠ শ্রেণি (Class 6)',
                    'দাখিল ৮ম শ্রেণি (Class 8)',
                    'দাখিল ১০ম শ্রেণি (Class 10)',
                    'আলিম ১ম বর্ষ (HSC Equivalent)',
                ],
            ],
            'alia_madrasah' => [
                'name_bn' => 'আলিয়া মাদ্রাসা',
                'name_en' => 'Alia Madrasah',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-kaaba',
                'classes' => [
                    'ইবতেদায়ী ১ম শ্রেণি (Class 1)',
                    'ইবতেদায়ী ৫ম শ্রেণি (Class 5)',
                    'দাখিল ১০ম শ্রেণি (Dakhil)',
                    'আলিম ১ম বর্ষ (Alim 1st Year)',
                    'আলিম ২য় বর্ষ (Alim 2nd Year)',
                    'ফাজিল ১ম-৩য় বর্ষ',
                    'কামিল হাদিস / তাফসির',
                ],
            ],
            'qawmi_madrasah' => [
                'name_bn' => 'কওমি মাদ্রাসা',
                'name_en' => 'Qawmi Madrasah',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-book-quran',
                'classes' => [
                    'নূরানী ও মক্তব',
                    'নাজেরা জামাত',
                    'হিফজুল কুরআন বিভাগ',
                    'মিযান জামাত',
                    'শরহে বেকায়া',
                    'জালালাইন জামাত',
                    'দাওরায়ে হাদিস (তাকমীল)',
                ],
            ],
            'university' => [
                'name_bn' => 'বিশ্ববিদ্যালয়',
                'name_en' => 'University',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-university',
                'classes' => [
                    '১ম বর্ষ ১ম সেমিস্টার (1st Sem)',
                    '১ম বর্ষ ২য় সেমিস্টার (2nd Sem)',
                    '২য় বর্ষ ৩য় সেমিস্টার (3rd Sem)',
                    '২য় বর্ষ ৪র্থ সেমিস্টার (4th Sem)',
                    '৩য় বর্ষ ৫ম সেমিস্টার (5th Sem)',
                    '৩য় বর্ষ ৬ষ্ঠ সেমিস্টার (6th Sem)',
                    '৪র্থ বর্ষ ৭ম সেমিস্টার (7th Sem)',
                    '৪র্থ বর্ষ ৮ম সেমিস্টার (8th Sem)',
                    'মাস্টার্স / এমফিল / পিএইচডি',
                ],
            ],
            'medical_college' => [
                'name_bn' => 'মেডিকেল কলেজ',
                'name_en' => 'Medical College',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-user-doctor',
                'classes' => [
                    'MBBS ১ম প্রফেশনাল (1st Prof)',
                    'MBBS ২য় প্রফেশনাল (2nd Prof)',
                    'MBBS ৩য় প্রফেশনাল (3rd Prof)',
                    'MBBS চূড়ান্ত প্রফেশনাল (Final Prof)',
                    'BDS ডেন্টাল বিভাগ',
                    'ইন্টার্নশিপ চিকিৎসক',
                ],
            ],
            'engineering_college' => [
                'name_bn' => 'ইঞ্জিনিয়ারিং কলেজ',
                'name_en' => 'Engineering College',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-microchip',
                'classes' => [
                    'B.Sc Engineering ১ম সেমিস্টার',
                    'B.Sc Engineering ২য় সেমিস্টার',
                    'B.Sc Engineering ৩য় সেমিস্টার',
                    'B.Sc Engineering ৪র্থ সেমিস্টার',
                    'B.Sc Engineering ৫ম সেমিস্টার',
                    'B.Sc Engineering ৬ষ্ঠ সেমিস্টার',
                    'B.Sc Engineering ৭ম সেমিস্টার',
                    'B.Sc Engineering ৮ম সেমিস্টার',
                ],
            ],
            'polytechnic' => [
                'name_bn' => 'পলিটেকনিক',
                'name_en' => 'Polytechnic Institute',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-gears',
                'classes' => [
                    'ডিপ্লোমা ১ম পর্ব (1st Sem)',
                    'ডিপ্লোমা ২য় পর্ব (2nd Sem)',
                    'ডিপ্লোমা ৩য় পর্ব (3rd Sem)',
                    'ডিপ্লোমা ৪র্থ পর্ব (4th Sem)',
                    'ডিপ্লোমা ৫ম পর্ব (5th Sem)',
                    'ডিপ্লোমা ৬ষ্ঠ পর্ব (6th Sem)',
                    'ডিপ্লোমা ৭ম পর্ব (7th Sem)',
                    'ডিপ্লোমা ৮ম পর্ব (ইন্টার্নশিপ)',
                ],
            ],
            'vocational_training' => [
                'name_bn' => 'কারিগরি প্রশিক্ষণ কেন্দ্র',
                'name_en' => 'Technical & Vocational Training Center',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-wrench',
                'classes' => [
                    'ভোকেশনাল এসএসসি ১ম বর্ষ',
                    'ভোকেশনাল এসএসসি ২য় বর্ষ',
                    'ইলেকট্রিক্যাল ও ইলেকট্রনিক্স ট্রেড',
                    'অটোমোবাইল ও মেকানিক্যাল ট্রেড',
                    'কম্পিউটার অপারেশন ও গ্রাফিক্স ট্রেড',
                    'শর্ট কোর্স (৩ মাস / ৬ মাস)',
                ],
            ],
            'coaching_center' => [
                'name_bn' => 'কোচিং সেন্টার',
                'name_en' => 'Coaching Center',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-chalkboard-user',
                'classes' => [
                    'মেডিকেল ভর্তি কোচিং ব্যাচ',
                    'বুয়েট ও ইঞ্জিনিয়ারিং ভর্তি ব্যাচ',
                    'বিশ্ববিদ্যালয় ক-ইউনিট বিজ্ঞান',
                    'বিশ্ববিদ্যালয় খ/গ ইউনিট মানবিক ও ব্যবসায়',
                    'এইচএসসি একাডেমিক মডেল টেস্ট ব্যাচ',
                    'এসএসসি স্পেশাল রিভিশন ব্যাচ',
                ],
            ],
            'english_medium' => [
                'name_bn' => 'ইংরেজি মাধ্যম প্রতিষ্ঠান',
                'name_en' => 'English Medium Institution',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-earth-americas',
                'classes' => [
                    'Playgroup',
                    'Nursery',
                    'Kindergarten (KG)',
                    'Class 1 (Grade 1)',
                    'Class 2 (Grade 2)',
                    'Class 3 (Grade 3)',
                    'Class 4 (Grade 4)',
                    'Class 5 (Grade 5)',
                    'Grade 6 to Grade 8 (Junior Section)',
                    'O Level (Grade 9 & 10)',
                    'A Level (AS & A2 Level)',
                ],
            ],
            'religious_other' => [
                'name_bn' => 'ধর্মীয় ও অন্যান্য শিক্ষা প্রতিষ্ঠান',
                'name_en' => 'Religious & Other Educational Institutions',
                'management' => 'পূর্ণাঙ্গ',
                'icon' => 'fa-solid fa-hands-praying',
                'classes' => [
                    'নূরানী কোরআন শিক্ষা কোর্স',
                    'বয়স্ক দ্বীনি তালিম ও তাজবীদ',
                    'হিফজুল কুরআন বিভাগ',
                    'ভাষা ও ধর্মীয় সংস্কৃতি শিক্ষা',
                    'বিশেষ ধর্মতত্ত্ব ও নৈতিক শিক্ষা কোর্স',
                ],
            ],
        ];
    }

    /**
     * Get valid type keys including 13 user-defined categories and legacy mappings
     *
     * @return array<int, string>
     */
    public static function validTypes(): array
    {
        return array_merge(array_keys(self::supportedTypes()), [
            'school',
            'college',
            'madrasah',
            'university',
            'govt_school',
            'private_school',
            'primary_school',
            'vocational',
            'other_institution',
        ]);
    }

    /**
     * Human-friendly Bengali name for institution type
     */
    public function getTypeNameAttribute(): string
    {
        $types = self::supportedTypes();
        if (isset($types[$this->type])) {
            return $types[$this->type]['name_bn'];
        }

        return match ($this->type) {
            'govt_school' => 'সরকারি স্কুল',
            'private_school' => 'বেসরকারি স্কুল',
            'govt_college' => 'সরকারি কলেজ',
            'private_college' => 'বেসরকারি কলেজ',
            'primary_school' => 'প্রাথমিক বিদ্যালয়',
            'kindergarten' => 'কিন্ডারগার্টেন',
            'madrasah' => 'মাদ্রাসা',
            'vocational' => 'কারিগরি প্রতিষ্ঠান',
            'polytechnic' => 'পলিটেকনিক',
            'university' => 'বিশ্ববিদ্যালয়',
            'medical_college' => 'মেডিকেল কলেজ',
            'engineering_college' => 'ইঞ্জিনিয়ারিং কলেজ',
            'other_institution' => 'অন্যান্য প্রতিষ্ঠান',
            'school' => 'উচ্চ বিদ্যালয়',
            'college' => 'কলেজ',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Management label
     */
    public function getManagementAttribute(): string
    {
        return 'পূর্ণাঙ্গ';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function academicClasses(): HasMany
    {
        return $this->hasMany(AcademicClass::class);
    }

    public function classes(): HasMany
    {
        return $this->academicClasses();
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function examTypes(): HasMany
    {
        return $this->hasMany(ExamType::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function classRoutines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class);
    }
}
