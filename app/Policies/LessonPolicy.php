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
        // Allow access if user is enrolled in the course
        if ($user->isEnrolledIn($lesson->course) && $lesson->isApproved()) {
            return true;
        }
        
        // Allow access to the first lesson as preview for all users
        if ($lesson->isApproved()) {
            $firstLesson = $lesson->course->lessons()
                ->where('status', 'approved')
                ->orderBy('created_at', 'asc')
                ->first();
            
            if ($firstLesson && $firstLesson->id === $lesson->id) {
                return true;
            }
        }
        
        // Allow course owners and admins to view all lessons
        if ($user->hasRole('admin') || $lesson->course->created_by === $user->id) {
            return true;
        }
        
        return false;
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
