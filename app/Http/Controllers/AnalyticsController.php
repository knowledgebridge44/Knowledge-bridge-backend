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
            ->get()
            ->map(function ($lesson) {
                // Calculate average rating for this lesson
                $avgRating = $lesson->ratings()->avg('rating_value') ?: 0;
                
                return [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'views' => $lesson->ratings_count + $lesson->comments_count, // engagement metric
                    'average_rating' => round($avgRating, 1),
                    'course_title' => $lesson->course?->title ?? 'N/A',
                ];
            });

        return response()->json([
            'most_viewed_lessons' => $lessons,
        ]);
    }

    /**
     * Get student activity metrics (Admin only).
     */
    public function studentActivity(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        
        // Generate time-series data for the last N days
        $activityData = [];
        $startDate = now()->subDays($days);
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            
            // Count active students (those who created questions, comments, or ratings on this day)
            $activeStudents = User::where('role', 'student')
                ->where(function ($query) use ($dateStr) {
                    $query->whereHas('questions', function ($q) use ($dateStr) {
                        $q->whereDate('created_at', $dateStr);
                    })
                    ->orWhereHas('comments', function ($q) use ($dateStr) {
                        $q->whereDate('created_at', $dateStr);
                    })
                    ->orWhereHas('ratings', function ($q) use ($dateStr) {
                        $q->whereDate('created_at', $dateStr);
                    });
                })
                ->count();
            
            // Count new enrollments on this day
            $enrollments = \App\Models\Enrollment::whereDate('created_at', $dateStr)->count();
            
            $activityData[] = [
                'date' => $date->format('M d'),
                'active_students' => $activeStudents,
                'enrollments' => $enrollments,
            ];
        }
        
        return response()->json([
            'student_activity' => $activityData,
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
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
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
