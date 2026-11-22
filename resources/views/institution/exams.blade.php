@extends('layouts.institution')

@section('page_title', 'পরীক্ষা ও ফলাফল মার্কশিট')

@section('content')
<div class="space-y-6" x-data="{
    classList: @js($initialClassList),
    selectedClass: @js($initialClassList[0] ?? 'অষ্টম শ্রেণি (Class 8)'),
    newCustomClassName: '',
    searchQuery: '',
    classDataMap: @js($classResultsData),
    expandedStudentId: null,
    examModal: false,
    resultModal: false,
    selectedStudentForModal: null,

    selectClass(name) {
        this.selectedClass = name;
        this.searchQuery = '';
        this.expandedStudentId = null;
        if (!this.classDataMap[name]) {
            this.classDataMap[name] = {
                class_id: null,
                class_name: name,
                students_count: 0,
                passed_count: 0,
                students: []
            };
        }
    },

    addNewClassButton() {
        const name = this.newCustomClassName.trim();
        if (!name) {
            alert('অনুগ্রহ করে ক্লাসের নাম লিখুন (যেমন: ক্লাস নাইন বা অনার্স ফার্স্ট ইয়ার)!');
            return;
        }
        if (!this.classList.includes(name)) {
            this.classList.push(name);
        }
        this.selectClass(name);
        this.newCustomClassName = '';
    },

    removeClassButton(index, name) {
        if (confirm('\'' + name + '\' ক্লাস বাটনটি তালিকা থেকে মুছে ফেলতে চান?')) {
            this.classList.splice(index, 1);
            if (this.selectedClass === name) {
                this.selectedClass = this.classList[0] || '';
            }
        }
    },

    currentClassData() {
        if (!this.selectedClass && this.classList.length > 0) {
            this.selectedClass = this.classList[0];
        }
        if (!this.classDataMap[this.selectedClass]) {
            this.classDataMap[this.selectedClass] = {
                class_id: null,
                class_name: this.selectedClass,
                students_count: 0,
                passed_count: 0,
                students: []
            };
        }
        return this.classDataMap[this.selectedClass];
    },

    currentClassStudents() {
        return (this.currentClassData().students || []);
    },

    filteredStudents() {
        const q = this.searchQuery.trim().toLowerCase();
        const students = this.currentClassStudents();
        if (!q) return students;

        return students.filter(s => {
            const nameMatch = (s.name || '').toLowerCase().includes(q);
            const rollMatch = String(s.roll_number || '').toLowerCase().includes(q);
            const sectionMatch = (s.section_name || '').toLowerCase().includes(q);
            return nameMatch || rollMatch || sectionMatch;
        });
    },

    countPassed() {
        return this.currentClassStudents().filter(s => s.has_results && s.grade !== 'F' && s.grade !== 'অপেক্ষমাণ').length;
    },

    calculateAverageGPA() {
        const withGpa = this.currentClassStudents().filter(s => s.gpa_raw !== null && s.gpa_raw !== undefined);
        if (withGpa.length === 0) return '—';
        const sum = withGpa.reduce((acc, s) => acc + (parseFloat(s.gpa_raw) || 0), 0);
        return (sum / withGpa.length).toFixed(2);
    },

    toggleExpand(id) {
        this.expandedStudentId = this.expandedStudentId === id ? null : id;
    },

    openStudentModal(student) {
        this.selectedStudentForModal = student;
    },

    // ==========================================
    // DYNAMIC MARKSHEET SYSTEM STATE & METHODS
    // ==========================================
    dynamicMarksheetModal: false,
    selectedStudentIdForMarksheet: '',
    selectedExamIdForMarksheet: '',
    selectedExamTypeIdForMarksheet: '',
    allSubjectsList: @js($subjects),
    allExamTypesList: @js($examTypes),
    allExamsList: @js($exams),
    marksheetRows: [
        { subject_id: '', subject_name: 'বাংলা ১ম পত্র', total_marks: 100, obtained_marks: 85, grade: 'A+', grade_point: 5.0 },
        { subject_id: '', subject_name: 'ইংরেজি ১ম পত্র', total_marks: 100, obtained_marks: 78, grade: 'A', grade_point: 4.0 },
        { subject_id: '', subject_name: 'সাধারণ গণিত', total_marks: 100, obtained_marks: 92, grade: 'A+', grade_point: 5.0 }
    ],

    openDynamicMarksheetModal(student = null) {
        if (student) {
            this.selectedStudentIdForMarksheet = student.id;
        } else {
            const students = this.currentClassStudents();
            this.selectedStudentIdForMarksheet = students.length > 0 ? students[0].id : '';
        }

        const examsForClass = this.availableExamsForSelectedClass();
        if (examsForClass.length > 0 && !this.selectedExamIdForMarksheet) {
            this.selectedExamIdForMarksheet = examsForClass[0].id;
        }

        this.populateStudentMarksheet(student);
        this.dynamicMarksheetModal = true;
    },

    populateStudentMarksheet(student = null, examId = null) {
        if (!student) {
            const sId = this.selectedStudentIdForMarksheet;
            const students = this.currentClassStudents();
            student = students.find(s => s.id == sId);
        }
        if (!student) return;

        this.selectedStudentIdForMarksheet = student.id;

        if (!examId) {
            examId = this.selectedExamIdForMarksheet;
        }
        if (!examId) {
            const examsForClass = this.availableExamsForSelectedClass();
            if (examsForClass.length > 0) {
                examId = examsForClass[0].id;
                this.selectedExamIdForMarksheet = examId;
            }
        }

        // 1. Check if student already has associated subject records in current page data
        let matching = [];
        if (student.results && student.results.length > 0) {
            if (examId) {
                matching = student.results.filter(r => r.exam_id == examId);
            }
            if (matching.length === 0 && !examId) {
                matching = student.results;
            }
        }

        if (matching.length > 0) {
            this.marksheetRows = matching.map(r => ({
                id: r.id || '',
                subject_id: r.subject_id || '',
                subject_name: r.subject_name || '',
                total_marks: Number(r.total_marks) || 100,
                obtained_marks: Number(r.obtained_marks) || 0,
                grade: r.grade || this.calculateRowGrade(r.obtained_marks, r.total_marks).grade,
                grade_point: Number(r.grade_point !== undefined ? r.grade_point : this.calculateRowGrade(r.obtained_marks, r.total_marks).gp)
            }));
            if (matching[0]?.exam_type_id) {
                this.selectedExamTypeIdForMarksheet = matching[0].exam_type_id;
            }
        } else {
            // 2. Fetch all associated subject records via AJAX endpoint to ensure 100% complete data
            const fetchUrl = '/institution/exams/students/' + student.id + '/marksheet' + (examId ? '?exam_id=' + examId : '');
            fetch(fetchUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.records && data.records.length > 0) {
                    this.marksheetRows = data.records.map(r => ({
                        id: r.id || '',
                        subject_id: r.subject_id || '',
                        subject_name: r.subject_name || '',
                        total_marks: Number(r.total_marks) || 100,
                        obtained_marks: Number(r.obtained_marks) || 0,
                        grade: r.grade || this.calculateRowGrade(r.obtained_marks, r.total_marks).grade,
                        grade_point: Number(r.grade_point !== undefined ? r.grade_point : this.calculateRowGrade(r.obtained_marks, r.total_marks).gp)
                    }));
                    if (data.records[0]?.exam_type_id) {
                        this.selectedExamTypeIdForMarksheet = data.records[0].exam_type_id;
                    }
                } else {
                    this.loadDefaultClassSubjects();
                }
            })
            .catch(() => {
                this.loadDefaultClassSubjects();
            });
        }
    },

    loadDefaultClassSubjects() {
        if (this.allSubjectsList && this.allSubjectsList.length > 0) {
            this.marksheetRows = this.allSubjectsList.slice(0, 7).map(s => ({
                id: '',
                subject_id: s.id,
                subject_name: s.name,
                total_marks: Number(s.full_marks) || 100,
                obtained_marks: '',
                grade: '—',
                grade_point: 0.0
            }));
        } else {
            this.loadStandardSubjects();
        }
    },

    onStudentSelectChange(studentId) {
        const student = this.currentClassStudents().find(s => s.id == studentId);
        if (student) {
            this.populateStudentMarksheet(student, this.selectedExamIdForMarksheet);
        }
    },

    onExamSelectChange(examId) {
        const student = this.currentClassStudents().find(s => s.id == this.selectedStudentIdForMarksheet);
        if (student) {
            this.populateStudentMarksheet(student, examId);
        }
    },

    availableExamsForSelectedClass() {
        const curClass = this.currentClassData();
        if (!curClass || !curClass.class_id) {
            return this.allExamsList;
        }
        const filtered = this.allExamsList.filter(e => e.academic_class_id == curClass.class_id);
        return filtered.length > 0 ? filtered : this.allExamsList;
    },

    addMarksheetRow(subjectName = '', totalMarks = 100, obtained = '', subjectId = '') {
        const res = this.calculateRowGrade(obtained, totalMarks);
        this.marksheetRows.push({
            subject_id: subjectId,
            subject_name: subjectName,
            total_marks: totalMarks,
            obtained_marks: obtained,
            grade: res.grade,
            grade_point: res.gp
        });
    },

    removeMarksheetRow(index) {
        if (this.marksheetRows.length > 1) {
            this.marksheetRows.splice(index, 1);
        } else {
            alert('কমপক্ষে একটি বিষয়ের সারি থাকতে হবে!');
        }
    },

    onSubjectSelectChange(index, event) {
        const selectedVal = event.target.value;
        const sub = this.allSubjectsList.find(s => s.name === selectedVal || s.id == selectedVal);
        if (sub) {
            this.marksheetRows[index].subject_id = sub.id;
            this.marksheetRows[index].subject_name = sub.name;
            this.marksheetRows[index].total_marks = sub.full_marks || 100;
        } else {
            this.marksheetRows[index].subject_name = selectedVal;
        }
        this.updateRowGrade(index);
    },

    updateRowGrade(index) {
        const row = this.marksheetRows[index];
        const res = this.calculateRowGrade(row.obtained_marks, row.total_marks);
        row.grade = res.grade;
        row.grade_point = res.gp;
    },

    calculateRowGrade(obtained, total) {
        if (obtained === '' || obtained === null || obtained === undefined || isNaN(obtained)) {
            return { grade: '—', gp: 0.0 };
        }
        obtained = parseFloat(obtained);
        total = parseFloat(total) || 100;
        const pct = total > 0 ? (obtained / total) * 100 : 0;
        if (pct >= 80) return { grade: 'A+', gp: 5.0 };
        if (pct >= 70) return { grade: 'A', gp: 4.0 };
        if (pct >= 60) return { grade: 'A-', gp: 3.5 };
        if (pct >= 50) return { grade: 'B', gp: 3.0 };
        if (pct >= 40) return { grade: 'C', gp: 2.0 };
        if (pct >= 33) return { grade: 'D', gp: 1.0 };
        return { grade: 'F', gp: 0.0 };
    },

    loadStandardSubjects() {
        this.marksheetRows = [
            { subject_id: '', subject_name: 'বাংলা ১ম পত্র (Bangla 1st)', total_marks: 100, obtained_marks: 85, grade: 'A+', grade_point: 5.0 },
            { subject_id: '', subject_name: 'ইংরেজি ১ম পত্র (English 1st)', total_marks: 100, obtained_marks: 80, grade: 'A+', grade_point: 5.0 },
            { subject_id: '', subject_name: 'সাধারণ গণিত (Mathematics)', total_marks: 100, obtained_marks: 95, grade: 'A+', grade_point: 5.0 },
            { subject_id: '', subject_name: 'সাধারণ বিজ্ঞান (General Science)', total_marks: 100, obtained_marks: 75, grade: 'A', grade_point: 4.0 },
            { subject_id: '', subject_name: 'বাংলাদেশ ও বিশ্বপরিচয় (BGS)', total_marks: 100, obtained_marks: 82, grade: 'A+', grade_point: 5.0 },
            { subject_id: '', subject_name: 'তথ্য ও যোগাযোগ প্রযুক্তি (ICT)', total_marks: 50, obtained_marks: 45, grade: 'A+', grade_point: 5.0 },
            { subject_id: '', subject_name: 'ইসলাম ও নৈতিক শিক্ষা (Religion)', total_marks: 100, obtained_marks: 90, grade: 'A+', grade_point: 5.0 }
        ];
    },

    dynamicTotalFullMarks() {
        return this.marksheetRows.reduce((sum, r) => sum + (parseFloat(r.total_marks) || 0), 0);
    },

    dynamicTotalObtainedMarks() {
        return this.marksheetRows.reduce((sum, r) => sum + (parseFloat(r.obtained_marks) || 0), 0);
    },

    dynamicAverageGPA() {
        const filled = this.marksheetRows.filter(r => r.obtained_marks !== '' && r.obtained_marks !== null && !isNaN(r.obtained_marks));
        if (filled.length === 0) return '—';
        const hasFail = filled.some(r => r.grade === 'F');
        if (hasFail) return '0.00';
        const sumGp = filled.reduce((sum, r) => sum + (parseFloat(r.grade_point) || 0), 0);
        return (sumGp / filled.length).toFixed(2);
    },

    dynamicOverallGrade() {
        const filled = this.marksheetRows.filter(r => r.obtained_marks !== '' && r.obtained_marks !== null && !isNaN(r.obtained_marks));
        if (filled.length === 0) return '—';
        const hasFail = filled.some(r => r.grade === 'F');
        if (hasFail) return 'F';
        const gpa = parseFloat(this.dynamicAverageGPA());
        if (isNaN(gpa)) return '—';
        if (gpa >= 5.0) return 'A+';
        if (gpa >= 4.0) return 'A';
        if (gpa >= 3.5) return 'A-';
        if (gpa >= 3.0) return 'B';
        if (gpa >= 2.0) return 'C';
        if (gpa >= 1.0) return 'D';
        return 'F';
    }
}">

    <!-- ============================================================== -->
    <!-- DYNAMIC CLASS TABS & CREATOR BAR                              -->
    <!-- ============================================================== -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-tags text-indigo-600"></i>
                    <span>শ্রেণি নির্বাচন ও ডায়নামিক ক্লাস বাটন:</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    যেকোনো শ্রেণির বাটনে ক্লিক করলে শুধু ওই শ্রেণির শিক্ষার্থীদের ফলাফল ও মার্কশিট প্রদর্শিত হবে। নতুন ক্লাসের জন্য ডানপাশের ইনপুট ব্যবহার করুন।
                </p>
            </div>

            <!-- Input to dynamically create new class button -->
            <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-200 w-full sm:w-auto">
                <div class="flex items-center gap-2 flex-1 sm:w-72 pl-2">
                    <i class="fa-solid fa-graduation-cap text-indigo-600 text-xs"></i>
                    <input type="text" x-model="newCustomClassName" 
                           @keydown.enter.prevent="addNewClassButton()"
                           placeholder="নতুন ক্লাসের নাম (যেমন: ক্লাস নাইন, অনার্স ১ম বর্ষ)..." 
                           class="bg-transparent border-none text-xs text-slate-900 font-bold focus:outline-none w-full placeholder:text-slate-400 placeholder:font-normal">
                </div>
                <button type="button" @click="addNewClassButton()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                    <i class="fa-solid fa-plus text-[11px]"></i>
                    <span>+ বাটন তৈরি করুন</span>
                </button>
            </div>
        </div>

        <!-- Dynamic Class Button List (Pills) -->
        <div class="flex flex-wrap items-center gap-2">
            <template x-for="(clsName, idx) in classList" :key="clsName">
                <div class="inline-flex items-center rounded-2xl transition-all duration-150 border"
                     :class="selectedClass === clsName ? 'bg-indigo-600 text-white border-indigo-600 shadow-md ring-2 ring-indigo-300 font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 font-semibold'">
                    <button type="button" @click="selectClass(clsName)" class="px-4 py-2 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-graduation-cap text-xs" :class="selectedClass === clsName ? 'text-white' : 'text-indigo-600'"></i>
                        <span x-text="clsName"></span>
                        <span x-show="classDataMap[clsName] && classDataMap[clsName].students_count !== undefined" 
                              class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                              :class="selectedClass === clsName ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'" 
                              x-text="classDataMap[clsName]?.students_count ?? 0"></span>
                    </button>

                    <button type="button" @click.stop="removeClassButton(idx, clsName)" 
                            x-show="classList.length > 1"
                            class="pr-2.5 pl-1 py-2 text-[11px] opacity-60 hover:opacity-100 hover:text-rose-300 transition" 
                            title="এই ক্লাস বাটনটি তালিকা থেকে মুছুন">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- SELECTED CLASS BANNER & LIVE STATS                            -->
    <!-- ============================================================== -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-800 to-blue-800 text-white p-5 sm:p-6 rounded-3xl shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md text-white flex items-center justify-center text-xl font-bold shadow-inner">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wider font-mono text-indigo-200 font-semibold">নির্বাচিত শ্রেণির ফলাফল শিট</span>
                        <span class="text-[10px] bg-indigo-500/30 text-white px-2 py-0.5 rounded-full border border-indigo-400/30">মার্কশিট ও গ্রেডিং</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight flex items-center gap-2 mt-0.5">
                        <span x-text="selectedClass"></span>
                        <span class="text-xs font-normal opacity-90">- এর শিক্ষার্থীদের ফলাফল তালিকা</span>
                    </h2>
                </div>
            </div>

            <!-- Quick Live Stats in Banner -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-sm border border-white/10 flex items-center gap-2">
                    <i class="fa-solid fa-users text-indigo-200"></i>
                    <span>মোট শিক্ষার্থী: <strong class="text-white font-mono" x-text="currentClassStudents().length"></strong> জন</span>
                </span>
                <span class="bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-sm border border-white/10 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-300"></i>
                    <span>উত্তীর্ণ: <strong class="text-emerald-300 font-mono" x-text="countPassed()"></strong> জন</span>
                </span>
                <span class="bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-sm border border-white/10 flex items-center gap-2">
                    <i class="fa-solid fa-star text-amber-300"></i>
                    <span>গড় জিপিএ: <strong class="text-amber-300 font-mono" x-text="calculateAverageGPA()"></strong></span>
                </span>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- INTERACTIVE SEARCH BAR (রোল বা নাম দিয়ে সার্চ)                  -->
    <!-- ============================================================== -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" x-model="searchQuery" 
                       placeholder="রোল নম্বর অথবা শিক্ষার্থীর নাম দিয়ে খুঁজুন (যেমন: ৫, তানভীর, ফারহান)..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-11 pr-10 py-3 text-xs sm:text-sm text-slate-900 font-semibold placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition shadow-inner">
                
                <button type="button" x-show="searchQuery" @click="searchQuery = ''" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition" 
                        title="সার্চ মুছুন">
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500 justify-between sm:justify-end">
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 font-medium">
                    অনুসন্ধানে <strong class="text-indigo-700 font-mono" x-text="filteredStudents().length"></strong> জন শিক্ষার্থী প্রদর্শিত
                </span>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STUDENTS LIST & MARKSHEET CARDS / TABLE                        -->
    <!-- ============================================================== -->
    <div class="space-y-4">
        <!-- EMPTY STATE 1: If class has no students at all -->
        <div x-show="currentClassStudents().length === 0" class="text-center py-14 px-4 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-800 text-base">
                    <span x-text="selectedClass"></span> শ্রেণিতে এখনো কোনো শিক্ষার্থীর ফলাফল তালিকাভুক্ত নেই
                </h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                    এই শ্রেণিতে কোনো শিক্ষার্থীর নাম যোগ করা হলে অথবা পরীক্ষার ফলাফল এন্ট্রি দেওয়া হলে স্বয়ংক্রিয়ভাবে তাদের অর্জিত নম্বর, লেটার গ্রেড এবং জিপিএ প্রদর্শিত হবে।
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <button type="button" @click="resultModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plus"></i>
                    <span>ফলাফল এন্ট্রি করুন</span>
                </button>
                <a href="{{ route('institution.students') }}" class="bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-xl border border-slate-300 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>নতুন শিক্ষার্থী যোগ করুন</span>
                </a>
            </div>
        </div>

        <!-- EMPTY STATE 2: If students exist but search query doesn't match -->
        <div x-show="currentClassStudents().length > 0 && filteredStudents().length === 0" class="text-center py-12 px-4 border-2 border-dashed border-slate-200 rounded-3xl bg-white space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto text-xl">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-800 text-sm">
                    '<span x-text="searchQuery"></span>' এর সাথে মিলে এমন কোনো শিক্ষার্থী পাওয়া যায়নি
                </h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                    অনুগ্রহ করে শিক্ষার্থীর নাম বা রোল নম্বরের সঠিক বানান যাচাই করুন অথবা সার্চ ফিল্টারটি রিসেট করুন।
                </p>
            </div>
            <button type="button" @click="searchQuery = ''" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2 rounded-xl transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-rotate-left text-xs"></i>
                <span>সার্চ রিসেট করুন</span>
            </button>
        </div>

        <!-- STUDENTS RESULTS LIST (Cards with Auto-Expandable Marksheet) -->
        <div x-show="filteredStudents().length > 0" class="space-y-3">
            <template x-for="student in filteredStudents()" :key="student.id">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden transition hover:border-slate-300">
                    <!-- Student Summary Header Bar -->
                    <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
                        <!-- Left Info: Avatar, Name, Roll, Section -->
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-base shadow-2xs font-mono"
                                 :class="student.grade === 'A+' ? 'bg-emerald-100 text-emerald-800' : (student.grade === 'F' ? 'bg-rose-100 text-rose-800' : 'bg-indigo-100 text-indigo-800')">
                                <span x-text="student.roll_number ? '#' + student.roll_number : 'ST'"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-base" x-text="student.name"></h3>
                                    <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-600 font-bold"
                                          x-text="'রোল: #' + (student.roll_number || '—')"></span>
                                </div>
                                <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span x-text="selectedClass"></span>
                                    <span>•</span>
                                    <span x-text="student.section_name"></span>
                                    <template x-if="student.rank">
                                        <span class="text-amber-700 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-trophy text-[10px]"></i>
                                            <span x-text="'মেধাক্রম: ' + student.rank + 'ম'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Right Core Metrics: অর্জিত নম্বর, লেটার গ্রেড, জিপিএ (GPA) -->
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Obtained Marks Metric -->
                            <div class="bg-white px-3.5 py-2 rounded-2xl border border-slate-200 shadow-2xs text-center min-w-[100px]">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">অর্জিত নম্বর</div>
                                <div class="text-sm font-extrabold text-slate-900 font-mono mt-0.5">
                                    <template x-if="student.total_obtained !== null">
                                        <span>
                                            <strong class="text-emerald-700" x-text="student.total_obtained"></strong>
                                            <span class="text-slate-400 text-xs font-normal" x-text="' / ' + student.total_possible"></span>
                                        </span>
                                    </template>
                                    <template x-if="student.total_obtained === null">
                                        <span class="text-slate-400 font-normal text-xs">এন্ট্রি হয়নি</span>
                                    </template>
                                </div>
                            </div>

                            <!-- Letter Grade Metric -->
                            <div class="bg-white px-3.5 py-2 rounded-2xl border border-slate-200 shadow-2xs text-center min-w-[85px]">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">লেটার গ্রেড</div>
                                <div class="mt-0.5">
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-black font-mono inline-block"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800 border border-emerald-300': student.grade === 'A+',
                                              'bg-blue-100 text-blue-800 border border-blue-300': student.grade === 'A',
                                              'bg-teal-100 text-teal-800 border border-teal-300': student.grade === 'A-',
                                              'bg-amber-100 text-amber-800 border border-amber-300': student.grade === 'B',
                                              'bg-orange-100 text-orange-800 border border-orange-300': student.grade === 'C',
                                              'bg-rose-100 text-rose-800 border border-rose-300': student.grade === 'F',
                                              'bg-slate-100 text-slate-600': student.grade === 'অপেক্ষমাণ'
                                          }"
                                          x-text="student.grade"></span>
                                </div>
                            </div>

                            <!-- GPA Metric -->
                            <div class="bg-white px-3.5 py-2 rounded-2xl border border-slate-200 shadow-2xs text-center min-w-[85px]">
                                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">জিপিএ (GPA)</div>
                                <div class="text-base font-black font-mono mt-0.5"
                                     :class="student.gpa_raw >= 4.0 ? 'text-emerald-700' : (student.gpa_raw > 0 ? 'text-blue-700' : 'text-slate-500')"
                                     x-text="student.gpa"></div>
                            </div>

                            <!-- Dynamic Mark Entry Button -->
                            <button type="button" @click="openDynamicMarksheetModal(student)" 
                                    class="px-3.5 py-2 rounded-2xl text-xs font-bold bg-violet-50 hover:bg-violet-100 text-violet-700 border border-violet-200 transition flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                <span>+ নম্বর এন্ট্রি</span>
                            </button>

                            <!-- Expand Toggle Button -->
                            <button type="button" @click="toggleExpand(student.id)" 
                                    class="px-3.5 py-2 rounded-2xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs"
                                    :class="expandedStudentId === student.id ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'">
                                <i class="fa-solid fa-list-check text-xs"></i>
                                <span>মার্কশিট</span>
                                <i class="fa-solid text-[10px] transition-transform duration-200" 
                                   :class="expandedStudentId === student.id ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Full Marksheet Details Table -->
                    <div x-show="expandedStudentId === student.id" x-collapse class="border-t border-slate-200 p-5 bg-white space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <h4 class="font-bold text-slate-900 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fa-solid fa-file-invoice text-indigo-600"></i>
                                <span><strong x-text="student.name"></strong> - এর বিষয়ভিত্তিক ফলাফল মার্কশিট:</span>
                            </h4>

                            <span class="text-xs font-medium text-slate-500">
                                স্ট্যাটাস: <strong :class="student.status === 'উত্তীর্ণ' || student.status === 'promoted' ? 'text-emerald-600' : 'text-slate-700'" x-text="student.status"></strong>
                            </span>
                        </div>

                        <!-- Marksheet Table -->
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 text-slate-600 font-bold text-[11px] border-b border-slate-200">
                                    <tr>
                                        <th class="p-3">#</th>
                                        <th class="p-3">বিষয়ের নাম</th>
                                        <th class="p-3">পরীক্ষা</th>
                                        <th class="p-3 text-center">পূর্ণমান</th>
                                        <th class="p-3 text-center">অর্জিত নম্বর</th>
                                        <th class="p-3 text-center">লেটার গ্রেড</th>
                                        <th class="p-3 text-right">গ্রেড পয়েন্ট (GP)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="(res, rIdx) in student.results" :key="'res-' + res.id">
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="p-3 font-mono text-slate-400" x-text="rIdx + 1"></td>
                                            <td class="p-3 font-bold text-slate-900" x-text="res.subject_name"></td>
                                            <td class="p-3 text-slate-500 font-medium" x-text="res.exam_title"></td>
                                            <td class="p-3 text-center font-mono text-slate-600" x-text="res.total_marks"></td>
                                            <td class="p-3 text-center font-mono font-bold text-emerald-700" x-text="res.obtained_marks"></td>
                                            <td class="p-3 text-center">
                                                <span class="px-2 py-0.5 rounded font-bold font-mono text-[11px]"
                                                      :class="{
                                                          'bg-emerald-100 text-emerald-800': res.grade === 'A+',
                                                          'bg-blue-100 text-blue-800': res.grade === 'A',
                                                          'bg-teal-100 text-teal-800': res.grade === 'A-',
                                                          'bg-amber-100 text-amber-800': res.grade === 'B',
                                                          'bg-orange-100 text-orange-800': res.grade === 'C',
                                                          'bg-rose-100 text-rose-800': res.grade === 'F'
                                                      }"
                                                      x-text="res.grade"></span>
                                            </td>
                                            <td class="p-3 text-right font-mono font-bold text-slate-900" x-text="Number(res.grade_point).toFixed(2)"></td>
                                        </tr>
                                    </template>
                                    
                                    <template x-if="!student.results || student.results.length === 0">
                                        <tr>
                                            <td colspan="7" class="p-6 text-center text-slate-400">
                                                এই শিক্ষার্থীর জন্য এখনো কোনো বিষয়ের নম্বর এন্ট্রি করা হয়নি।
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                
                                <template x-if="student.results && student.results.length > 0">
                                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                        <tr>
                                            <td colspan="3" class="p-3 text-slate-900">মোট ও গড় ফলাফল:</td>
                                            <td class="p-3 text-center font-mono" x-text="student.total_possible"></td>
                                            <td class="p-3 text-center font-mono text-emerald-700" x-text="student.total_obtained"></td>
                                            <td class="p-3 text-center">
                                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black font-mono bg-indigo-100 text-indigo-900" x-text="student.grade"></span>
                                            </td>
                                            <td class="p-3 text-right font-mono text-sm text-indigo-700" x-text="student.gpa"></td>
                                        </tr>
                                    </tfoot>
                                </template>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: নতুন পরীক্ষা তৈরির ফর্ম (Create Exam Modal)           -->
    <!-- ============================================================== -->
    <div x-show="examModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-start sm:items-center justify-center p-3 sm:p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl relative my-auto max-h-[92vh] overflow-y-auto" @click.away="examModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">নতুন পরীক্ষা তৈরি করুন</h3>
                        <p class="text-xs text-slate-500">মূল্যায়ন বা সেমিস্টার পরীক্ষা যুক্ত করুন</p>
                    </div>
                </div>
                <button @click="examModal = false" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('institution.exams.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-bold mb-1">শ্রেণি নির্বাচন করুন:</label>
                    <select name="academic_class_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-indigo-500">
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">পরীক্ষার ধরন (Exam Type):</label>
                    <select name="exam_type_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-indigo-500">
                        <option value="">-- ধরন নির্বাচন করুন (ঐচ্ছিক) --</option>
                        @foreach($examTypes as $et)
                        <option value="{{ $et->id }}">{{ $et->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">পরীক্ষার নাম (Title):</label>
                    <input type="text" name="title" required placeholder="যেমন: অর্ধবার্ষিক মূল্যায়ন ২০২৬" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">শুরুর তারিখ:</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">শেষের তারিখ:</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">শিক্ষাবর্ষ (Session Year):</label>
                    <input type="text" name="session_year" value="{{ date('Y') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="examModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        বাতিল
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>পরীক্ষা সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: ফলাফল এন্ট্রি ফর্ম (Enter Exam Result Modal)          -->
    <!-- ============================================================== -->
    <div x-show="resultModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-start sm:items-center justify-center p-3 sm:p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl relative my-auto max-h-[92vh] overflow-y-auto" @click.away="resultModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">ফলাফল ও মার্কস এন্ট্রি</h3>
                        <p class="text-xs text-slate-500">শিক্ষার্থীর পরীক্ষার একক নম্বর এন্ট্রি করুন</p>
                    </div>
                </div>
                <button @click="resultModal = false" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('institution.exams.results.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-700 font-bold mb-1">পরীক্ষা নির্বাচন করুন:</label>
                    <select name="exam_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-emerald-500">
                        @foreach($exams as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->title }} ({{ $ex->academicClass?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">পরীক্ষার ধরন (Exam Type):</label>
                    <select name="exam_type_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-emerald-500">
                        <option value="">-- ধরন নির্বাচন করুন (ঐচ্ছিক) --</option>
                        @foreach($examTypes as $et)
                        <option value="{{ $et->id }}">{{ $et->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">শিক্ষার্থী নির্বাচন করুন:</label>
                    <select name="student_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-emerald-500">
                        <template x-for="st in currentClassStudents()" :key="st.id">
                            <option :value="st.id" x-text="st.name + ' (রোল: #' + (st.roll_number || '—') + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">বিষয়ের নাম (Subject Name):</label>
                    <input type="text" name="subject_name" required list="singleSubjectDataList" placeholder="যেমন: বাংলা, সাধারণ গণিত, ইংরেজি" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-emerald-500">
                    <datalist id="singleSubjectDataList">
                        @foreach($subjects as $sb)
                        <option value="{{ $sb->name }}">{{ $sb->code ? '(' . $sb->code . ')' : '' }}</option>
                        @endforeach
                    </datalist>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">মোট নম্বর (Full Marks):</label>
                        <input type="number" name="total_marks" value="100" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">প্রাপ্ত নম্বর (Obtained):</label>
                        <input type="number" step="0.5" name="obtained_marks" required placeholder="যেমন: 85" min="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                    <button type="button" @click="resultModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        বাতিল
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>নম্বর সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: ডাইনামিক মার্কশিট সিস্টেম (Dynamic Multi-Row Marksheet) -->
    <!-- ============================================================== -->
    <div x-show="dynamicMarksheetModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-start sm:items-center justify-center p-2 sm:p-4 md:p-6" style="display: none;">
        <div class="bg-white rounded-3xl max-w-4xl w-full p-5 sm:p-6 shadow-2xl relative my-auto max-h-[92vh] flex flex-col" @click.away="dynamicMarksheetModal = false">
            <!-- Modal Header (Always visible, non-shrinking at top) -->
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-200 mb-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-violet-500/20 shrink-0">
                        <i class="fa-solid fa-table-list"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                            <span>ডাইনামিক মার্কশিট ও নম্বর এন্ট্রি</span>
                            <span class="text-[11px] font-bold font-mono px-2 py-0.5 rounded-full bg-violet-100 text-violet-800 hidden xs:inline-block">Dynamic Marksheet</span>
                        </h3>
                        <p class="text-xs text-slate-500">পরীক্ষার ধরন ও শিক্ষার্থী নির্বাচন করে সবকটি বিষয়ের নম্বর এক ফর্মে এন্ট্রি ও সংরক্ষণ করুন</p>
                    </div>
                </div>
                <button @click="dynamicMarksheetModal = false" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition shrink-0">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Marksheet Form -->
            <form action="{{ route('institution.exams.dynamic-marksheet.store') }}" method="POST" class="flex-1 flex flex-col min-h-0 text-xs">
                @csrf

                <!-- Scrollable Form Body -->
                <div class="flex-1 overflow-y-auto min-h-0 pr-1.5 space-y-4">
                    <!-- Top Selection Filters: Exam, Exam Type, Student -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
                        <!-- Exam Selection -->
                        <div>
                            <label class="block text-slate-700 font-bold mb-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar-check text-indigo-600"></i>
                                <span>পরীক্ষা নির্বাচন করুন:</span>
                            </label>
                            <select name="exam_id" x-model="selectedExamIdForMarksheet" @change="onExamSelectChange($event.target.value)" required class="w-full bg-white border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-violet-500 shadow-2xs">
                                <template x-for="ex in availableExamsForSelectedClass()" :key="ex.id">
                                    <option :value="ex.id" x-text="ex.title + (ex.session_year ? ' (' + ex.session_year + ')' : '')"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Exam Type Selection -->
                        <div>
                            <label class="block text-slate-700 font-bold mb-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group text-violet-600"></i>
                                <span>পরীক্ষার ধরন (Exam Type):</span>
                            </label>
                            <select name="exam_type_id" x-model="selectedExamTypeIdForMarksheet" class="w-full bg-white border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-violet-500 shadow-2xs">
                                <option value="">-- ধরন নির্বাচন করুন (ঐচ্ছিক) --</option>
                                @foreach($examTypes as $et)
                                <option value="{{ $et->id }}">{{ $et->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Student Selection (Ordered by roll_number ASC) -->
                        <div>
                            <label class="block text-slate-700 font-bold mb-1.5 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-graduate text-emerald-600"></i>
                                <span>শিক্ষার্থী (রোল অনুযায়ী ক্রমান্বয়ে):</span>
                            </label>
                            <select name="student_id" x-model="selectedStudentIdForMarksheet" @change="onStudentSelectChange($event.target.value)" required class="w-full bg-white border border-slate-200 rounded-xl p-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:border-violet-500 shadow-2xs">
                                <template x-for="st in currentClassStudents()" :key="st.id">
                                    <option :value="st.id" x-text="'রোল: #' + (st.roll_number || '—') + ' - ' + st.name + ' (' + st.section_name + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Action Bar Above Dynamic Table -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-xs sm:text-sm flex items-center gap-1.5">
                                <i class="fa-solid fa-book-open text-violet-600"></i>
                                <span>বিষয় ও প্রাপ্ত নম্বরের তালিকা:</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono text-[11px]" x-text="'মোট ' + marksheetRows.length + ' টি বিষয়'"></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Quick Auto-populate Button -->
                            <button type="button" @click="loadStandardSubjects()" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                <span>স্ট্যান্ডার্ড ৭ বিষয় লোড করুন</span>
                            </button>

                            <!-- Add Row Button -->
                            <button type="button" @click="addMarksheetRow()" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-violet-600 hover:bg-violet-700 text-white shadow-xs transition flex items-center gap-1.5">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>+ নতুন সারি যোগ করুন</span>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Rows Table -->
                    <div class="overflow-x-auto border border-slate-200 rounded-2xl max-h-64 sm:max-h-72 overflow-y-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200 sticky top-0 z-10">
                                <tr>
                                    <th class="p-3 w-10 text-center">#</th>
                                    <th class="p-3">বিষয়ের নাম (Subject)</th>
                                    <th class="p-3 w-28 text-center">পূর্ণমান (Full)</th>
                                    <th class="p-3 w-28 text-center">প্রাপ্ত নম্বর (Obtained)</th>
                                    <th class="p-3 w-20 text-center">গ্রেড</th>
                                    <th class="p-3 w-20 text-center">জিপিএ</th>
                                    <th class="p-3 w-12 text-center">মুছুন</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <template x-for="(row, idx) in marksheetRows" :key="idx">
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="p-3 text-center font-mono font-bold text-slate-400" x-text="idx + 1"></td>
                                        
                                        <!-- Subject input / datalist -->
                                        <td class="p-2.5">
                                            <input type="hidden" :name="'rows[' + idx + '][subject_id]'" x-model="row.subject_id">
                                            <div class="relative">
                                                <input type="text" :name="'rows[' + idx + '][subject_name]'" 
                                                       x-model="row.subject_name" 
                                                       @change="onSubjectSelectChange(idx, $event)"
                                                       required 
                                                       list="subjectsDataList"
                                                       placeholder="যেমন: বাংলা ১ম পত্র, সাধারণ গণিত..." 
                                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs text-slate-900 font-semibold focus:outline-none focus:border-violet-500 focus:bg-white transition">
                                            </div>
                                        </td>

                                        <!-- Full Marks -->
                                        <td class="p-2.5">
                                            <input type="number" :name="'rows[' + idx + '][total_marks]'" 
                                                   x-model.number="row.total_marks" 
                                                   @input="updateRowGrade(idx)"
                                                   min="1" required 
                                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs text-center font-mono font-bold text-slate-900 focus:outline-none focus:border-violet-500 focus:bg-white transition">
                                        </td>

                                        <!-- Obtained Marks -->
                                        <td class="p-2.5">
                                            <input type="number" step="0.5" :name="'rows[' + idx + '][obtained_marks]'" 
                                                   x-model="row.obtained_marks" 
                                                   @input="updateRowGrade(idx)"
                                                   placeholder="প্রাপ্ত নম্বর"
                                                   min="0" required 
                                                   class="w-full bg-emerald-50 border border-emerald-200 rounded-xl p-2 text-xs text-center font-mono font-bold text-emerald-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition">
                                        </td>

                                        <!-- Dynamic Grade Badge -->
                                        <td class="p-2.5 text-center">
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-black font-mono inline-block shadow-2xs"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-800 border border-emerald-300': row.grade === 'A+',
                                                      'bg-blue-100 text-blue-800 border border-blue-300': row.grade === 'A',
                                                      'bg-teal-100 text-teal-800 border border-teal-300': row.grade === 'A-',
                                                      'bg-amber-100 text-amber-800 border border-amber-300': row.grade === 'B',
                                                      'bg-orange-100 text-orange-800 border border-orange-300': row.grade === 'C',
                                                      'bg-rose-100 text-rose-800 border border-rose-300': row.grade === 'F',
                                                      'bg-slate-100 text-slate-500': row.grade === '—'
                                                  }"
                                                  x-text="row.grade"></span>
                                        </td>

                                        <!-- Dynamic GPA -->
                                        <td class="p-2.5 text-center font-mono font-bold text-slate-800" x-text="Number(row.grade_point || 0).toFixed(2)"></td>

                                        <!-- Delete Row -->
                                        <td class="p-2.5 text-center">
                                            <button type="button" @click="removeMarksheetRow(idx)" class="w-8 h-8 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 flex items-center justify-center transition">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Datalist for Subject autocomplete -->
                    <datalist id="subjectsDataList">
                        @foreach($subjects as $sb)
                        <option value="{{ $sb->name }}">{{ $sb->code ? '(' . $sb->code . ')' : '' }}</option>
                        @endforeach
                    </datalist>

                    <!-- Dynamic Realtime Summary Calculation Box -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-3.5 bg-gradient-to-r from-violet-50/70 via-indigo-50/50 to-slate-50 rounded-2xl border border-violet-100 text-center">
                        <div>
                            <div class="text-[10px] text-slate-500 font-semibold">মোট বিষয়</div>
                            <div class="text-base font-extrabold text-slate-900 font-mono mt-0.5" x-text="marksheetRows.length"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-500 font-semibold">সর্বমোট পূর্ণমান</div>
                            <div class="text-base font-extrabold text-slate-900 font-mono mt-0.5" x-text="dynamicTotalFullMarks()"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-500 font-semibold">মোট প্রাপ্ত নম্বর</div>
                            <div class="text-base font-extrabold text-emerald-700 font-mono mt-0.5" x-text="dynamicTotalObtainedMarks()"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-500 font-semibold">সার্বিক জিপিএ (GPA)</div>
                            <div class="text-base font-black font-mono mt-0.5 text-violet-700" x-text="dynamicAverageGPA()"></div>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <div class="text-[10px] text-slate-500 font-semibold">চূড়ান্ত লেটার গ্রেড</div>
                            <div class="mt-0.5">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black font-mono inline-block shadow-2xs"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800 border border-emerald-300': dynamicOverallGrade() === 'A+',
                                          'bg-blue-100 text-blue-800 border border-blue-300': dynamicOverallGrade() === 'A',
                                          'bg-teal-100 text-teal-800 border border-teal-300': dynamicOverallGrade() === 'A-',
                                          'bg-amber-100 text-amber-800 border border-amber-300': dynamicOverallGrade() === 'B',
                                          'bg-orange-100 text-orange-800 border border-orange-300': dynamicOverallGrade() === 'C',
                                          'bg-rose-100 text-rose-800 border border-rose-300': dynamicOverallGrade() === 'F',
                                          'bg-slate-100 text-slate-500': dynamicOverallGrade() === '—'
                                      }"
                                      x-text="dynamicOverallGrade()"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Buttons (Always visible at bottom of card) -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 mt-2 shrink-0">
                    <button type="button" @click="dynamicMarksheetModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        বাতিল
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 shadow-md shadow-violet-500/20 flex items-center gap-2 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>সম্পূর্ণ মার্কশিট সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
