<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Display a listing of reports (Admin only).
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        $reports = Report::with('user:id,full_name')->latest()->get();

        return response()->json([
            'reports' => $reports,
        ]);
    }

    /**
     * Store a newly created report.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Report::class);

        $request->validate([
            'target_type' => 'required|in:lesson,comment,question',
            'target_id' => 'required|integer',
            'reason' => 'required|string',
        ]);

        // Validate target exists
        $targetModel = match ($request->target_type) {
            'lesson' => \App\Models\Lesson::class,
            'comment' => \App\Models\Comment::class,
            'question' => \App\Models\Question::class,
        };

        if (!$targetModel::find($request->target_id)) {
            return response()->json([
                'error' => true,
                'message' => 'Target not found',
                'code' => 404
            ], 404);
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'reason' => $request->reason,
            'status' => 'open',
        ]);

        return response()->json([
            'report' => $report->load('user:id,full_name'),
        ], 201);
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report): JsonResponse
    {
        Gate::authorize('view', $report);

        return response()->json([
            'report' => $report->load('user:id,full_name'),
        ]);
    }

    /**
     * Update the specified report (Admin only).
     */
    public function update(Request $request, Report $report): JsonResponse
    {
        Gate::authorize('update', $report);

        $request->validate([
            'status' => 'required|in:open,resolved,dismissed',
        ]);

        $report->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'report' => $report->load('user:id,full_name'),
        ]);
    }

    /**
     * Remove the specified report.
     */
    public function destroy(Report $report): JsonResponse
    {
        Gate::authorize('delete', $report);

        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully',
        ]);
    }
}
