<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view the comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        if ($comment->lesson_id) {
            return $user->isEnrolledIn($comment->lesson->course);
        }
        
        return true; // All users can view comments on questions
    }

    /**
     * Determine whether the user can create comments.
     */
    public function create(User $user, $target): bool
    {
        if (method_exists($target, 'course')) {
            // Comment on lesson
            return in_array($user->role, ['student', 'graduate', 'teacher']) && 
                   $user->isEnrolledIn($target->course);
        }
        
        // Comment on question
        return in_array($user->role, ['student', 'graduate', 'teacher']);
    }

    /**
     * Determine whether the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->hasRole('admin') || $user->id === $comment->user_id;
    }
}
