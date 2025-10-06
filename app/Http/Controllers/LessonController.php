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
        ]);

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

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $lesson->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'lesson' => $lesson,
            'message' => "Lesson {$request->status} successfully",
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
