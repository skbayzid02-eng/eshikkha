@extends('layouts.institution')

@section('page_title', 'শিক্ষকগণ ও স্টুডেন্টগণ এর সাপ্তাহিক ক্লাস রুটিন')

@section('content')
<div class="space-y-6" x-data="{
    mainTab: 'teacher', // 'teacher' (শিক্ষক রুটিন - ডিফল্ট সক্রিয়) or 'student' (স্টুডেন্ট রুটিন)
    activeDay: 'Saturday',
    newTeacherName: '',
    newCustomClassName: '',

    // Student Classes & Weekly Grids
    studentClassList: @js($initialClassList),
    selectedStudentClass: @js($initialClassList[0] ?? 'ষষ্ঠ শ্রেণি'),
    classGrids: @js($classWeeklyGrids),

    // Teacher Routine Data
    teacherGrid: @js($teacherWeeklyGrid),
    teachersList: @js($teachers->pluck('name')),
    classesList: @js($classes->pluck('name')),

    jsonModal: false,
    schemaModal: false,
    jsonText: @js(json_encode($templateJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
    copied: false,
    saving: false,

    copyJson() {
        navigator.clipboard.writeText(this.jsonText);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    },

    getDayBn(day) {
        const map = {
            'Saturday': 'শনিবার',
            'Sunday': 'রবিবার',
            'Monday': 'সোমবার',
            'Tuesday': 'মঙ্গলবার',
            'Wednesday': 'বুধবার',
            'Thursday': 'বৃহস্পতিবার'
        };
        return map[day] || day;
    },

    // ==========================================
    // STUDENT ROUTINE DYNAMIC CLASS METHODS
    // ==========================================
    selectStudentClass(clsName) {
        this.selectedStudentClass = clsName;
        this.ensureClassGrid(clsName);
    },

    addNewClassButton() {
        const name = this.newCustomClassName.trim();
        if (!name) {
            alert('অনুগ্রহ করে ক্লাসের নাম লিখুন (যেমন: ক্লাস নাইন বা অনার্স ফার্স্ট ইয়ার)!');
            return;
        }
        if (!this.studentClassList.includes(name)) {
            this.studentClassList.push(name);
        }
        this.selectedStudentClass = name;
        this.ensureClassGrid(name);
        this.newCustomClassName = '';
    },

    removeClassButton(index, clsName) {
        if (confirm('\'' + clsName + '\' ক্লাস বাটনটি তালিকা থেকে মুছে ফেলতে চান?')) {
            this.studentClassList.splice(index, 1);
            if (this.selectedStudentClass === clsName) {
                this.selectedStudentClass = this.studentClassList[0] || '';
            }
        }
    },

    ensureClassGrid(clsName) {
        if (!this.classGrids[clsName]) {
            this.classGrids[clsName] = {};
        }
        const days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        days.forEach(d => {
            if (!this.classGrids[clsName][d]) {
                const defaultCols = [
                    { number: 1, start_time: '09:00 AM', end_time: '09:45 AM', label: '১ম পিরিয়ড', is_break: false },
                    { number: 2, start_time: '09:45 AM', end_time: '10:30 AM', label: '২য় পিরিয়ড', is_break: false },
                    { number: 3, start_time: '10:30 AM', end_time: '11:15 AM', label: '৩য় পিরিয়ড', is_break: false },
                    { number: 4, start_time: '11:15 AM', end_time: '11:45 AM', label: 'টিফিন বিরতি', is_break: true },
                    { number: 5, start_time: '11:45 AM', end_time: '12:30 PM', label: '৪র্থ পিরিয়ড', is_break: false },
                    { number: 6, start_time: '12:30 PM', end_time: '01:15 PM', label: '৫ম পিরিয়ড', is_break: false }
                ];
                const cells = defaultCols.map(col => ({
                    period_number: col.number,
                    start_time: col.start_time,
                    end_time: col.end_time,
                    subject: '',
                    teacher_name: '',
                    room: '',
                    is_break: col.is_break
                }));

                this.classGrids[clsName][d] = {
                    day_name_bn: this.getDayBn(d),
                    columns: defaultCols,
                    rows: [
                        {
                            row_title: 'শাখা - ক (Section A)',
                            periods: cells
                        }
                    ]
                };
            }
        });
    },

    currentClassDay() {
        if (!this.selectedStudentClass) {
            if (this.studentClassList.length > 0) {
                this.selectedStudentClass = this.studentClassList[0];
            } else {
                this.studentClassList.push('ষষ্ঠ শ্রেণি');
                this.selectedStudentClass = 'ষষ্ঠ শ্রেণি';
            }
        }
        this.ensureClassGrid(this.selectedStudentClass);
        return this.classGrids[this.selectedStudentClass][this.activeDay];
    },

    addClassColumn() {
        const day = this.currentClassDay();
        const nextNum = day.columns.length + 1;
        const newCol = {
            number: nextNum,
            start_time: '01:15 PM',
            end_time: '02:00 PM',
            label: nextNum + 'ম পিরিয়ড',
            is_break: false
        };
        day.columns.push(newCol);
        day.rows.forEach(row => {
            row.periods.push({
                period_number: nextNum,
                start_time: newCol.start_time,
                end_time: newCol.end_time,
                subject: '',
                teacher_name: '',
                room: '',
                is_break: false
            });
        });
    },

    removeClassColumn() {
        const day = this.currentClassDay();
        if (day.columns.length <= 1) {
            alert('কমপক্ষে একটি কলাম বা পিরিয়ড থাকতে হবে!');
            return;
        }
        day.columns.pop();
        day.rows.forEach(row => {
            row.periods.pop();
        });
    },

    addClassRow(rowTitle = '') {
        const day = this.currentClassDay();
        const newPeriods = [];
        day.columns.forEach((col, idx) => {
            newPeriods.push({
                period_number: col.number || (idx + 1),
                start_time: col.start_time,
                end_time: col.end_time,
                subject: '',
                teacher_name: '',
                room: '',
                is_break: !!col.is_break
            });
        });
        const title = rowTitle || ('শাখা ' + String.fromCharCode(65 + day.rows.length));
        day.rows.push({
            row_title: title,
            periods: newPeriods
        });
    },

    removeClassRow(index) {
        const day = this.currentClassDay();
        if (confirm('আপনি কি এই রো/সারিটি মুছে ফেলতে চান?')) {
            day.rows.splice(index, 1);
        }
    },

    deleteEmptyClassRows() {
        const day = this.currentClassDay();
        const prevCount = day.rows.length;
        day.rows = day.rows.filter(row => {
            const hasContent = row.periods && row.periods.some(p => (p.subject && p.subject.trim().length > 0) || (p.teacher_name && p.teacher_name.trim().length > 0) || p.is_break);
            return hasContent;
        });
        const removed = prevCount - day.rows.length;
        if (removed > 0) {
            alert(removed + ' টি সম্পূর্ণ ফাঁকা রো ডিলিট করা হয়েছে!');
        } else {
            alert('কোনো সম্পূর্ণ ফাঁকা রো পাওয়া যায়নি।');
        }
    },

    clearClassDay() {
        if (confirm('আপনি কি ' + this.selectedStudentClass + ' এর ' + this.getDayBn(this.activeDay) + '-এর সকল তথ্য মুছে সম্পূর্ণ ফাঁকা করতে চান?')) {
            this.currentClassDay().rows = [];
        }
    },

    generateBlankClassGrid(colsCount, rowsCount) {
        colsCount = Math.max(1, Math.min(12, parseInt(colsCount) || 5));
        rowsCount = Math.max(1, Math.min(15, parseInt(rowsCount) || 1));
        const day = this.currentClassDay();
        day.columns = [];
        for (let c = 1; c <= colsCount; c++) {
            const isBreak = (c === 4);
            day.columns.push({
                number: c,
                start_time: (8 + c) + ':00 AM',
                end_time: (8 + c) + ':45 AM',
                label: isBreak ? 'টিফিন বিরতি' : (c + 'ম পিরিয়ড'),
                is_break: isBreak
            });
        }
        day.rows = [];
        for (let r = 1; r <= rowsCount; r++) {
            this.addClassRow('শাখা ' + String.fromCharCode(64 + r));
        }
    },

    countFilledClassCells() {
        const day = this.currentClassDay();
        let count = 0;
        day.rows.forEach(r => {
            (r.periods || []).forEach(p => {
                if ((p.subject && p.subject.trim()) || p.is_break) count++;
            });
        });
        return count;
    },

    saveClassRoutine() {
        this.saving = true;
        this.$refs.gridJsonInput.value = JSON.stringify(this.currentClassDay().rows);
        this.$refs.gridDayInput.value = this.activeDay;
        this.$refs.gridClassInput.value = this.selectedStudentClass;
        this.$refs.gridSaveForm.submit();
    },

    // ==========================================
    // TEACHER ROUTINE METHODS (শিক্ষক রুটিন)
    // ==========================================
    teacherDay() {
        if (!this.teacherGrid[this.activeDay]) {
            this.teacherGrid[this.activeDay] = {
                day_name_bn: this.getDayBn(this.activeDay),
                columns: [
                    { number: 1, start_time: '09:00 AM', end_time: '09:45 AM', label: '১ম পিরিয়ড', is_break: false },
                    { number: 2, start_time: '09:45 AM', end_time: '10:30 AM', label: '২য় পিরিয়ড', is_break: false },
                    { number: 3, start_time: '10:30 AM', end_time: '11:15 AM', label: '৩য় পিরিয়ড', is_break: false },
                    { number: 4, start_time: '11:15 AM', end_time: '11:45 AM', label: 'টিফিন বিরতি', is_break: true },
                    { number: 5, start_time: '11:45 AM', end_time: '12:30 PM', label: '৪র্থ পিরিয়ড', is_break: false }
                ],
                rows: []
            };
        }
        return this.teacherGrid[this.activeDay];
    },

    addTeacherColumn() {
        const day = this.teacherDay();
        const nextNum = day.columns.length + 1;
        const newCol = {
            number: nextNum,
            start_time: '12:30 PM',
            end_time: '01:15 PM',
            label: nextNum + 'ম পিরিয়ড',
            is_break: false
        };
        day.columns.push(newCol);

        day.rows.forEach(row => {
            row.periods.push({
                period_number: nextNum,
                start_time: newCol.start_time,
                end_time: newCol.end_time,
                subject: '',
                class_name: '',
                teacher_name: row.teacher_name || '',
                room: '',
                is_break: false
            });
        });
    },

    removeTeacherColumn() {
        const day = this.teacherDay();
        if (day.columns.length <= 1) {
            alert('কমপক্ষে একটি কলাম বা পিরিয়ড থাকতে হবে!');
            return;
        }
        day.columns.pop();
        day.rows.forEach(row => {
            row.periods.pop();
        });
    },

    addTeacherRow(teacherName = '') {
        const day = this.teacherDay();
        const newPeriods = [];

        day.columns.forEach((col, idx) => {
            newPeriods.push({
                period_number: col.number || (idx + 1),
                start_time: col.start_time,
                end_time: col.end_time,
                subject: '',
                class_name: '',
                teacher_name: teacherName,
                room: '',
                is_break: !!col.is_break
            });
        });

        day.rows.push({
            teacher_name: teacherName,
            periods: newPeriods
        });
    },

    removeTeacherRow(index) {
        const day = this.teacherDay();
        if (confirm('আপনি কি এই রো/শিক্ষকটি মুছে ফেলতে চান?')) {
            day.rows.splice(index, 1);
        }
    },

    deleteEmptyTeacherRows() {
        const day = this.teacherDay();
        const prevCount = day.rows.length;
        day.rows = day.rows.filter(row => {
            const hasName = row.teacher_name && row.teacher_name.trim().length > 0;
            const hasContent = row.periods && row.periods.some(p => (p.subject && p.subject.trim().length > 0) || (p.class_name && p.class_name.trim().length > 0) || p.is_break);
            return hasName || hasContent;
        });
        const removed = prevCount - day.rows.length;
        if (removed > 0) {
            alert(removed + ' টি সম্পূর্ণ ফাঁকা রো ডিলিট করা হয়েছে!');
        } else {
            alert('কোনো সম্পূর্ণ ফাঁকা রো পাওয়া যায়নি।');
        }
    },

    clearTeacherCurrentDay() {
        if (confirm('আপনি কি ' + this.getDayBn(this.activeDay) + '-এর শিক্ষক রুটিনের সকল ঘর মুছে সম্পূর্ণ ফাঁকা করতে চান?')) {
            this.teacherDay().rows = [];
        }
    },

    generateBlankTeacherGrid(colsCount, rowsCount) {
        colsCount = Math.max(1, Math.min(12, parseInt(colsCount) || 5));
        rowsCount = Math.max(1, Math.min(25, parseInt(rowsCount) || 4));

        const day = this.teacherDay();
        day.columns = [];
        for (let c = 1; c <= colsCount; c++) {
            const isBreak = (c === 4);
            day.columns.push({
                number: c,
                start_time: (8 + c) + ':00 AM',
                end_time: (8 + c) + ':45 AM',
                label: isBreak ? 'টিফিন বিরতি' : (c + 'ম পিরিয়ড'),
                is_break: isBreak
            });
        }

        day.rows = [];
        for (let r = 1; r <= rowsCount; r++) {
            this.addTeacherRow('');
        }
    },

    countFilledTeacherCells() {
        const day = this.teacherDay();
        let count = 0;
        day.rows.forEach(r => {
            (r.periods || []).forEach(p => {
                if ((p.subject && p.subject.trim()) || p.is_break) count++;
            });
        });
        return count;
    },

    saveTeacherGrid() {
        this.saving = true;
        this.$refs.gridJsonInput.value = JSON.stringify(this.teacherDay().rows);
        this.$refs.gridDayInput.value = this.activeDay;
        this.$refs.gridClassInput.value = '';
        this.$refs.gridSaveForm.submit();
    }
}">
    <!-- ============================================================== -->
    <!-- TOP HEADER & CONTROLS -->
    <!-- ============================================================== -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <!-- Title: শিক্ষকগণ ও স্টুডেন্টগণ এর সাপ্তাহিক ক্লাস রুটিন -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center text-lg shadow-md shadow-blue-500/20">
                        <i class="fa-solid fa-calendar-days"></i>
                    </span>
                    <span>শিক্ষকগণ ও স্টুডেন্টগণ এর সাপ্তাহিক ক্লাস রুটিন</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 pl-13">
                    শিক্ষক ও শিক্ষার্থীদের পৃথক সাপ্তাহিক ক্লাস রুটিন ব্যবস্থাপনা, ডায়নামিক ক্লাস বাটন তৈরি, কলাম ও রো যোগ/ডিলিট এবং সম্পূর্ণ সময়সূচী সংরক্ষণ করুন।
                </p>
            </div>

            <!-- Global Action Buttons (Save, JSON, Download, Schema) -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Save Grid Button (Primary CTA) -->
                <button type="button" @click="mainTab === 'teacher' ? saveTeacherGrid() : saveClassRoutine()" :disabled="saving" class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition">
                    <i class="fa-solid fa-floppy-disk text-xs" :class="saving ? 'fa-spin' : ''"></i>
                    <span x-text="saving ? 'সংরক্ষণ হচ্ছে...' : 'ছক সংরক্ষণ করুন'"></span>
                </button>

                <!-- JSON Editor Button -->
                <button @click="jsonModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3.5 py-2.5 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                    <i class="fa-solid fa-code"></i>
                    <span>JSON এডিটর</span>
                </button>

                <!-- JSON Download -->
                <a href="{{ route('institution.routines.export-json') }}" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs px-3.5 py-2.5 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                    <i class="fa-solid fa-download"></i>
                    <span>ডাউনলোড</span>
                </a>

                <!-- Schema Guide -->
                <button @click="schemaModal = true" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3 py-2.5 rounded-xl flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-circle-question text-emerald-600"></i>
                    <span>কাঠামো</span>
                </button>
            </div>
        </div>

        <!-- The 2 Core Routine Buttons: 'শিক্ষক রুটিন' এবং 'স্টুডেন্ট রুটিন' (শিক্ষক রুটিন ডিফল্ট হিসেবে সক্রিয়) -->
        <div class="flex items-center gap-2 p-1.5 bg-slate-100 rounded-2xl border border-slate-200 w-full sm:w-auto inline-flex">
            <!-- Button 1: শিক্ষক রুটিন (ডিফল্ট সক্রিয়) -->
            <button type="button" @click="mainTab = 'teacher'" 
                    :class="mainTab === 'teacher' ? 'bg-white text-blue-700 shadow-sm font-bold ring-1 ring-blue-300' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all">
                <i class="fa-solid fa-chalkboard-user text-blue-600 text-sm"></i>
                <span>শিক্ষক রুটিন</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full" :class="mainTab === 'teacher' ? 'bg-blue-100 text-blue-800 font-bold' : 'bg-slate-200 text-slate-600'">সক্রিয়</span>
            </button>

            <!-- Button 2: স্টুডেন্ট রুটিন -->
            <button type="button" @click="mainTab = 'student'" 
                    :class="mainTab === 'student' ? 'bg-white text-emerald-700 shadow-sm font-bold ring-1 ring-emerald-300' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all">
                <i class="fa-solid fa-graduation-cap text-emerald-600 text-sm"></i>
                <span>স্টুডেন্ট রুটিন</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold" x-show="mainTab === 'student'">সক্রিয়</span>
            </button>
        </div>
    </div>

    <!-- Hidden form for saving grid -->
    <form x-ref="gridSaveForm" action="{{ route('institution.routines.save-grid') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="day" x-ref="gridDayInput">
        <input type="hidden" name="class_name" x-ref="gridClassInput">
        <input type="hidden" name="grid_json" x-ref="gridJsonInput">
    </form>

    <!-- ============================================================== -->
    <!-- TAB 1: শিক্ষক রুটিন (Teacher Routine Grid - Default Active)    -->
    <!-- ============================================================== -->
    <div x-show="mainTab === 'teacher'" class="space-y-6">
        <!-- Subtitle & Badge -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span>শিক্ষক ক্লাস রুটিন গ্রিড</span>
                </span>
                <span class="text-xs text-slate-500 hidden sm:inline">শিক্ষকদের নামভিত্তিক সময়সূচী, ক্লাস, বিষয় ও রুম নম্বর ব্যবস্থাপনা</span>
            </div>
        </div>

        <!-- Day Navigation Tabs (শনিবার থেকে শুরু করে সপ্তাহের দিনগুলো) -->
        <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-2">
            @php
                $dayList = [
                    'Saturday' => ['bn' => 'শনিবার', 'icon' => 'fa-calendar-day'],
                    'Sunday' => ['bn' => 'রবিবার', 'icon' => 'fa-calendar-day'],
                    'Monday' => ['bn' => 'সোমবার', 'icon' => 'fa-calendar-day'],
                    'Tuesday' => ['bn' => 'মঙ্গলবার', 'icon' => 'fa-calendar-day'],
                    'Wednesday' => ['bn' => 'বুধবার', 'icon' => 'fa-calendar-day'],
                    'Thursday' => ['bn' => 'বৃহস্পতিবার', 'icon' => 'fa-calendar-day'],
                ];
            @endphp

            @foreach($dayList as $dEn => $dMeta)
            <button type="button" @click="activeDay = '{{ $dEn }}'" 
                    :class="activeDay === '{{ $dEn }}' ? 'bg-blue-600 text-white shadow-md font-bold ring-2 ring-blue-400' : 'text-slate-700 hover:bg-slate-100 font-semibold'" 
                    class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 flex-1 sm:flex-none justify-center">
                <i class="fa-solid {{ $dMeta['icon'] }} text-xs" :class="activeDay === '{{ $dEn }}' ? 'text-white' : 'text-slate-400'"></i>
                <span>{{ $dMeta['bn'] }}</span>
                <span class="text-[10px] font-mono opacity-80">({{ $dEn }})</span>
            </button>
            @endforeach
        </div>

        <!-- Dynamic Counter Card for Teacher Routine -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-4 rounded-3xl border border-slate-200 shadow-xs">
            <div class="flex items-center gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-table-columns"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট কলাম (পিরিয়ড)</div>
                    <div class="text-lg font-extrabold text-blue-900 leading-tight">
                        <span x-text="teacherDay().columns.length"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-indigo-50/60 rounded-2xl border border-indigo-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-bars"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট রো (শিক্ষক)</div>
                    <div class="text-lg font-extrabold text-indigo-900 leading-tight">
                        <span x-text="teacherDay().rows.length"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-table-cells"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট ঘর (সেল)</div>
                    <div class="text-lg font-extrabold text-emerald-900 leading-tight">
                        <span x-text="teacherDay().columns.length * teacherDay().rows.length"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-purple-50/60 rounded-2xl border border-purple-100">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">পূরণকৃত ঘর</div>
                    <div class="text-lg font-extrabold text-purple-900 leading-tight">
                        <span x-text="countFilledTeacherCells()"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher Routine Active Day Container -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-5">
            <!-- Day Toolbar -->
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-base shadow-2xs">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <span>শিক্ষক রুটিন:</span>
                            <span class="text-blue-700 font-extrabold" x-text="getDayBn(activeDay)"></span>
                            <span class="text-xs font-mono font-normal text-slate-500" x-text="'(' + activeDay + ')'"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            <span x-show="teacherDay().rows.length > 0">
                                মোট <strong class="text-blue-700" x-text="teacherDay().rows.length"></strong> জন শিক্ষক ও <strong class="text-blue-700" x-text="teacherDay().columns.length"></strong> টি পিরিয়ড কলাম
                            </span>
                            <span x-show="teacherDay().rows.length === 0" class="text-amber-600 font-medium">
                                এই দিনে এখনো কোনো শিক্ষকের রুটিন যোগ করা হয়নি (সম্পূর্ণ ফাঁকা)
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Controls -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <!-- Quick Teacher Name Input -->
                    <div class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl p-1">
                        <i class="fa-solid fa-user-tie text-blue-600 pl-2 text-xs"></i>
                        <input type="text" x-model="newTeacherName" list="master-teachers-datalist" placeholder="শিক্ষকের নাম লিখুন" class="bg-white border border-slate-200 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-blue-500 text-slate-800 w-36 sm:w-44 font-semibold">
                        <button type="button" @click="if(newTeacherName.trim()){ addTeacherRow(newTeacherName.trim()); newTeacherName = ''; } else { alert('অনুগ্রহ করে শিক্ষকের নাম লিখুন!'); }" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1 rounded-lg text-xs transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>রো যোগ</span>
                        </button>
                    </div>

                    <!-- Column Controls -->
                    <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                        <span class="text-[11px] font-bold text-slate-600 px-2 flex items-center gap-1">
                            <i class="fa-solid fa-table-columns text-blue-600"></i>
                            <span>কলাম:</span>
                        </span>
                        <button type="button" @click="addTeacherColumn()" class="bg-white hover:bg-blue-50 text-blue-700 font-bold px-2.5 py-1 rounded-lg border border-slate-200 hover:border-blue-300 transition flex items-center gap-1 shadow-2xs" title="নতুন কলাম বা পিরিয়ড যোগ করুন">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>কলাম যোগ</span>
                        </button>
                        <button type="button" @click="removeTeacherColumn()" class="text-slate-500 hover:text-rose-600 px-2 py-1 transition" title="শেষ কলামটি মুছুন">
                            <i class="fa-solid fa-minus text-[10px]"></i>
                        </button>
                    </div>

                    <!-- Add Blank Row Button -->
                    <button type="button" @click="addTeacherRow('')" class="bg-white hover:bg-blue-50 text-blue-700 font-bold px-3 py-2 rounded-xl border border-blue-300 hover:border-blue-400 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>+ রো যোগ</span>
                    </button>

                    <!-- Delete Empty Rows Button -->
                    <button type="button" @click="deleteEmptyTeacherRows()" class="bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold px-3 py-2 rounded-xl border border-amber-300 transition flex items-center gap-1.5 shadow-2xs" title="যেসব সারিতে কোনো তথ্য নেই সেগুলো মুছে ফেলুন">
                        <i class="fa-solid fa-eraser text-xs"></i>
                        <span>ফাঁকা ঘর ডিলিট</span>
                    </button>

                    <!-- Clear Day Button -->
                    <button type="button" @click="clearTeacherCurrentDay()" class="bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 font-bold px-3 py-2 rounded-xl border border-rose-200 hover:border-rose-300 transition flex items-center gap-1.5 shadow-2xs" title="এই দিনের সকল শিক্ষক রো মুছে ফাঁকা করুন">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>দিন ফাঁকা করুন</span>
                    </button>

                    <!-- Quick Grid Dimension -->
                    <div class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2 py-1" x-data="{ cInput: 5, rInput: 4 }">
                        <span class="text-[10px] text-slate-500 font-semibold">ছক:</span>
                        <input type="number" x-model="cInput" min="1" max="10" class="w-9 text-center font-mono text-xs bg-white border border-slate-200 rounded p-0.5" title="কলাম">
                        <span class="text-slate-400">×</span>
                        <input type="number" x-model="rInput" min="1" max="15" class="w-9 text-center font-mono text-xs bg-white border border-slate-200 rounded p-0.5" title="রো">
                        <button type="button" @click="generateBlankTeacherGrid(cInput, rInput)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-2 py-0.5 rounded text-[10px] transition">
                            তৈরি
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State for Teacher Routine -->
            <div x-show="teacherDay().rows.length === 0" class="text-center py-12 px-4 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-base">
                        <span x-text="getDayBn(activeDay)"></span> এর কোনো শিক্ষক রুটিন এখনো সেট করা নেই (সম্পূর্ণ ফাঁকা)
                    </h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                        নিচে শিক্ষকের নাম লিখে একটি খালি রো তৈরি করুন অথবা কলাম ও রো যোগ করে সরাসরি নিজেদের ইচ্ছামতো তথ্য টাইপ করুন।
                    </p>
                </div>

                <div class="max-w-md mx-auto flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm">
                    <input type="text" x-model="newTeacherName" list="master-teachers-datalist" placeholder="শিক্ষকের নাম (যেমন: মোঃ রফিকুল ইসলাম)" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-blue-500 text-slate-800 font-semibold">
                    <button type="button" @click="if(newTeacherName.trim()){ addTeacherRow(newTeacherName.trim()); newTeacherName = ''; } else { alert('অনুগ্রহ করে শিক্ষকের নাম লিখুন!'); }" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>রো তৈরি করুন</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                    <button type="button" @click="addTeacherRow('')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2 rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus text-blue-600"></i>
                        <span>+ ফাঁকা সারি যোগ</span>
                    </button>
                    <button type="button" @click="generateBlankTeacherGrid(5, 4)" class="bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs px-4 py-2 rounded-xl border border-slate-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-table-cells text-blue-600"></i>
                        <span>৫ কলাম × ৪ রো ফাঁকা ছক খুলুন</span>
                    </button>
                </div>
            </div>

            <!-- Teacher Table Grid -->
            <div x-show="teacherDay().rows.length > 0" class="overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50/40 p-1">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 border-b border-slate-200">
                            <th class="p-3.5 font-bold text-xs text-slate-900 min-w-[180px] sticky left-0 bg-slate-100 z-10 border-r border-slate-200">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-tie text-blue-600"></i>
                                    <span>শিক্ষক \ পিরিয়ড</span>
                                </div>
                            </th>

                            <template x-for="(col, colIndex) in teacherDay().columns" :key="'t-col-' + colIndex">
                                <th class="p-3 font-semibold min-w-[200px] border-r border-slate-200 align-top transition-all" 
                                    :class="col.is_break ? 'bg-amber-100/70 text-amber-900' : 'bg-slate-100 text-slate-800'">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between">
                                            <input type="text" x-model="col.label" 
                                                   class="font-bold text-xs bg-transparent border-b border-dashed border-slate-300 focus:border-blue-500 focus:outline-none w-full" 
                                                   placeholder="পিরিয়ডের নাম">
                                            <button type="button" @click="col.is_break = !col.is_break" 
                                                    :class="col.is_break ? 'text-amber-700' : 'text-slate-400 hover:text-slate-600'" 
                                                    class="ml-1 text-[11px]" title="টিফিন বিরতি হিসেবে চিহ্নিত করুন">
                                                <i class="fa-solid fa-mug-hot"></i>
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-1 text-[10px] text-slate-600 font-mono">
                                            <input type="text" x-model="col.start_time" class="w-16 bg-white px-1.5 py-0.5 rounded border border-slate-200 text-center font-mono focus:border-blue-500 focus:outline-none" placeholder="09:00 AM">
                                            <span>-</span>
                                            <input type="text" x-model="col.end_time" class="w-16 bg-white px-1.5 py-0.5 rounded border border-slate-200 text-center font-mono focus:border-blue-500 focus:outline-none" placeholder="09:45 AM">
                                        </div>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(row, rowIndex) in teacherDay().rows" :key="'t-row-' + rowIndex">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="p-3 sticky left-0 bg-white hover:bg-slate-50 z-10 border-r border-slate-200 align-top min-w-[180px] shadow-xs">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between gap-1">
                                            <input type="text" x-model="row.teacher_name" 
                                                   list="master-teachers-datalist"
                                                   class="font-bold text-xs text-slate-900 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:border-blue-500 focus:bg-white focus:outline-none w-full shadow-2xs" 
                                                   placeholder="শিক্ষকের নাম">
                                            <button type="button" @click="removeTeacherRow(rowIndex)" class="text-slate-300 hover:text-rose-500 p-1 transition" title="এই শিক্ষকটি মুছুন">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono">শিক্ষক ক্রম #<span x-text="rowIndex + 1"></span></span>
                                    </div>
                                </td>

                                <template x-for="(period, periodIndex) in row.periods" :key="'t-cell-' + rowIndex + '-' + periodIndex">
                                    <td class="p-2 border-r border-slate-200 align-top transition-all min-w-[190px]"
                                        :class="period.is_break ? 'bg-amber-50/50' : 'bg-white'">
                                        <div class="rounded-xl border p-2.5 transition-all duration-150 flex flex-col justify-between gap-2 shadow-2xs break-words whitespace-normal"
                                             :class="period.is_break ? 'bg-amber-50 border-amber-200' : 'bg-white border-slate-200 hover:border-blue-300 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-400'">
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 font-mono" x-text="'ঘর ' + (periodIndex + 1)"></span>
                                                    <button type="button" 
                                                            @click="period.is_break = !period.is_break; if(period.is_break && !period.subject) period.subject = 'টিফিন বিরতি';" 
                                                            :class="period.is_break ? 'text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded' : 'text-slate-300 hover:text-slate-600'" 
                                                            class="text-[10px] transition" 
                                                            title="বিরতি হিসেবে চিহ্নিত করুন">
                                                        <i class="fa-solid fa-mug-hot"></i>
                                                    </button>
                                                </div>

                                                <input type="text" x-model="period.subject" 
                                                       class="w-full font-bold text-xs text-slate-900 bg-transparent border-b border-dashed border-slate-300 focus:border-blue-500 focus:outline-none pb-0.5" 
                                                       :placeholder="period.is_break ? 'বিরতির নাম' : 'বিষয়ের নাম লিখুন'">
                                            </div>

                                            <div class="space-y-0.5" x-show="!period.is_break">
                                                <div class="flex items-center gap-1 text-[11px] text-slate-600">
                                                    <i class="fa-solid fa-graduation-cap text-slate-400 text-[10px]"></i>
                                                    <input type="text" x-model="period.class_name" 
                                                           list="master-classes-datalist"
                                                           class="w-full text-[11px] bg-transparent border-b border-dashed border-slate-200 focus:border-blue-500 focus:outline-none pb-0.5 placeholder:text-slate-400 text-slate-700" 
                                                           placeholder="শ্রেণি (যেমন: নবম)">
                                                </div>
                                            </div>

                                            <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between gap-1 text-[10px] text-slate-500 font-mono">
                                                <div class="flex items-center gap-0.5" x-show="!period.is_break">
                                                    <i class="fa-solid fa-door-open text-slate-400 text-[9px]"></i>
                                                    <input type="text" x-model="period.room" 
                                                           class="w-16 bg-transparent border-b border-dashed border-slate-200 focus:border-blue-500 focus:outline-none placeholder:text-slate-300 text-slate-600 text-[10px]" 
                                                           placeholder="রুম নং">
                                                </div>

                                                <button type="button" 
                                                        @click="period.subject = ''; period.class_name = ''; period.room = ''; period.is_break = false;" 
                                                        class="text-slate-300 hover:text-rose-500 text-[10px] ml-auto transition" 
                                                        title="ঘর খালি করুন">
                                                    <i class="fa-solid fa-eraser"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Save Banner -->
            <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs border-t border-slate-100">
                <div class="text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-blue-600"></i>
                    <span><strong x-text="getDayBn(activeDay)"></strong>-এর তথ্যাদি সম্পন্ন হলে <strong>'ছক সংরক্ষণ করুন'</strong> বাটনে চাপ দিন।</span>
                </div>

                <button type="button" @click="saveTeacherGrid()" :disabled="saving" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2 justify-center">
                    <i class="fa-solid fa-floppy-disk" :class="saving ? 'fa-spin' : ''"></i>
                    <span x-text="saving ? 'সংরক্ষণ হচ্ছে...' : 'ছক সংরক্ষণ করুন (Save ' + getDayBn(activeDay) + ')'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: স্টুডেন্ট রুটিন (Student Class-Specific Routine Grid)   -->
    <!-- ============================================================== -->
    <div x-show="mainTab === 'student'" class="space-y-6" style="display: none;">
        <!-- Subtitle & Badge -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>স্টুডেন্ট ক্লাস রুটিন গ্রিড</span>
                </span>
                <span class="text-xs text-slate-500 hidden sm:inline">যেকোনো ক্লাসের জন্য পৃথক ডায়নামিক বাটন তৈরি ও আলাদা সাপ্তাহিক রুটিন ব্যবস্থাপনা</span>
            </div>
        </div>

        <!-- Dynamic Class Button Creator & Class Selector Bar -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-tags text-emerald-600"></i>
                        <span>ক্লাস বাটন নির্বাচন করুন ও নতুন বাটন তৈরি করুন:</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        নিচের যেকোনো ক্লাসের বাটনে ক্লিক করলে সেই ক্লাসের জন্য আলাদা সাপ্তাহিক রুটিন লোড হবে। নতুন কোনো ক্লাসের নাম লিখে বাটন যোগ করতে ডানপাশের ইনপুট ব্যবহার করুন।
                    </p>
                </div>

                <!-- Input to dynamically create new class button -->
                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-200 w-full sm:w-auto">
                    <div class="flex items-center gap-2 flex-1 sm:w-72 pl-2">
                        <i class="fa-solid fa-graduation-cap text-emerald-600 text-xs"></i>
                        <input type="text" x-model="newCustomClassName" 
                               @keydown.enter.prevent="addNewClassButton()"
                               list="master-classes-datalist"
                               placeholder="নতুন ক্লাসের নাম (যেমন: ক্লাস নাইন, অনার্স ফার্স্ট ইয়ার)..." 
                               class="bg-transparent border-none text-xs text-slate-900 font-bold focus:outline-none w-full placeholder:text-slate-400 placeholder:font-normal">
                    </div>
                    <button type="button" @click="addNewClassButton()" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>বাটন তৈরি করুন</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Class Buttons (Pill Grid) -->
            <div class="flex flex-wrap items-center gap-2">
                <template x-for="(clsName, idx) in studentClassList" :key="clsName">
                    <div class="inline-flex items-center rounded-2xl transition-all duration-150 border"
                         :class="selectedStudentClass === clsName ? 'bg-emerald-600 text-white border-emerald-600 shadow-md ring-2 ring-emerald-300 font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'">
                        <button type="button" @click="selectStudentClass(clsName)" class="px-4 py-2 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-xs" :class="selectedStudentClass === clsName ? 'text-white' : 'text-emerald-600'"></i>
                            <span x-text="clsName"></span>
                            <span x-show="selectedStudentClass === clsName" class="text-[9px] bg-white/20 px-1.5 py-0.2 rounded font-mono">সক্রিয়</span>
                        </button>

                        <button type="button" @click.stop="removeClassButton(idx, clsName)" 
                                x-show="studentClassList.length > 1"
                                class="pr-2.5 pl-1 py-2 text-[11px] opacity-60 hover:opacity-100 hover:text-rose-300 transition" 
                                title="এই ক্লাস বাটনটি মুছুন">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Active Class Name Banner -->
        <div class="bg-gradient-to-r from-emerald-700 to-teal-700 text-white p-5 rounded-3xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md text-white flex items-center justify-center text-xl font-bold shadow-inner">
                    <i class="fa-solid fa-screen-users"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wider font-mono text-emerald-200 font-semibold">নির্বাচিত ক্লাসের রুটিন</span>
                        <span class="text-[10px] bg-emerald-500/30 text-white px-2 py-0.5 rounded-full border border-emerald-400/30">সাপ্তাহিক শিডিউল</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2">
                        <span x-text="selectedStudentClass"></span>
                        <span class="text-xs font-normal opacity-90">- এর সাপ্তাহিক ক্লাস রুটিন</span>
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs bg-white/10 px-3 py-1.5 rounded-xl backdrop-blur-sm border border-white/10 flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-check text-emerald-300"></i>
                    <span>দিন: <strong x-text="getDayBn(activeDay)"></strong></span>
                </span>
            </div>
        </div>

        <!-- Day Navigation Tabs for Selected Class (শনিবার থেকে বৃহস্পতিবার) -->
        <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-2">
            @foreach($dayList as $dEn => $dMeta)
            <button type="button" @click="activeDay = '{{ $dEn }}'" 
                    :class="activeDay === '{{ $dEn }}' ? 'bg-emerald-600 text-white shadow-md font-bold ring-2 ring-emerald-400' : 'text-slate-700 hover:bg-slate-100 font-semibold'" 
                    class="px-4 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 flex-1 sm:flex-none justify-center">
                <i class="fa-solid {{ $dMeta['icon'] }} text-xs" :class="activeDay === '{{ $dEn }}' ? 'text-white' : 'text-slate-400'"></i>
                <span>{{ $dMeta['bn'] }}</span>
                <span class="text-[10px] font-mono opacity-80">({{ $dEn }})</span>
            </button>
            @endforeach
        </div>

        <!-- Dynamic Counter Card for Selected Class Routine -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-4 rounded-3xl border border-slate-200 shadow-xs">
            <div class="flex items-center gap-3 p-3 bg-emerald-50/60 rounded-2xl border border-emerald-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-table-columns"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট কলাম (পিরিয়ড)</div>
                    <div class="text-lg font-extrabold text-emerald-900 leading-tight">
                        <span x-text="currentClassDay().columns.length"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-teal-50/60 rounded-2xl border border-teal-100">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-bars"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট রো (শাখা/সারি)</div>
                    <div class="text-lg font-extrabold text-teal-900 leading-tight">
                        <span x-text="currentClassDay().rows.length"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-blue-50/60 rounded-2xl border border-blue-100">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-table-cells"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">মোট ঘর (সেল)</div>
                    <div class="text-lg font-extrabold text-blue-900 leading-tight">
                        <span x-text="currentClassDay().columns.length * currentClassDay().rows.length"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-purple-50/60 rounded-2xl border border-purple-100">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">পূরণকৃত ঘর</div>
                    <div class="text-lg font-extrabold text-purple-900 leading-tight">
                        <span x-text="countFilledClassCells()"></span> <span class="text-xs font-normal text-slate-600">টি</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Class Day Routine Container -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-5">
            <!-- Day Toolbar: Controls to add column, add row, delete empty cells -->
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <!-- Day & Class Info Badge -->
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base shadow-2xs">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <span class="text-emerald-700 font-extrabold" x-text="selectedStudentClass"></span>
                            <span>•</span>
                            <span class="text-slate-800 font-bold" x-text="getDayBn(activeDay)"></span>
                            <span class="text-xs font-mono font-normal text-slate-500" x-text="'(' + activeDay + ')'"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            <span x-show="currentClassDay().rows.length > 0">
                                মোট <strong class="text-emerald-700" x-text="currentClassDay().rows.length"></strong> টি সারি ও <strong class="text-emerald-700" x-text="currentClassDay().columns.length"></strong> টি পিরিয়ড কলাম
                            </span>
                            <span x-show="currentClassDay().rows.length === 0" class="text-amber-600 font-medium">
                                এই দিনে এখনো কোনো রুটিন তৈরি করা হয়নি (সম্পূর্ণ ফাঁকা)
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Controls -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <!-- Column Controls -->
                    <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                        <span class="text-[11px] font-bold text-slate-600 px-2 flex items-center gap-1">
                            <i class="fa-solid fa-table-columns text-emerald-600"></i>
                            <span>কলাম:</span>
                        </span>
                        <button type="button" @click="addClassColumn()" class="bg-white hover:bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded-lg border border-slate-200 hover:border-emerald-300 transition flex items-center gap-1 shadow-2xs" title="নতুন কলাম বা পিরিয়ড যোগ করুন">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>কলাম যোগ</span>
                        </button>
                        <button type="button" @click="removeClassColumn()" class="text-slate-500 hover:text-rose-600 px-2 py-1 transition" title="শেষ কলামটি মুছুন">
                            <i class="fa-solid fa-minus text-[10px]"></i>
                        </button>
                    </div>

                    <!-- Add Blank Row Button -->
                    <button type="button" @click="addClassRow('')" class="bg-white hover:bg-emerald-50 text-emerald-700 font-bold px-3 py-2 rounded-xl border border-emerald-300 hover:border-emerald-400 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>+ রো যোগ</span>
                    </button>

                    <!-- Delete Empty Rows Button (ফাঁকা ঘর ডিলিট) -->
                    <button type="button" @click="deleteEmptyClassRows()" class="bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold px-3 py-2 rounded-xl border border-amber-300 transition flex items-center gap-1.5 shadow-2xs" title="যেসব সারিতে কোনো তথ্য নেই সেগুলো মুছে ফেলুন">
                        <i class="fa-solid fa-eraser text-xs"></i>
                        <span>ফাঁকা ঘর ডিলিট</span>
                    </button>

                    <!-- Clear Day Button -->
                    <button type="button" @click="clearClassDay()" class="bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 font-bold px-3 py-2 rounded-xl border border-rose-200 hover:border-rose-300 transition flex items-center gap-1.5 shadow-2xs" title="এই দিনের সকল তথ্য মুছে ফাঁকা করুন">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>দিন ফাঁকা করুন</span>
                    </button>

                    <!-- Quick Blank Grid Generator -->
                    <div class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2 py-1" x-data="{ cInput: 5, rInput: 2 }">
                        <span class="text-[10px] text-slate-500 font-semibold">ছক:</span>
                        <input type="number" x-model="cInput" min="1" max="10" class="w-9 text-center font-mono text-xs bg-white border border-slate-200 rounded p-0.5" title="কলাম">
                        <span class="text-slate-400">×</span>
                        <input type="number" x-model="rInput" min="1" max="15" class="w-9 text-center font-mono text-xs bg-white border border-slate-200 rounded p-0.5" title="রো">
                        <button type="button" @click="generateBlankClassGrid(cInput, rInput)" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2 py-0.5 rounded text-[10px] transition">
                            তৈরি
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State for Class Routine -->
            <div x-show="currentClassDay().rows.length === 0" class="text-center py-12 px-4 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-base">
                        <span x-text="selectedStudentClass"></span> এর <span x-text="getDayBn(activeDay)"></span>-এ কোনো রুটিন নেই (সম্পূর্ণ ফাঁকা)
                    </h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                        নিচে 'ফাঁকা সারি যোগ' বাটনে চাপ দিয়ে অথবা কলাম ও রো বৃদ্ধি করে রুটিন সাজানো শুরু করুন।
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                    <button type="button" @click="addClassRow('শাখা - ক')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ ফাঁকা সারি যোগ করুন</span>
                    </button>
                    <button type="button" @click="generateBlankClassGrid(5, 2)" class="bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-xl border border-slate-300 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-table-cells text-emerald-600"></i>
                        <span>৫ কলাম × ২ রো ফাঁকা ছক খুলুন</span>
                    </button>
                </div>
            </div>

            <!-- Interactive Table Grid for Selected Class -->
            <div x-show="currentClassDay().rows.length > 0" class="overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50/40 p-1">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 border-b border-slate-200">
                            <!-- Corner Header: Row Title / Section -->
                            <th class="p-3.5 font-bold text-xs text-slate-900 min-w-[170px] sticky left-0 bg-slate-100 z-10 border-r border-slate-200">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-graduation-cap text-emerald-600"></i>
                                    <span>শাখা / সারি \ পিরিয়ড</span>
                                </div>
                            </th>

                            <!-- Dynamic Columns -->
                            <template x-for="(col, colIndex) in currentClassDay().columns" :key="'sc-col-' + colIndex">
                                <th class="p-3 font-semibold min-w-[200px] border-r border-slate-200 align-top transition-all" 
                                    :class="col.is_break ? 'bg-amber-100/70 text-amber-900' : 'bg-slate-100 text-slate-800'">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between">
                                            <input type="text" x-model="col.label" 
                                                   class="font-bold text-xs bg-transparent border-b border-dashed border-slate-300 focus:border-emerald-500 focus:outline-none w-full" 
                                                   placeholder="পিরিয়ডের নাম">
                                            <button type="button" @click="col.is_break = !col.is_break" 
                                                    :class="col.is_break ? 'text-amber-700' : 'text-slate-400 hover:text-slate-600'" 
                                                    class="ml-1 text-[11px]" title="টিফিন বিরতি হিসেবে চিহ্নিত করুন">
                                                <i class="fa-solid fa-mug-hot"></i>
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-1 text-[10px] text-slate-600 font-mono">
                                            <input type="text" x-model="col.start_time" class="w-16 bg-white px-1.5 py-0.5 rounded border border-slate-200 text-center font-mono focus:border-emerald-500 focus:outline-none" placeholder="09:00 AM">
                                            <span>-</span>
                                            <input type="text" x-model="col.end_time" class="w-16 bg-white px-1.5 py-0.5 rounded border border-slate-200 text-center font-mono focus:border-emerald-500 focus:outline-none" placeholder="09:45 AM">
                                        </div>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="(row, rowIndex) in currentClassDay().rows" :key="'sc-row-' + rowIndex">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Row Title (Section / Group Name) -->
                                <td class="p-3 sticky left-0 bg-white hover:bg-slate-50 z-10 border-r border-slate-200 align-top min-w-[170px] shadow-xs">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center justify-between gap-1">
                                            <input type="text" x-model="row.row_title" 
                                                   class="font-bold text-xs text-slate-900 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:border-emerald-500 focus:bg-white focus:outline-none w-full shadow-2xs" 
                                                   placeholder="শাখার নাম (যেমন: শাখা ক)">
                                            <button type="button" @click="removeClassRow(rowIndex)" class="text-slate-300 hover:text-rose-500 p-1 transition" title="এই সারিটি মুছুন">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono">সারি #<span x-text="rowIndex + 1"></span></span>
                                    </div>
                                </td>

                                <!-- Dynamic Period Cells -->
                                <template x-for="(period, periodIndex) in row.periods" :key="'sc-cell-' + rowIndex + '-' + periodIndex">
                                    <td class="p-2 border-r border-slate-200 align-top transition-all min-w-[190px]"
                                        :class="period.is_break ? 'bg-amber-50/50' : 'bg-white'">
                                        <div class="rounded-xl border p-2.5 transition-all duration-150 flex flex-col justify-between gap-2 shadow-2xs break-words whitespace-normal"
                                             :class="period.is_break ? 'bg-amber-50 border-amber-200' : 'bg-white border-slate-200 hover:border-emerald-300 focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-400'">
                                            
                                            <!-- Top row: Subject & Break Toggle -->
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 font-mono" x-text="'ঘর ' + (periodIndex + 1)"></span>
                                                    <button type="button" 
                                                            @click="period.is_break = !period.is_break; if(period.is_break && !period.subject) period.subject = 'টিফিন বিরতি';" 
                                                            :class="period.is_break ? 'text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded' : 'text-slate-300 hover:text-slate-600'" 
                                                            class="text-[10px] transition" 
                                                            title="বিরতি হিসেবে চিহ্নিত করুন">
                                                        <i class="fa-solid fa-mug-hot"></i>
                                                    </button>
                                                </div>

                                                <input type="text" x-model="period.subject" 
                                                       class="w-full font-bold text-xs text-slate-900 bg-transparent border-b border-dashed border-slate-300 focus:border-emerald-500 focus:outline-none pb-0.5" 
                                                       :placeholder="period.is_break ? 'বিরতির নাম' : 'বিষয়ের নাম লিখুন'">
                                            </div>

                                            <!-- Middle row: Teacher Name -->
                                            <div class="space-y-0.5" x-show="!period.is_break">
                                                <div class="flex items-center gap-1 text-[11px] text-slate-600">
                                                    <i class="fa-solid fa-chalkboard-user text-slate-400 text-[10px]"></i>
                                                    <input type="text" x-model="period.teacher_name" 
                                                           list="master-teachers-datalist"
                                                           class="w-full text-[11px] bg-transparent border-b border-dashed border-slate-200 focus:border-emerald-500 focus:outline-none pb-0.5 placeholder:text-slate-400 text-slate-700" 
                                                           placeholder="শিক্ষকের নাম">
                                                </div>
                                            </div>

                                            <!-- Bottom row: Room & Eraser -->
                                            <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between gap-1 text-[10px] text-slate-500 font-mono">
                                                <div class="flex items-center gap-0.5" x-show="!period.is_break">
                                                    <i class="fa-solid fa-door-open text-slate-400 text-[9px]"></i>
                                                    <input type="text" x-model="period.room" 
                                                           class="w-16 bg-transparent border-b border-dashed border-slate-200 focus:border-emerald-500 focus:outline-none placeholder:text-slate-300 text-slate-600 text-[10px]" 
                                                           placeholder="রুম নং">
                                                </div>

                                                <button type="button" 
                                                        @click="period.subject = ''; period.teacher_name = ''; period.room = ''; period.is_break = false;" 
                                                        class="text-slate-300 hover:text-rose-500 text-[10px] ml-auto transition" 
                                                        title="ঘর খালি করুন">
                                                    <i class="fa-solid fa-eraser"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Save Banner for Selected Class -->
            <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs border-t border-slate-100">
                <div class="text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span><strong x-text="selectedStudentClass"></strong>-এর <strong x-text="getDayBn(activeDay)"></strong>-এর তথ্যাদি সম্পন্ন হলে <strong>'ছক সংরক্ষণ করুন'</strong> বাটনে চাপ দিন।</span>
                </div>

                <button type="button" @click="saveClassRoutine()" :disabled="saving" class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2 justify-center">
                    <i class="fa-solid fa-floppy-disk" :class="saving ? 'fa-spin' : ''"></i>
                    <span x-text="saving ? 'সংরক্ষণ হচ্ছে...' : 'ছক সংরক্ষণ করুন (Save ' + selectedStudentClass + ' - ' + getDayBn(activeDay) + ')'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Datalists for autocompletion -->
    <datalist id="master-teachers-datalist">
        <template x-for="t in teachersList" :key="t">
            <option :value="t"></option>
        </template>
    </datalist>

    <datalist id="master-classes-datalist">
        <template x-for="c in classesList" :key="c">
            <option :value="c"></option>
        </template>
    </datalist>

    <!-- MODAL 1: JSON Editor & Import (Full Weekly Routine) -->
    <div x-show="jsonModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[92vh] flex flex-col p-6 sm:p-7 shadow-2xl relative" @click.away="jsonModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-file-code"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">সাপ্তাহিক মাস্টার রুটিন JSON এডিটর</h3>
                        <p class="text-xs text-slate-500">শনি থেকে বৃহস্পতিবারের সম্পূর্ণ মাস্টার রুটিন JSON কোড এডিট বা পেস্ট করে ইমপোর্ট করুন</p>
                    </div>
                </div>
                <button @click="jsonModal = false" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('institution.routines.import-json') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden space-y-4 text-xs">
                @csrf

                <div class="flex items-center justify-between">
                    <label class="text-slate-700 font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-code text-blue-600"></i>
                        <span>JSON কোড:</span>
                    </label>

                    <button type="button" @click="copyJson()" class="text-xs font-semibold px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 flex items-center gap-1.5 transition">
                        <i class="fa-regular fa-copy" :class="copied ? 'text-emerald-600' : 'text-slate-500'"></i>
                        <span x-text="copied ? 'কপি হয়েছে!' : 'JSON ফরম্যাট কপি করুন'"></span>
                    </button>
                </div>

                <div class="flex-1 min-h-[300px]">
                    <textarea name="json_content" x-model="jsonText" rows="14" class="w-full h-full bg-slate-900 text-emerald-400 font-mono text-[11px] p-4 rounded-2xl border border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">অথবা .json ফাইল আপলোড করুন:</label>
                        <input type="file" name="json_file" accept=".json,application/json" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-semibold">
                            <input type="checkbox" name="replace_existing" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>পূর্বের সব রুটিন মুছে নতুনভাবে সেট করুন</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="jsonModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        বাতিল
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm flex items-center gap-2 transition">
                        <i class="fa-solid fa-file-import"></i>
                        <span>JSON থেকে রুটিন আপডেট করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: JSON Format & Schema Documentation -->
    <div x-show="schemaModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[92vh] flex flex-col p-6 sm:p-7 shadow-2xl relative" @click.away="schemaModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">মাস্টার রুটিন ডাটাবেস ও গ্রিড কাঠামো</h3>
                        <p class="text-xs text-slate-500">শনিবার হতে বৃহস্পতিবার পর্যন্ত প্রতি শ্রেণির পিরিয়ড বা ঘর কাঠামোর বিবরণ</p>
                    </div>
                </div>
                <button @click="schemaModal = false" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="overflow-y-auto space-y-4 text-xs pr-1">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <h4 class="font-bold text-slate-900 mb-2 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-600"></i>
                        <span>মাস্টার রুটিনের প্রতিটি ঘরের ফিল্ডসমূহ:</span>
                    </h4>
                    <ul class="space-y-1.5 text-slate-700">
                        <li><strong>day</strong> (দিন): <code>"Saturday"</code>, <code>"Sunday"</code>, ... <code>"Thursday"</code></li>
                        <li><strong>columns / periods</strong> (কলাম / পিরিয়ড সংখ্যা): নিজের ইচ্ছামতো যোগ বা বাদ দেওয়া যায়।</li>
                        <li><strong>rows</strong> (রো / শাখা সংখ্যা): প্রতিটি ক্লাসের জন্য আলাদা আলাদা সারি যোগ বা মুছে ফেলা যায়।</li>
                        <li><strong>subject</strong> (বিষয়): বড় বা ছোট যেকোনো দৈর্ঘ্যের টেক্সট লিখলে ঘরটি নিজে থেকেই প্রসারিত হয়ে যাবে।</li>
                        <li><strong>teacher_name</strong> (শিক্ষকের নাম): যেকোনো নাম টাইপ বা ড্রপডাউন থেকে সিলেক্ট করা যাবে।</li>
                        <li><strong>class_name</strong> (শ্রেণির নাম): ডায়নামিক বাটন দিয়ে যেকোনো নতুন শ্রেণি (যেমন: ক্লাস নাইন, অনার্স ১ম বর্ষ) তৈরি করা যায়।</li>
                        <li><strong>room</strong> (রুম নম্বর): যেমন <code>"Room 101"</code></li>
                        <li><strong>is_break</strong> (বিরতি): টিফিন বিরতির ঘর হিসেবে সহজে মার্ক করার সুবিধা।</li>
                    </ul>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-200 mt-4">
                <button type="button" @click="schemaModal = false" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    বুঝেছি, বন্ধ করুন
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
