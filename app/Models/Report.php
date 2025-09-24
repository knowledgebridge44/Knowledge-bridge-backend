<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'reason',
        'status',
    ];

    /**
     * Get the user who made this report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the target of this report (polymorphic).
     */
    public function target()
    {
        return match ($this->target_type) {
            'lesson' => $this->belongsTo(Lesson::class, 'target_id'),
            'comment' => $this->belongsTo(Comment::class, 'target_id'),
            'question' => $this->belongsTo(Question::class, 'target_id'),
            default => null,
        };
    }
}
