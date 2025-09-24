<?php

namespace App\Policies;

use App\Models\Rating;
use App\Models\User;

class RatingPolicy
{
    /**
     * Determine whether the user can create ratings.
     */
    public function create(User $user, $lesson): bool
    {
        return in_array($user->role, ['student', 'graduate']) && 
               $user->isEnrolledIn($lesson->course) &&
               !$user->ratings()->where('lesson_id', $lesson->id)->exists();
    }

    /**
     * Determine whether the user can update the rating.
     */
    public function update(User $user, Rating $rating): bool
    {
        return $user->id === $rating->user_id;
    }

    /**
     * Determine whether the user can delete the rating.
     */
    public function delete(User $user, Rating $rating): bool
    {
        return $user->hasRole('admin') || $user->id === $rating->user_id;
    }
}
