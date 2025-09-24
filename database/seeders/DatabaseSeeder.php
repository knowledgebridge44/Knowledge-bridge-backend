<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Comment;
use App\Models\Rating;
use App\Models\Enrollment;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'full_name' => 'Admin User',
            'email' => 'admin@knowledgebridge.com',
        ]);

        // Create test users for each role
        $teacher1 = User::factory()->teacher()->create([
            'full_name' => 'Shahd Teacher',
            'email' => 'teacher@knowledgebridge.com',
        ]);

        $teacher2 = User::factory()->teacher()->create([
            'full_name' => 'Atheer Teacher',
            'email' => 'teacher2@knowledgebridge.com',
        ]);

        $student = User::factory()->student()->create([
            'full_name' => 'Ahad Student',
            'email' => 'student@knowledgebridge.com',
        ]);

        $graduate = User::factory()->graduate()->create([
            'full_name' => 'Ghada Graduate',
            'email' => 'graduate@knowledgebridge.com',
        ]);

        // Create additional users
        $teachers = User::factory()->teacher()->count(3)->create();
        $students = User::factory()->student()->count(15)->create();
        $graduates = User::factory()->graduate()->count(8)->create();

        // Combine all users
        $allTeachers = collect([$teacher1, $teacher2])->merge($teachers);
        $allStudents = collect([$student])->merge($students);
        $allGraduates = collect([$graduate])->merge($graduates);
        $allUsers = $allTeachers->merge($allStudents)->merge($allGraduates);

        // Create courses
        $courses = collect();
        $allTeachers->each(function ($teacher) use (&$courses) {
            $teacherCourses = Course::factory()->count(rand(1, 3))->create([
                'created_by' => $teacher->id,
            ]);
            $courses = $courses->merge($teacherCourses);
        });

        // Create enrollments (students and graduates enroll in courses)
        $learners = $allStudents->merge($allGraduates);
        $learners->each(function ($learner) use ($courses) {
            $enrollCourses = $courses->random(rand(2, 5));
            $enrollCourses->each(function ($course) use ($learner) {
                Enrollment::create([
                    'user_id' => $learner->id,
                    'course_id' => $course->id,
                ]);
            });
        });

        // Create lessons for courses
        $lessons = collect();
        $courses->each(function ($course) use (&$lessons) {
            $courseLessons = Lesson::factory()->count(rand(3, 8))->create([
                'course_id' => $course->id,
                'uploaded_by' => $course->created_by,
                'status' => fake()->randomElement(['approved', 'approved', 'approved', 'pending']), // More approved
            ]);
            $lessons = $lessons->merge($courseLessons);
        });

        // Create questions
        $questions = collect();
        $learners->each(function ($learner) use ($lessons, &$questions) {
            // Get courses the learner is enrolled in
            $enrolledCourses = $learner->enrolledCourses;
            $enrolledLessons = $lessons->whereIn('course_id', $enrolledCourses->pluck('id'));
            
            if ($enrolledLessons->isNotEmpty()) {
                $userQuestions = Question::factory()->count(rand(0, 3))->create([
                    'user_id' => $learner->id,
                    'lesson_id' => $enrolledLessons->random()->id,
                ]);
                $questions = $questions->merge($userQuestions);
            }
        });

        // Create comments on lessons and questions
        $approvedLessons = $lessons->where('status', 'approved');
        
        // Comments on lessons
        $learners->each(function ($learner) use ($approvedLessons) {
            $enrolledCourses = $learner->enrolledCourses;
            $availableLessons = $approvedLessons->whereIn('course_id', $enrolledCourses->pluck('id'));
            
            if ($availableLessons->isNotEmpty()) {
                Comment::factory()->count(rand(0, 2))->create([
                    'lesson_id' => $availableLessons->random()->id,
                    'question_id' => null,
                    'user_id' => $learner->id,
                ]);
            }
        });

        // Comments on questions (answers)
        $questions->each(function ($question) use ($allUsers) {
            if (rand(1, 100) <= 70) { // 70% chance of having at least one answer
                Comment::factory()->count(rand(1, 3))->create([
                    'lesson_id' => null,
                    'question_id' => $question->id,
                    'user_id' => $allUsers->random()->id,
                ]);
            }
        });

        // Create ratings for lessons
        $learners->each(function ($learner) use ($approvedLessons) {
            $enrolledCourses = $learner->enrolledCourses;
            $availableLessons = $approvedLessons->whereIn('course_id', $enrolledCourses->pluck('id'));
            
            if ($availableLessons->isNotEmpty()) {
                $lessonsToRate = $availableLessons->random(rand(1, min(3, $availableLessons->count())));
                $lessonsToRate->each(function ($lesson) use ($learner) {
                    Rating::factory()->create([
                        'lesson_id' => $lesson->id,
                        'user_id' => $learner->id,
                    ]);
                });
            }
        });

        $this->command->info('Database seeded successfully!');
        $this->command->info("Created:");
        $this->command->info("- 1 Admin user (admin@knowledgebridge.com)");
        $this->command->info("- {$allTeachers->count()} Teachers");
        $this->command->info("- {$allStudents->count()} Students");
        $this->command->info("- {$allGraduates->count()} Graduates");
        $this->command->info("- {$courses->count()} Courses");
        $this->command->info("- {$lessons->count()} Lessons");
        $this->command->info("- {$questions->count()} Questions");
        $this->command->info("- Multiple Comments and Ratings");
        $this->command->info("Password for all users: 'password'");
    }
}
