<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'login'])->name('login');
Route::post('/login', [HomeController::class, 'authenticate'])->name('login.post');
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout.post');
Route::post('/register-institution', [HomeController::class, 'registerInstitution'])->name('register-institution');

// One-click quick demo switcher
Route::get('/quick-login/{role}/{institutionId?}', [HomeController::class, 'quickLogin'])->name('quick-login');

// Super Admin Panel (Platform Owner - Only for Me)
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/institutions', [SuperAdminController::class, 'institutions'])->name('institutions');
    Route::post('/institutions', [SuperAdminController::class, 'storeInstitution'])->name('institutions.store');
    Route::get('/institutions/{institution}', [SuperAdminController::class, 'showInstitution'])->name('institutions.show');
    Route::post('/institutions/{institution}/status', [SuperAdminController::class, 'updateStatus'])->name('institutions.status');
    Route::post('/institutions/{institution}/plan', [SuperAdminController::class, 'updatePlan'])->name('institutions.plan');
    Route::get('/institutions/{institution}/impersonate', [SuperAdminController::class, 'impersonate'])->name('institutions.impersonate');
    Route::get('/notices', [SuperAdminController::class, 'notices'])->name('notices');
    Route::post('/notices', [SuperAdminController::class, 'storeNotice'])->name('notices.store');
    Route::get('/subscriptions', [SuperAdminController::class, 'subscriptions'])->name('subscriptions');
});

// Institution Management Panel (School, College, University, Madrasah)
Route::prefix('institution')->name('institution.')->group(function () {
    Route::get('/', [InstitutionController::class, 'dashboard'])->name('dashboard');
    Route::get('/switch/{id}', [InstitutionController::class, 'switchTenant'])->name('switch');
    Route::get('/classes', [InstitutionController::class, 'classes'])->name('classes');
    Route::post('/classes', [InstitutionController::class, 'storeClass'])->name('classes.store');
    Route::post('/classes/{id}/sections', [InstitutionController::class, 'storeSection'])->name('classes.sections.store');
    Route::delete('/classes/{id}', [InstitutionController::class, 'destroyClass'])->name('classes.destroy');
    Route::delete('/sections/{id}', [InstitutionController::class, 'destroySection'])->name('sections.destroy');
    Route::get('/students', [InstitutionController::class, 'students'])->name('students');
    Route::post('/students', [InstitutionController::class, 'storeStudent'])->name('students.store');
    Route::get('/teachers', [InstitutionController::class, 'teachers'])->name('teachers');
    Route::post('/teachers', [InstitutionController::class, 'storeTeacher'])->name('teachers.store');
    Route::get('/attendance', [AttendanceController::class, 'filter'])->name('attendance');
    Route::post('/attendance', [AttendanceController::class, 'storeAttendance'])->name('attendance.store');
    Route::get('/fees', [InstitutionController::class, 'fees'])->name('fees');
    Route::post('/fees', [InstitutionController::class, 'storeFee'])->name('fees.store');
    Route::post('/fees/{fee}/pay', [InstitutionController::class, 'markFeePaid'])->name('fees.pay');
    Route::get('/exams', [InstitutionController::class, 'exams'])->name('exams');
    Route::post('/exams', [InstitutionController::class, 'storeExam'])->name('exams.store');
    Route::post('/exams/results', [InstitutionController::class, 'storeExamResult'])->name('exams.results.store');
    Route::post('/exams/dynamic-marksheet', [InstitutionController::class, 'storeDynamicMarksheet'])->name('exams.dynamic-marksheet.store');
    Route::get('/exams/students/{student}/marksheet', [InstitutionController::class, 'getStudentMarksheet'])->name('exams.student-marksheet');
    Route::post('/subjects', [InstitutionController::class, 'storeSubject'])->name('subjects.store');
    Route::post('/exam-types', [InstitutionController::class, 'storeExamType'])->name('exam-types.store');
    Route::get('/routines', [InstitutionController::class, 'routines'])->name('routines');
    Route::post('/routines', [InstitutionController::class, 'storeRoutine'])->name('routines.store');
    Route::post('/routines/save-grid', [InstitutionController::class, 'saveRoutineGrid'])->name('routines.save-grid');
    Route::post('/routines/import-json', [InstitutionController::class, 'importRoutineJson'])->name('routines.import-json');
    Route::get('/routines/export-json', [InstitutionController::class, 'exportRoutineJson'])->name('routines.export-json');
    Route::delete('/routines/{routine}', [InstitutionController::class, 'destroyRoutine'])->name('routines.destroy');
    Route::get('/notices', [InstitutionController::class, 'notices'])->name('notices');
    Route::post('/notices', [InstitutionController::class, 'storeNotice'])->name('notices.store');
    Route::get('/academic-years', [InstitutionController::class, 'academicYears'])->name('academic-years');
    Route::post('/academic-years', [InstitutionController::class, 'storeAcademicYear'])->name('academic-years.store');
    Route::post('/academic-years/{year}/activate', [InstitutionController::class, 'activateAcademicYear'])->name('academic-years.activate');
    Route::post('/academic-years/{year}/enroll-students', [InstitutionController::class, 'enrollStudentsInYear'])->name('academic-years.enroll');
});
