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
        Route::post('/gate-attendance', [TeacherApiController::class, 'submitGateAttendance']);
        Route::post('/class-attendance', [TeacherApiController::class, 'submitClassAttendance']);
        Route::post('/class-reports', [TeacherApiController::class, 'submitClassReport']);
        Route::get('/class-reports', [TeacherApiController::class, 'getClassReports']);
        Route::post('/homework', [TeacherApiController::class, 'submitHomework']);
        Route::get('/homework', [TeacherApiController::class, 'getHomeworkList']);
        Route::post('/marks', [TeacherApiController::class, 'submitMarks']);
        Route::get('/notices', [TeacherApiController::class, 'getNotices']);
    });
});

// Student Mobile App Routes
Route::prefix('student')->group(function () {
    // Public routes (Registration & initial catalog lookup ONLY)
    Route::post('/register', [StudentApiController::class, 'register']);
    Route::post('/login', [StudentApiController::class, 'login']);
    Route::get('/available-classes', [StudentApiController::class, 'availableClasses']);
    Route::get('/available-institutions', [StudentApiController::class, 'availableInstitutions']);

    // Authenticated Student routes (Strict auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/classes', [StudentApiController::class, 'storeClass']);
        Route::post('/logout', [StudentApiController::class, 'logout']);
        Route::get('/navigation-hub', [StudentApiController::class, 'navigationHub']);
        Route::get('/study', [StudentApiController::class, 'study']);
        Route::get('/profile-hub', [StudentApiController::class, 'profileHub']);
        Route::get('/portal-data', [StudentApiController::class, 'portalData']);
        Route::get('/profile', [StudentApiController::class, 'profile']);
        Route::get('/dashboard', [StudentApiController::class, 'dashboard']);
        Route::get('/attendance', [StudentApiController::class, 'attendanceHistory']);
        Route::get('/gate-attendance', [StudentApiController::class, 'gateAttendance']);
        Route::get('/class-attendance', [StudentApiController::class, 'classAttendance']);
        Route::get('/class-reports', [StudentApiController::class, 'classReports']);
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
        Route::post('/device-token', [StudentApiController::class, 'storeDeviceToken']);
        Route::get('/notifications', [StudentApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [StudentApiController::class, 'markNotificationRead']);
    });
});

// Guardian Multi-Child Mobile App Routes
Route::prefix('guardian')->group(function () {
    // Public routes (Login ONLY)
    Route::post('/login', [GuardianApiController::class, 'login']);

    // Authenticated Guardian routes (Strict auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/children', [GuardianApiController::class, 'getChildren']);
        Route::get('/children/{studentId}/dashboard', [GuardianApiController::class, 'getChildDashboard']);
        Route::get('/children/{studentId}/attendance-analytics', [GuardianApiController::class, 'childAttendanceAnalytics']);
        Route::get('/children/{studentId}/gate-attendance', [GuardianApiController::class, 'childGateAttendance']);
        Route::get('/children/{studentId}/class-attendance', [GuardianApiController::class, 'childClassAttendance']);
        Route::get('/children/{studentId}/class-reports', [GuardianApiController::class, 'childClassReports']);
        Route::get('/children/{studentId}/academic-results', [GuardianApiController::class, 'childAcademicResults']);
        Route::get('/children/{studentId}/marksheet/{examId}/pdf', [GuardianApiController::class, 'childMarksheetPdf']);
        Route::post('/children/lookup', [GuardianApiController::class, 'lookupChild']);
        Route::post('/children/request-link', [GuardianApiController::class, 'requestLink']);
        Route::post('/children/verify-otp', [GuardianApiController::class, 'verifyOtp']);
        Route::post('/children/{studentId}/pay-fee', [GuardianApiController::class, 'payChildFee']);
        Route::post('/children/{studentId}/messages/{messageId}/read', [GuardianApiController::class, 'markMessageAsRead']);
        Route::post('/device-token', [GuardianApiController::class, 'storeDeviceToken']);
        Route::get('/notifications', [GuardianApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [GuardianApiController::class, 'markNotificationRead']);
    });
});
