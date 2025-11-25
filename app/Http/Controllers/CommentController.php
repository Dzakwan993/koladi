<?php

namespace App\Http\Controllers;

use App\Models\Comment;
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
            'id' => 'nullable|string', // 🔥 Pre-generated UUID dari frontend
            'content' => 'required|string',
            'commentable_id' => 'required|string',
            'commentable_type' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id',
        ]);

        // 🔥 FIX: Gunakan UUID dari request atau generate baru
        $commentId = $request->input('id') ?? (string) Str::uuid();

        // Buat komentar baru
        $comment = Comment::create([
            'id' => $commentId, // ✅ FIXED!
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
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
                    'name' => $comment->user->full_name ?? $comment->user->name ?? 'Anonim',
                    'avatar' => $comment->user->avatar ?? asset('images/dk.jpg'),
                ],
                'content' => $comment->content,
                'createdAt' => $comment->created_at->toIso8601String(),
                'replies' => [],
            ],
        ]);
    }
}