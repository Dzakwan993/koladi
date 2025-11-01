<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    /**
     * Simpan komentar baru
     */
    public function store(Request $request)
    {

        $request->validate([
            'content' => 'required|string',
            'commentable_id' => 'required|string', // UUID pengumuman
            'commentable_type' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id',
        ]);

        // Buat komentar baru
        $comment = Comment::create([
            'id' => (string) Str::uuid(),
            'user_id' => Auth::id(),
            'content' => $request->content,
            'commentable_id' => $request->commentable_id,
            'commentable_type' => $request->commentable_type,
            'parent_comment_id' => $request->parent_comment_id ?? null,
        ]);

        // Muat relasi user
        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'author' => [
                    'name' => $comment->user->name ?? ($comment->user->full_name ?? 'Anonim'),
                    'avatar' => $comment->user->avatar_url ?? asset('images/dk.jpg'),
                ],
                'content' => $comment->content,
                'createdAt' => $comment->created_at->toIso8601String(),
                'replies' => [],
            ],
        ]);
    }

    /**
     * Ambil semua komentar milik satu pengumuman
     */
    public function index(Pengumuman $pengumuman)
    {
        $comments = $pengumuman->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_comment_id')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'author' => [
                        'name' => $comment->user->name ?? ($comment->user->full_name ?? 'Anonim'),
                        'avatar' => $comment->user->avatar_url ?? asset('images/dk.jpg'),

                    ],
                    'content' => $comment->content,
                    'createdAt' => $comment->created_at->toIso8601String(),
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'author' => [
                                'name' => $reply->user->name ?? ($reply->user->full_name ?? 'Anonim'),
                                'avatar' => $reply->user->avatar_url ?? asset('images/dk.jpg'),
                            ],
                            'content' => $reply->content,
                            'createdAt' => $reply->created_at->toIso8601String(),
                        ];
                    }),
                ];
            });

        return response()->json(['comments' => $comments]);
    }
}
