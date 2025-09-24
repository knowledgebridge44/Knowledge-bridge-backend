<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    /**
     * Determine whether the user can view the material.
     */
    public function view(User $user, Material $material): bool
    {
        return $user->isEnrolledIn($material->lesson->course);
    }

    /**
     * Determine whether the user can create materials.
     */
    public function create(User $user, $lesson): bool
    {
        return $user->hasRole('teacher') && 
               $lesson->course->created_by === $user->id;
    }

    /**
     * Determine whether the user can download the material.
     */
    public function download(User $user, Material $material): bool
    {
        return $user->isEnrolledIn($material->lesson->course);
    }

    /**
     * Determine whether the user can delete the material.
     */
    public function delete(User $user, Material $material): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('teacher') && $material->uploaded_by === $user->id);
    }
}
