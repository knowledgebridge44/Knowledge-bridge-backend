<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Store a comment for a lesson.
     */
    public function storeForLesson(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('create', [Comment::class, $lesson]);

        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'sometimes|exists:comments,id',
        ]);

        $comment = Comment::create([
            'lesson_id' => $lesson->id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'comment' => $comment->load(['user:id,full_name', 'parent.user:id,full_name']),
        ], 201);
    }

    /**
     * Store a comment for a question.
     */
    public function storeForQuestion(Request $request, Question $question): JsonResponse
    {
        Gate::authorize('create', [Comment::class, $question]);

        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'sometimes|exists:comments,id',
        ]);

        $comment = Comment::create([
            'question_id' => $question->id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'comment' => $comment->load(['user:id,full_name', 'parent.user:id,full_name']),
        ], 201);
    }

    /**
     * Display the specified comment.
     */
    public function show(Comment $comment): JsonResponse
    {
        Gate::authorize('view', $comment);

        return response()->json([
            'comment' => $comment->load([
                'user:id,full_name',
                'parent.user:id,full_name',
                'replies.user:id,full_name'
            ]),
        ]);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'comment' => $comment->load('user:id,full_name'),
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}
