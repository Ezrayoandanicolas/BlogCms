<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function store(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'content' => 'required|string|min:3|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $this->commentService->submit($validated, $post);

        if ($comment->status === 'approved') {
            return back()->with('success', 'Komentar berhasil dipublikasikan.');
        }

        return back()->with('success', 'Komentar berhasil dikirim dan menunggu review admin.');
    }
}
