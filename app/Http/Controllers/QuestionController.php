<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Question::class);

        $questions = Question::with([
            'user:id,full_name',
            'lesson:id,title',
            'comments.user:id,full_name'
        ])->latest()->get();

        return response()->json([
            'questions' => $questions,
        ]);
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'question_text' => 'required|string',
            'lesson_id' => 'sometimes|exists:lessons,id',
        ]);

        $lesson = null;
        if ($request->lesson_id) {
            $lesson = Lesson::findOrFail($request->lesson_id);
        }

        Gate::authorize('create', [Question::class, $lesson]);

        $question = Question::create([
            'user_id' => $request->user()->id,
            'lesson_id' => $request->lesson_id,
            'question_text' => $request->question_text,
        ]);

        return response()->json([
            'question' => $question->load(['user:id,full_name', 'lesson:id,title']),
        ], 201);
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question): JsonResponse
    {
        Gate::authorize('view', $question);

        return response()->json([
            'question' => $question->load([
                'user:id,full_name',
                'lesson:id,title',
                'comments.user:id,full_name',
                'comments.replies.user:id,full_name'
            ]),
        ]);
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        Gate::authorize('update', $question);

        $request->validate([
            'question_text' => 'required|string',
        ]);

        $question->update([
            'question_text' => $request->question_text,
        ]);

        return response()->json([
            'question' => $question->load(['user:id,full_name', 'lesson:id,title']),
        ]);
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question): JsonResponse
    {
        Gate::authorize('delete', $question);

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully',
        ]);
    }
}
