<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    /**
     * Determine whether the user can view any questions.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view questions
    }

    /**
     * Determine whether the user can view the question.
     */
    public function view(User $user, Question $question): bool
    {
        return true; // All authenticated users can view questions
    }

    /**
     * Determine whether the user can create questions.
     */
    public function create(User $user, $lesson = null): bool
    {
        if (!$lesson) {
            return in_array($user->role, ['student', 'graduate', 'teacher']);
        }
        
        return in_array($user->role, ['student', 'graduate', 'teacher']) && 
               $user->isEnrolledIn($lesson->course);
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->hasRole('admin') || $user->id === $question->user_id;
    }
}
