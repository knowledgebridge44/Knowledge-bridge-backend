<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $courses = Course::with('creator:id,full_name')
            ->withCount('lessons')
            ->paginate($perPage);
        
        // Add enrolled status and ratings for authenticated users
        if ($user = $request->user()) {
            $courses->getCollection()->transform(function ($course) use ($user) {
                $course->enrolled = $course->enrollments()->where('user_id', $user->id)->exists();
                $course->teacher = $course->creator; // Map creator to teacher for frontend
                
                // Calculate ratings efficiently
                $course->average_rating = $course->average_rating ?? 0;
                $course->ratings_count = $course->ratings_count ?? 0;
                
                return $course;
            });
        } else {
            $courses->getCollection()->transform(function ($course) {
                $course->teacher = $course->creator;
                $course->average_rating = $course->average_rating ?? 0;
                $course->ratings_count = $course->ratings_count ?? 0;
                return $course;
            });
        }
        
        return response()->json([
            'data' => $courses->items(),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
        ]);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Course::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        $course->load('creator:id,full_name');
        $course->teacher = $course->creator;

        return response()->json([
            'data' => $course,
        ], 201);
    }

    /**
     * Display the specified course.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('view', $course);

        $course->load('creator:id,full_name');
        
        // Add enrolled status for authenticated user
        $isEnrolled = false;
        $isCourseOwner = false;
        if ($user = $request->user()) {
            $isEnrolled = $course->enrollments()->where('user_id', $user->id)->exists();
            $isCourseOwner = $course->created_by === $user->id;
            $course->enrolled = $isEnrolled;
        }
        
        // Get total approved lessons count (always show the real count)
        $totalLessonsCount = $course->lessons()->where('status', 'approved')->count();
        
        // Include lessons if requested
        $lessonsData = [];
        if ($request->get('include') === 'lessons') {
            if ($isEnrolled || $isCourseOwner || $user?->role === 'admin') {
                // Show all lessons to enrolled users, course owner, or admins
                $lessonsData = $course->lessons()
                    ->where('status', 'approved')
                    ->with(['ratings' => function($query) {
                        $query->select('id', 'lesson_id', 'user_id', 'rating_value');
                    }])
                    ->get()
                    ->toArray();
            } else {
                // Show only the first lesson as preview to non-enrolled users
                $firstLesson = $course->lessons()
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'asc')
                    ->with(['ratings' => function($query) {
                        $query->select('id', 'lesson_id', 'user_id', 'rating_value');
                    }])
                    ->first();
                $lessonsData = $firstLesson ? [$firstLesson->toArray()] : [];
            }
        }
        
        // Add rating attributes
        $course->average_rating = $course->average_rating ?? 0;
        $course->ratings_count = $course->ratings_count ?? 0;
        
        $course->teacher = $course->creator; // Map creator to teacher for frontend

        // Prepare response data
        $responseData = $course->toArray();
        
        // Always show the total count of approved lessons (not just what's being returned)
        $responseData['lessons_count'] = $totalLessonsCount;
        
        // Include lessons array if requested
        if (!empty($lessonsData)) {
            $responseData['lessons'] = $lessonsData;
        }

        return response()->json([
            'data' => $responseData,
        ]);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('update', $course);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $course->update($request->only(['title', 'description']));
        $course->load('creator:id,full_name');
        $course->teacher = $course->creator;

        return response()->json([
            'data' => $course,
        ]);
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course): JsonResponse
    {
        Gate::authorize('delete', $course);

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully',
        ]);
    }
}
