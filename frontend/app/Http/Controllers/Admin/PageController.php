<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Get all pages
     */
    public function index(): JsonResponse
    {
        $pages = Page::with('children')
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }

    /**
     * Store a new page
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'layout' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:pages,id',
            'position' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = Page::create([
            'title' => $request->title,
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_active' => $request->is_active ?? true,
            'layout' => $request->layout ?? 'default',
            'parent_id' => $request->parent_id,
            'position' => $request->position ?? 0
        ]);

        // Create initial revision
        $page->createRevision();

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully',
            'data' => $page->load('parent')
        ], 201);
    }

    /**
     * Get a specific page
     */
    public function show(Page $page): JsonResponse
    {
        $page->load(['parent', 'children', 'revisions.creator']);

        return response()->json([
            'success' => true,
            'data' => $page
        ]);
    }

    /**
     * Update a page
     */
    public function update(Request $request, Page $page): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'layout' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:pages,id',
            'position' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent circular parent reference
        if ($request->parent_id && $request->parent_id == $page->id) {
            return response()->json([
                'success' => false,
                'message' => 'A page cannot be its own parent'
            ], 422);
        }

        $page->update([
            'title' => $request->title,
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_active' => $request->is_active ?? $page->is_active,
            'layout' => $request->layout ?? $page->layout,
            'parent_id' => $request->parent_id,
            'position' => $request->position ?? $page->position
        ]);

        // Create revision
        $page->createRevision();

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
            'data' => $page->load('parent')
        ]);
    }

    /**
     * Delete a page
     */
    public function destroy(Page $page): JsonResponse
    {
        // Page will automatically delete its revisions due to cascade
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully'
        ]);
    }

    /**
     * Update page positions
     */
    public function updatePositions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:pages,id',
            'positions.*.position' => 'required|integer|min:0',
            'positions.*.parent_id' => 'nullable|exists:pages,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->positions as $position) {
            Page::where('id', $position['id'])->update([
                'position' => $position['position'],
                'parent_id' => $position['parent_id']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page positions updated successfully'
        ]);
    }

    /**
     * Get page revisions
     */
    public function revisions(Page $page): JsonResponse
    {
        $revisions = $page->revisions()->with('creator')->get();

        return response()->json([
            'success' => true,
            'data' => $revisions
        ]);
    }

    /**
     * Compare two revisions
     */
    public function compareRevisions(Page $page, PageRevision $revision1, PageRevision $revision2): JsonResponse
    {
        if ($revision1->page_id !== $page->id || $revision2->page_id !== $page->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid revision IDs'
            ], 422);
        }

        $comparison = $revision1->compareWith($revision2);

        return response()->json([
            'success' => true,
            'data' => $comparison
        ]);
    }

    /**
     * Restore a revision
     */
    public function restoreRevision(Page $page, PageRevision $revision): JsonResponse
    {
        if ($revision->page_id !== $page->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid revision ID'
            ], 422);
        }

        $page->restoreRevision($revision);

        return response()->json([
            'success' => true,
            'message' => 'Revision restored successfully',
            'data' => $page->fresh()
        ]);
    }

    /**
     * Toggle page active status
     */
    public function toggleActive(Page $page): JsonResponse
    {
        $page->update([
            'is_active' => !$page->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page status updated successfully',
            'data' => [
                'is_active' => $page->is_active
            ]
        ]);
    }
} 