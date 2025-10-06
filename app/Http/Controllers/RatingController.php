<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RatingController extends Controller
{
    /**
     * Get all ratings for a lesson.
     */
    public function getByLesson(Lesson $lesson): JsonResponse
    {
        $ratings = $lesson->ratings()
            ->with('user:id,full_name')
            ->get();

        // Map rating_value to rating and full_name to name for frontend
        $ratings->transform(function ($rating) {
            $rating->rating = $rating->rating_value;
            $rating->user->name = $rating->user->full_name;
            return $rating;
        });

        return response()->json([
            'data' => $ratings,
        ]);
    }

    /**
     * Store a rating for a lesson.
     */
    public function store(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('create', [Rating::class, $lesson]);

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'sometimes|string',
        ]);

        // Check if user already rated this lesson
        $existingRating = $request->user()->ratings()
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'error' => true,
                'message' => 'You have already rated this lesson',
                'code' => 409
            ], 409);
        }

        $rating = Rating::create([
            'lesson_id' => $lesson->id,
            'user_id' => $request->user()->id,
            'rating_value' => $request->rating,
            'review' => $request->review,
        ]);

        $rating->load('user:id,full_name');
        $rating->rating = $rating->rating_value;
        $rating->user->name = $rating->user->full_name;

        return response()->json([
            'data' => $rating,
        ], 201);
    }

    /**
     * Update the specified rating.
     */
    public function update(Request $request, Rating $rating): JsonResponse
    {
        Gate::authorize('update', $rating);

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'sometimes|string',
        ]);

        $rating->update([
            'rating_value' => $request->rating,
            'review' => $request->review,
        ]);

        $rating->load('user:id,full_name');
        $rating->rating = $rating->rating_value;
        $rating->user->name = $rating->user->full_name;

        return response()->json([
            'data' => $rating,
        ]);
    }

    /**
     * Remove the specified rating.
     */
    public function destroy(Rating $rating): JsonResponse
    {
        Gate::authorize('delete', $rating);

        $rating->delete();

        return response()->json([
            'message' => 'Rating deleted successfully',
        ]);
    }
}
