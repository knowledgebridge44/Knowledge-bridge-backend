<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'created_by',
    ];

    // Don't auto-append these - they'll be added manually when needed
    // to avoid N+1 queries
    protected $appends = [];

    /**
     * Get the user who created this course.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the lessons for this course.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get the enrollments for this course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the enrolled users for this course.
     */
    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    /**
     * Get the approved lessons for this course.
     */
    public function approvedLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->where('status', 'approved');
    }

    /**
     * Get the average rating for this course (based on lesson ratings).
     */
    public function getAverageRatingAttribute()
    {
        $lessons = $this->lessons()->where('status', 'approved')->get();
        if ($lessons->isEmpty()) {
            return 0;
        }

        $totalRatings = 0;
        $ratingCount = 0;

        foreach ($lessons as $lesson) {
            $lessonRatings = $lesson->ratings;
            foreach ($lessonRatings as $rating) {
                $totalRatings += $rating->rating_value;
                $ratingCount++;
            }
        }

        return $ratingCount > 0 ? round($totalRatings / $ratingCount, 1) : 0;
    }

    /**
     * Get the total number of ratings for this course.
     */
    public function getRatingsCountAttribute()
    {
        return $this->lessons()
            ->where('status', 'approved')
            ->withCount('ratings')
            ->get()
            ->sum('ratings_count');
    }
}
