<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\User;
use App\Models\Rating;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get most viewed lessons (Admin only).
     */
    public function mostViewedLessons(): JsonResponse
    {
        // Since we don't have view tracking, we'll use ratings + comments as engagement metric
        $lessons = Lesson::select('lessons.*')
            ->withCount(['ratings', 'comments'])
            ->orderByDesc(DB::raw('ratings_count + comments_count'))
            ->with(['course:id,title', 'uploader:id,full_name'])
            ->limit(10)
            ->get();

        return response()->json([
            'most_viewed_lessons' => $lessons,
        ]);
    }

    /**
     * Get student activity metrics (Admin only).
     */
    public function studentActivity(): JsonResponse
    {
        $studentActivity = User::where('role', 'student')
            ->orWhere('role', 'graduate')
            ->withCount(['questions', 'comments', 'ratings', 'enrolledCourses'])
            ->orderByDesc('questions_count')
            ->get();

        $totalStudents = User::whereIn('role', ['student', 'graduate'])->count();
        $activeStudents = User::whereIn('role', ['student', 'graduate'])
            ->where(function ($query) {
                $query->has('questions')
                    ->orHas('comments')
                    ->orHas('ratings');
            })
            ->count();

        return response()->json([
            'student_activity' => $studentActivity,
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'activity_rate' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0,
        ]);
    }

    /**
     * Get teacher engagement metrics (Admin only).
     */
    public function teacherEngagement(): JsonResponse
    {
        $teacherEngagement = User::where('role', 'teacher')
            ->withCount(['createdCourses', 'uploadedLessons', 'uploadedMaterials'])
            ->with(['createdCourses' => function ($query) {
                $query->withCount('enrolledUsers');
            }])
            ->get();

        $totalTeachers = User::where('role', 'teacher')->count();
        $activeTeachers = User::where('role', 'teacher')
            ->where(function ($query) {
                $query->has('createdCourses')
                    ->orHas('uploadedLessons');
            })
            ->count();

        return response()->json([
            'teacher_engagement' => $teacherEngagement,
            'total_teachers' => $totalTeachers,
            'active_teachers' => $activeTeachers,
            'engagement_rate' => $totalTeachers > 0 ? round(($activeTeachers / $totalTeachers) * 100, 2) : 0,
        ]);
    }

    /**
     * Get overall platform statistics (Admin only).
     */
    public function platformStats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_courses' => \App\Models\Course::count(),
            'total_lessons' => Lesson::count(),
            'approved_lessons' => Lesson::where('status', 'approved')->count(),
            'pending_lessons' => Lesson::where('status', 'pending')->count(),
            'total_enrollments' => \App\Models\Enrollment::count(),
            'total_questions' => \App\Models\Question::count(),
            'total_comments' => Comment::count(),
            'total_ratings' => Rating::count(),
            'average_rating' => Rating::avg('rating_value') ?: 0,
        ];

        return response()->json([
            'platform_stats' => $stats,
        ]);
    }
}
