<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Determine whether the user can view any lessons.
     */
    public function viewAny(User $user, $course): bool
    {
        return $user->isEnrolledIn($course);
    }

    /**
     * Determine whether the user can view the lesson.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        return $user->isEnrolledIn($lesson->course) && $lesson->isApproved();
    }

    /**
     * Determine whether the user can create lessons.
     */
    public function create(User $user, $course): bool
    {
        return $user->hasRole('teacher') && $course->created_by === $user->id;
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('teacher') && $lesson->uploaded_by === $user->id);
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('teacher') && $lesson->uploaded_by === $user->id);
    }

    /**
     * Determine whether the user can approve the lesson.
     */
    public function approve(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('admin');
    }
}
