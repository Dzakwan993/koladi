<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyDocumentCommentController extends Controller
{
    public function index($fileId)
    {
        $file = File::findOrFail($fileId);
        
        $comments = $file->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_comment_id')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'author' => [
                        'name' => $comment->user->full_name ?? $comment->user->name ?? 'Anonim',
                        'avatar' => $comment->user->avatar ?? asset('images/dk.jpg'),
                    ],
                    'content' => $comment->content,
                    'createdAt' => $comment->created_at->toIso8601String(),
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'author' => [
                                'name' => $reply->user->full_name ?? $reply->user->name ?? 'Anonim',
                                'avatar' => $reply->user->avatar ?? asset('images/dk.jpg'),
                            ],
                            'content' => $reply->content,
                            'createdAt' => $reply->created_at->toIso8601String(),
                        ];
                    }),
                ];
            });

        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'nullable|string',
            'content' => 'required|string',
            'commentable_id' => 'required|string',
            'commentable_type' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id',
        ]);

        $commentId = $request->input('id') ?? (string) Str::uuid();

        $comment = Comment::create([
            'id' => $commentId,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'commentable_id' => $request->commentable_id,
            'commentable_type' => $request->commentable_type,
            'parent_comment_id' => $request->parent_comment_id ?? null,
        ]);

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