<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function index(Request $request)
    {
        $status = $request->get('status');
        $comments = $status
            ? $this->commentService->paginateByStatus($status)
            : $this->commentService->paginateAll();

        return response()->json($comments);
    }

    public function show(int $id)
    {
        $comment = \App\Models\Comment::with(['post:id,title,slug', 'user:id,name', 'replies'])->findOrFail($id);
        return response()->json($comment);
    }

    public function approve(int $id)
    {
        $comment = $this->commentService->approve($id);
        return response()->json(['message' => 'Comment approved', 'comment' => $comment]);
    }

    public function reject(int $id)
    {
        $comment = $this->commentService->reject($id);
        return response()->json(['message' => 'Comment rejected', 'comment' => $comment]);
    }

    public function destroy(int $id)
    {
        $this->commentService->delete($id);
        return response()->json(['message' => 'Comment deleted']);
    }

    public function counts()
    {
        return response()->json($this->commentService->counts());
    }
}
