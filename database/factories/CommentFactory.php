<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isLessonComment = fake()->boolean();
        
        return [
            'lesson_id' => $isLessonComment ? Lesson::factory() : null,
            'question_id' => !$isLessonComment ? Question::factory() : null,
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => fake()->paragraph(),
        ];
    }

    /**
     * Indicate that the comment is for a lesson.
     */
    public function forLesson(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => Lesson::factory(),
            'question_id' => null,
        ]);
    }

    /**
     * Indicate that the comment is for a question.
     */
    public function forQuestion(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'question_id' => Question::factory(),
        ]);
    }
}
