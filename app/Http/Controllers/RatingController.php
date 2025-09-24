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
     * Store a rating for a lesson.
     */
    public function store(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('create', [Rating::class, $lesson]);

        $request->validate([
            'rating_value' => 'required|integer|between:1,5',
            'review' => 'sometimes|string',
        ]);

        // Check if user already rated this lesson
        $existingRating = $request->user()->ratings()
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'error' => true,
                'message' => 'Conflict',
                'code' => 409
            ], 409);
        }

        $rating = Rating::create([
            'lesson_id' => $lesson->id,
            'user_id' => $request->user()->id,
            'rating_value' => $request->rating_value,
            'review' => $request->review,
        ]);

        return response()->json([
            'rating' => $rating->load('user:id,full_name'),
        ], 201);
    }

    /**
     * Update the specified rating.
     */
    public function update(Request $request, Rating $rating): JsonResponse
    {
        Gate::authorize('update', $rating);

        $request->validate([
            'rating_value' => 'required|integer|between:1,5',
            'review' => 'sometimes|string',
        ]);

        $rating->update($request->only(['rating_value', 'review']));

        return response()->json([
            'rating' => $rating->load('user:id,full_name'),
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
