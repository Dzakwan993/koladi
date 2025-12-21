<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Task;
use App\Models\Comment;
use App\Models\Pengumuman;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    /**
     * 🔹 Get comments untuk berbagai commentable types (Calendar Event, File, Task, dll)
     */
    public function index($commentableId)
    {
        try {
            // 🔥 Cari di berbagai model yang bisa dikomentari
            $commentable = $this->findCommentable($commentableId);

            if (!$commentable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Ambil komentar utama (yang tidak punya parent)
            $comments = $commentable->comments()
                ->whereNull('parent_comment_id')
                ->with(['user', 'replies.user', 'replies.replies.user', 'attachments'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formatted = $comments->map(function ($comment) {
                return $this->formatComment($comment);
            });

            return response()->json([
                'success' => true,
                'comments' => $formatted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Store new comment (universal untuk semua commentable types)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|uuid',
            'content' => 'required|string',
            'commentable_id' => 'required|uuid',
            'commentable_type' => 'required|string',
            'parent_comment_id' => 'nullable|uuid'
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Validasi commentable_type
            $allowedTypes = [
                'App\\Models\\CalendarEvent',
                'App\\Models\\Pengumuman',
                'App\\Models\\Task',
                'App\\Models\\File',
            ];

            if (!in_array($request->commentable_type, $allowedTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipe komentar tidak valid'
                ], 400);
            }

            // Create comment dengan UUID yang sudah di-generate
            $comment = Comment::create([
                'id' => $request->id,
                'user_id' => $user->id,
                'content' => $request->content,
                'commentable_id' => $request->commentable_id,
                'commentable_type' => $request->commentable_type,
                'parent_comment_id' => $request->parent_comment_id
            ]);

            // Update attachments yang sudah diupload dengan UUID ini
            DB::table('attachments')
                ->where('attachable_id', $request->id)
                ->where('attachable_type', 'App\\Models\\Comment')
                ->update([
                    'attachable_id' => $comment->id,
                    'attachable_type' => 'App\\Models\\Comment'
                ]);

            DB::commit();

            // Load relasi untuk response
            $comment->load(['user', 'attachments']);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan',
                'comment' => $this->formatComment($comment)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Update comment
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        try {
            $comment = Comment::findOrFail($id);

            // Pastikan hanya pembuat yang bisa edit
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }

            $comment->update([
                'content' => $request->content
            ]);

            $comment->load(['user', 'attachments']);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diupdate',
                'comment' => $this->formatComment($comment)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Delete comment
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);

            // Pastikan hanya pembuat yang bisa hapus
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }

            DB::beginTransaction();

            // Hapus attachments files dari storage
            foreach ($comment->attachments as $attachment) {
                if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }

            // Hapus attachments records
            DB::table('attachments')
                ->where('attachable_type', 'App\\Models\\Comment')
                ->where('attachable_id', $comment->id)
                ->delete();

            // Soft delete comment (akan cascade ke replies kalau ada)
            $comment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 Helper: Find commentable model
     */
    private function findCommentable($id)
    {
        // Coba cari di berbagai model
        $models = [
            CalendarEvent::class,
            Pengumuman::class,
            Task::class,
            File::class,
        ];

        foreach ($models as $model) {
            $found = $model::find($id);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * 🔥 Format comment untuk response (with attachments)
     */
    private function formatComment($comment)
    {
        $user = $comment->user;

        if (!$user) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'createdAt' => $comment->created_at->toIso8601String(),
                'author' => [
                    'id' => null,
                    'name' => 'Unknown User',
                    'avatar' => 'https://ui-avatars.com/api/?name=Unknown&background=cccccc&color=666666'
                ],
                'attachments' => [],
                'replies' => []
            ];
        }

        // Generate avatar URL
        $avatarPath = $user->avatar ? 'storage/' . $user->avatar : null;
        $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

        $avatarUrl = $hasAvatarFile
            ? asset($avatarPath)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'User') . '&background=random&color=fff';

        // Format attachments
        $attachments = [];
        if ($comment->relationLoaded('attachments')) {
            $attachments = $comment->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_url' => $attachment->url ?? asset('storage/' . $attachment->file_url),
                    'file_type' => $attachment->file_type,
                    'file_size' => $attachment->formatted_size ?? $attachment->file_size,
                    'is_image' => $attachment->is_image ?? false,
                ];
            })->toArray();
        }

        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'createdAt' => $comment->created_at->toIso8601String(),
            'author' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'avatar' => $avatarUrl
            ],
            'attachments' => $attachments,
            'replies' => $comment->replies->map(function ($reply) {
                return $this->formatComment($reply);
            })->toArray()
        ];
    }
}
