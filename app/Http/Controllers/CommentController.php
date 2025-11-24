<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\CalendarEvent;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * 🔹 Get comments untuk Calendar Event atau Pengumuman
     */
    public function index($commentableId)
    {
        try {
            // Coba cari di CalendarEvent dulu
            $commentable = CalendarEvent::find($commentableId);

            if (!$commentable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Ambil komentar utama (yang tidak punya parent)
            $comments = $commentable->comments()
                ->whereNull('parent_comment_id')
                ->with(['user', 'replies.user', 'replies.replies.user'])
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
     * 🔹 Store new comment (untuk Calendar Event atau Pengumuman)
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
            $comment->load('user');

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
     * 🔹 Format comment untuk response
     */
    private function formatComment($comment)
    {
        $user = $comment->user;

        // Generate avatar URL
        $avatarPath = $user->avatar ? 'storage/' . $user->avatar : null;
        $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

        $avatarUrl = $hasAvatarFile
            ? asset($avatarPath)
            : ($user->full_name
                ? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) . '&background=random&color=fff'
                : asset('images/dk.jpg'));

        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'createdAt' => $comment->created_at->toIso8601String(),
            'author' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'avatar' => $avatarUrl
            ],
            'replies' => $comment->replies->map(function ($reply) {
                return $this->formatComment($reply);
            })->toArray()
        ];
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

            // Hapus attachments terkait
            DB::table('attachments')
                ->where('attachable_type', 'App\\Models\\Comment')
                ->where('attachable_id', $comment->id)
                ->delete();

            // Soft delete comment
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
            ], 500);
        }
    }
}
