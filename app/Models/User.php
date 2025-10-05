<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Append these attributes to the model's array/JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = ['name', 'badges'];

    /**
     * Get the name attribute (alias for full_name).
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get the badges attribute (placeholder - implement badge logic later).
     */
    public function getBadgesAttribute(): array
    {
        // TODO: Implement actual badge logic based on user achievements
        return [];
    }

    /**
     * Get the courses created by this user.
     */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    /**
     * Get the courses this user is enrolled in.
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments');
    }

    /**
     * Get the lessons uploaded by this user.
     */
    public function uploadedLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'uploaded_by');
    }

    /**
     * Get the materials uploaded by this user.
     */
    public function uploadedMaterials(): HasMany
    {
        return $this->hasMany(Material::class, 'uploaded_by');
    }

    /**
     * Get the questions asked by this user.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the comments made by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the ratings given by this user.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the reports made by this user.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is enrolled in a course.
     */
    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrolledCourses()->where('course_id', $course->id)->exists();
    }
}
