<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Courses routes
    Route::get('/courses', [\App\Http\Controllers\CourseController::class, 'index']);
    Route::post('/courses', [\App\Http\Controllers\CourseController::class, 'store']);
    Route::get('/courses/{course}', [\App\Http\Controllers\CourseController::class, 'show']);
    Route::put('/courses/{course}', [\App\Http\Controllers\CourseController::class, 'update']);
    Route::delete('/courses/{course}', [\App\Http\Controllers\CourseController::class, 'destroy']);
    
    // Lessons routes
    Route::get('/lessons', [\App\Http\Controllers\LessonController::class, 'all']); // Admin: get all lessons with filtering
    Route::get('/courses/{course}/lessons', [\App\Http\Controllers\LessonController::class, 'index']);
    Route::post('/courses/{course}/lessons', [\App\Http\Controllers\LessonController::class, 'store']);
    Route::get('/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'show']);
    Route::put('/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'update']);
    Route::delete('/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy']);
    Route::patch('/lessons/{lesson}/approve', [\App\Http\Controllers\LessonController::class, 'approve']);
    
    // Materials routes
    Route::get('/lessons/{lesson}/materials', [\App\Http\Controllers\MaterialController::class, 'getByLesson']);
    Route::post('/lessons/{lesson}/materials', [\App\Http\Controllers\MaterialController::class, 'store']);
    Route::get('/materials/{material}/download', [\App\Http\Controllers\MaterialController::class, 'download']);
    Route::delete('/materials/{material}', [\App\Http\Controllers\MaterialController::class, 'destroy']);
    
    // Questions routes
    Route::get('/questions', [\App\Http\Controllers\QuestionController::class, 'index']);
    Route::post('/questions', [\App\Http\Controllers\QuestionController::class, 'store']);
    Route::get('/questions/{question}', [\App\Http\Controllers\QuestionController::class, 'show']);
    Route::put('/questions/{question}', [\App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [\App\Http\Controllers\QuestionController::class, 'destroy']);
    Route::post('/questions/{question}/comments', [\App\Http\Controllers\CommentController::class, 'storeForQuestion']);
    
    // Comments routes
    Route::get('/lessons/{lesson}/comments', [\App\Http\Controllers\CommentController::class, 'getByLesson']);
    Route::post('/lessons/{lesson}/comments', [\App\Http\Controllers\CommentController::class, 'storeForLesson']);
    Route::get('/questions/{question}/comments', [\App\Http\Controllers\CommentController::class, 'getByQuestion']);
    Route::get('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'show']);
    Route::put('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy']);
    
    // Ratings routes
    Route::get('/lessons/{lesson}/ratings', [\App\Http\Controllers\RatingController::class, 'getByLesson']);
    Route::post('/lessons/{lesson}/ratings', [\App\Http\Controllers\RatingController::class, 'store']);
    Route::put('/ratings/{rating}', [\App\Http\Controllers\RatingController::class, 'update']);
    Route::delete('/ratings/{rating}', [\App\Http\Controllers\RatingController::class, 'destroy']);
    
    // Reports routes
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index']);
    Route::post('/reports', [\App\Http\Controllers\ReportController::class, 'store']);
    Route::get('/reports/{report}', [\App\Http\Controllers\ReportController::class, 'show']);
    Route::put('/reports/{report}', [\App\Http\Controllers\ReportController::class, 'update']);
    Route::delete('/reports/{report}', [\App\Http\Controllers\ReportController::class, 'destroy']);
    
    // Enrollment routes
    Route::get('/enrollments', [\App\Http\Controllers\EnrollmentController::class, 'index']);
    Route::post('/courses/{course}/enroll', [\App\Http\Controllers\EnrollmentController::class, 'store']);
    Route::delete('/courses/{course}/unenroll', [\App\Http\Controllers\EnrollmentController::class, 'destroy']);
    
    // Analytics routes (Admin only)
    Route::middleware('role:admin')->prefix('analytics')->group(function () {
        Route::get('/lessons/most-viewed', [\App\Http\Controllers\AnalyticsController::class, 'mostViewedLessons']);
        Route::get('/students/activity', [\App\Http\Controllers\AnalyticsController::class, 'studentActivity']);
        Route::get('/teachers/engagement', [\App\Http\Controllers\AnalyticsController::class, 'teacherEngagement']);
        Route::get('/platform/stats', [\App\Http\Controllers\AnalyticsController::class, 'platformStats']);
    });
});
