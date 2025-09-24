<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'user_id',
        'rating_value',
        'review',
    ];

    /**
     * Get the lesson this rating belongs to.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the user who gave this rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
