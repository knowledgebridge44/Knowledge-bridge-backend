<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaterialController extends Controller
{
    /**
     * Get all materials for a lesson.
     */
    public function getByLesson(Lesson $lesson): JsonResponse
    {
        $materials = $lesson->materials()
            ->with('uploader:id,full_name')
            ->get();

        // Map fields for frontend
        $materials->transform(function ($material) {
            $material->title = $material->file_name;
            $material->description = $material->description ?? '';
            return $material;
        });

        return response()->json([
            'data' => $materials,
        ]);
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request, Lesson $lesson): JsonResponse
    {
        Gate::authorize('create', [Material::class, $lesson]);

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('materials', 'public');

        $material = Material::create([
            'lesson_id' => $lesson->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json([
            'material' => $material->load('uploader:id,full_name'),
        ], 201);
    }

    /**
     * Download a material file.
     */
    public function download(Material $material): BinaryFileResponse|JsonResponse
    {
        Gate::authorize('download', $material);

        if (!Storage::disk('public')->exists($material->file_path)) {
            return response()->json([
                'error' => true,
                'message' => 'Not Found',
                'code' => 404
            ], 404);
        }

        return response()->download(
            Storage::disk('public')->path($material->file_path),
            $material->file_name
        );
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Material $material): JsonResponse
    {
        Gate::authorize('delete', $material);

        // Delete file from storage
        if (Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return response()->json([
            'message' => 'Material deleted successfully',
        ]);
    }
}
