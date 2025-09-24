<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view courses
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        return true; // All authenticated users can view courses
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'admin']);
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('teacher') && $course->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('teacher') && $course->created_by === $user->id);
    }
}
