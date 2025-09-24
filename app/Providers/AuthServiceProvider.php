<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\Question;
use App\Models\Comment;
use App\Models\Rating;
use App\Models\Report;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\CommentPolicy;
use App\Policies\RatingPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        Lesson::class => LessonPolicy::class,
        Material::class => MaterialPolicy::class,
        Question::class => QuestionPolicy::class,
        Comment::class => CommentPolicy::class,
        Rating::class => RatingPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
