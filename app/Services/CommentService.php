<?php

namespace App\Services;

use App\Mail\NewCommentNotification;
use App\Models\Comment;
use App\Models\SpamFilter;
use App\Models\Post;
use Illuminate\Support\Facades\Mail;

class CommentService
{
    public function submit(array $data, Post $post): Comment
    {
        $status = $this->determineStatus($data['name'] . ' ' . $data['email'] . ' ' . $data['content']);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'content' => $data['content'],
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'approved_at' => $status === 'approved' ? now() : null,
        ]);

        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new NewCommentNotification($comment));
            }
        } catch (\Throwable $e) {
            // silent
        }

        return $comment;
    }

    public function determineStatus(string $text): string
    {
        $match = SpamFilter::containsSpam($text);
        if ($match) {
            $map = ['reject' => 'rejected'];
            return $map[$match->type] ?? $match->type;
        }

        if (SpamFilter::containsUrl($text)) {
            return 'pending';
        }

        return 'approved';
    }

    public function approve(int $id): Comment
    {
        $comment = Comment::findOrFail($id);
        $comment->approve();
        return $comment;
    }

    public function reject(int $id): Comment
    {
        $comment = Comment::findOrFail($id);
        $comment->reject();
        return $comment;
    }

    public function getApprovedForPost(int $postId)
    {
        return Comment::with('replies')
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginateAll(int $perPage = 20)
    {
        return Comment::with(['post:id,title,slug', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function paginateByStatus(string $status, int $perPage = 20)
    {
        return Comment::with(['post:id,title,slug', 'user:id,name'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function delete(int $id): void
    {
        Comment::findOrFail($id)->delete();
    }

    public function counts(): array
    {
        return [
            'total' => Comment::count(),
            'pending' => Comment::pending()->count(),
            'approved' => Comment::approved()->count(),
            'rejected' => Comment::where('status', 'rejected')->count(),
            'spam' => Comment::where('status', 'spam')->count(),
        ];
    }
}
