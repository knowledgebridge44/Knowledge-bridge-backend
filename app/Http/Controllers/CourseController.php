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
    public function index(): JsonResponse
    {
        $courses = Course::with('creator:id,full_name')->get();
        
        return response()->json([
            'courses' => $courses,
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

        return response()->json([
            'course' => $course->load('creator:id,full_name'),
        ], 201);
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course): JsonResponse
    {
        Gate::authorize('view', $course);

        return response()->json([
            'course' => $course->load(['creator:id,full_name', 'approvedLessons']),
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

        return response()->json([
            'course' => $course->load('creator:id,full_name'),
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
