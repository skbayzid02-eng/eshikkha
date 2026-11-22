<?php

use App\Http\Controllers\Api\GuardianApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\TeacherApiController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'online',
        'app' => 'eShikkha Smart Education Platform',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Teacher Mobile App Routes
Route::prefix('teacher')->group(function () {
    Route::post('/login', [TeacherApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [TeacherApiController::class, 'dashboard']);
        Route::get('/students', [TeacherApiController::class, 'getStudents']);
        Route::post('/attendance', [TeacherApiController::class, 'submitAttendance']);
        Route::post('/marks', [TeacherApiController::class, 'submitMarks']);
        Route::get('/notices', [TeacherApiController::class, 'getNotices']);
    });
});

// Student Mobile App Routes
Route::prefix('student')->group(function () {
    Route::post('/register', [StudentApiController::class, 'register']);
    Route::post('/login', [StudentApiController::class, 'login']);
    Route::get('/available-classes', [StudentApiController::class, 'availableClasses']);
    Route::post('/classes', [StudentApiController::class, 'storeClass']);
    Route::get('/available-institutions', [StudentApiController::class, 'availableInstitutions']);
    Route::post('/logout', [StudentApiController::class, 'logout']);
    Route::get('/navigation-hub', [StudentApiController::class, 'navigationHub']);
    Route::get('/study', [StudentApiController::class, 'study']);
    Route::get('/profile-hub', [StudentApiController::class, 'profileHub']);
    Route::get('/portal-data', [StudentApiController::class, 'portalData']);
    Route::get('/profile', [StudentApiController::class, 'profile']);
    Route::get('/dashboard', [StudentApiController::class, 'dashboard']);
    Route::get('/attendance', [StudentApiController::class, 'attendanceHistory']);
    Route::get('/attendance-analytics', [StudentApiController::class, 'attendanceAnalytics']);
    Route::post('/leaves/apply', [StudentApiController::class, 'applyLeave']);
    Route::get('/results', [StudentApiController::class, 'results']);
    Route::get('/academic-results', [StudentApiController::class, 'academicResults']);
    Route::get('/marksheet/{examId}/pdf', [StudentApiController::class, 'marksheetPdf']);
    Route::get('/homework', [StudentApiController::class, 'homework']);
    Route::post('/homework/{id}/submit', [StudentApiController::class, 'submitHomework']);
    Route::get('/routine', [StudentApiController::class, 'routine']);
    Route::get('/exam-routine', [StudentApiController::class, 'examRoutine']);
    Route::get('/messages', [StudentApiController::class, 'messages']);
    Route::post('/messages/{id}/read', [StudentApiController::class, 'markMessageAsRead']);
    Route::get('/notices', [StudentApiController::class, 'notices']);
    Route::get('/fees', [StudentApiController::class, 'fees']);
    Route::post('/fees/{feeId}/pay', [StudentApiController::class, 'payFee']);
    Route::get('/textbooks', [StudentApiController::class, 'textbooks']);
    Route::get('/textbooks/{id}/pdf', [StudentApiController::class, 'textbookPdf']);

    Route::middleware('auth:sanctum')->group(function () {
        // Authenticated routes mapped to same controller methods
    });
});

// Guardian Multi-Child Mobile App Routes
Route::prefix('guardian')->group(function () {
    Route::post('/login', [GuardianApiController::class, 'login']);
    Route::get('/children', [GuardianApiController::class, 'getChildren']);
    Route::get('/children/{studentId}/dashboard', [GuardianApiController::class, 'getChildDashboard']);
    Route::get('/children/{studentId}/attendance-analytics', [GuardianApiController::class, 'childAttendanceAnalytics']);
    Route::get('/children/{studentId}/academic-results', [GuardianApiController::class, 'childAcademicResults']);
    Route::get('/children/{studentId}/marksheet/{examId}/pdf', [GuardianApiController::class, 'childMarksheetPdf']);
    Route::post('/children/lookup', [GuardianApiController::class, 'lookupChild']);
    Route::post('/children/request-link', [GuardianApiController::class, 'requestLink']);
    Route::post('/children/verify-otp', [GuardianApiController::class, 'verifyOtp']);
    Route::post('/children/{studentId}/pay-fee', [GuardianApiController::class, 'payChildFee']);
    Route::post('/children/{studentId}/messages/{messageId}/read', [GuardianApiController::class, 'markMessageAsRead']);

    Route::middleware('auth:sanctum')->group(function () {
        // Authenticated endpoints
    });
});
