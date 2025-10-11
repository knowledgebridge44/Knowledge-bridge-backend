<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LessonController extends Controller
{
    /**
     * Get all lessons (admin only, supports filtering).
     */
    public function all(Request $request): JsonResponse
    {
        // Only admins can view all lessons
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Lesson::with(['course:id,title,created_by', 'uploader:id,full_name']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $lessons = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json($lessons);
    }

    /**
     * Display lessons for a course.
     */
    public function index(Course $course): JsonResponse
    {
        Gate::authorize('viewAny', [Lesson::class, $course]);

        $lessons = $course->approvedLessons()
            ->with(['uploader:id,full_name', 'materials'])
            ->get();

        return response()->json([
            'lessons' => $lessons,
        ]);
    }

    /**
     * Store a newly created lesson.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('create', [Lesson::class, $course]);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => $request->title,
            'content' => $request->content,
            'uploaded_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'lesson' => $lesson->load('uploader:id,full_name'),
        ], 201);
    }

    /**
     * Display the specified lesson.
     */
    public function show(Lesson $lesson): JsonResponse
    {
        Gate::authorize('view', $lesson);

        $lesson->load([
            'uploader:id,full_name',
            'materials',
            'course:id,title,created_by',
        ]);

        // Add enrollment status for authenticated user
        if ($user = request()->user()) {
            $isEnrolled = $lesson->course->enrollments()->where('user_id', $user->id)->exists();
            $lesson->course->enrolled = $isEnrolled;
        }

        return response()->json([
            'data' => $lesson,
        ]);
    }

    /**
     * Approve a lesson (Admin only).
     */
    public function approve(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('approve', $lesson);

        $lesson->update([
            'status' => 'approved',
        ]);

        return response()->json([
            'data' => $lesson,
            'message' => "Lesson approved successfully",
        ]);
    }

    /**
     * Update the specified lesson.
     */
    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('update', $lesson);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $lesson->update($request->only(['title', 'content']));

        return response()->json([
            'lesson' => $lesson->load('uploader:id,full_name'),
        ]);
    }

    /**
     * Remove the specified lesson.
     */
    public function destroy(Lesson $lesson): JsonResponse
    {
        Gate::authorize('delete', $lesson);

        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted successfully',
        ]);
    }
}
