<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends Controller
{
    /**
     * Enroll user in a course.
     */
    public function store(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        // Check if already enrolled
        if ($user->isEnrolledIn($course)) {
            return response()->json([
                'error' => true,
                'message' => 'Already enrolled in this course',
                'code' => 409
            ], 409);
        }

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json([
            'message' => 'Successfully enrolled in course',
            'course' => $course->load('creator:id,full_name'),
        ], 201);
    }

    /**
     * Unenroll user from a course.
     */
    public function destroy(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        // Check if enrolled
        if (!$user->isEnrolledIn($course)) {
            return response()->json([
                'error' => true,
                'message' => 'Not enrolled in this course',
                'code' => 404
            ], 404);
        }

        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->delete();

        return response()->json([
            'message' => 'Successfully unenrolled from course',
        ]);
    }

    /**
     * Get user's enrollments.
     */
    public function index(Request $request): JsonResponse
    {
        $enrollments = $request->user()->enrolledCourses()
            ->with('creator:id,full_name')
            ->get();

        return response()->json([
            'enrollments' => $enrollments,
        ]);
    }
}
