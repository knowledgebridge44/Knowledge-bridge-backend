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
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Question::class);

        $perPage = $request->get('per_page', 20);
        $questions = Question::with([
            'user:id,full_name',
            'lesson:id,title'
        ])
        ->withCount('comments')
        ->latest()
        ->paginate($perPage);

        // Map question_text to title and content for frontend
        $questions->getCollection()->transform(function ($question) {
            $question->title = $question->question_text;
            $question->content = $question->question_text;
            $question->user->name = $question->user->full_name;
            return $question;
        });

        return response()->json([
            'data' => $questions->items(),
            'meta' => [
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'per_page' => $questions->perPage(),
                'total' => $questions->total(),
            ],
        ]);
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'lesson_id' => 'sometimes|exists:lessons,id',
        ]);

        $lesson = null;
        if ($request->lesson_id) {
            $lesson = Lesson::findOrFail($request->lesson_id);
        }

        Gate::authorize('create', [Question::class, $lesson]);

        // Combine title and content into question_text
        $questionText = $request->title . "\n\n" . $request->content;

        $question = Question::create([
            'user_id' => $request->user()->id,
            'lesson_id' => $request->lesson_id,
            'question_text' => $questionText,
        ]);

        $question->load(['user:id,full_name', 'lesson:id,title']);
        $question->title = $request->title;
        $question->content = $request->content;
        $question->user->name = $question->user->full_name;

        return response()->json([
            'data' => $question,
        ], 201);
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question): JsonResponse
    {
        Gate::authorize('view', $question);

        $question->load([
            'user:id,full_name',
            'lesson:id,title'
        ]);

        $question->title = $question->question_text;
        $question->content = $question->question_text;
        $question->user->name = $question->user->full_name;

        return response()->json([
            'data' => $question,
        ]);
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        Gate::authorize('update', $question);

        $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
        ]);

        // Combine title and content if both provided
        if ($request->has('title') && $request->has('content')) {
            $questionText = $request->title . "\n\n" . $request->content;
        } else {
            $questionText = $request->title ?? $request->content ?? $question->question_text;
        }

        $question->update([
            'question_text' => $questionText,
        ]);

        $question->load(['user:id,full_name', 'lesson:id,title']);
        $question->title = $request->title ?? $question->question_text;
        $question->content = $request->content ?? $question->question_text;
        $question->user->name = $question->user->full_name;

        return response()->json([
            'data' => $question,
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
