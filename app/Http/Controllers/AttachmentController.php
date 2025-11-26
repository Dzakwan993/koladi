<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * 🔥 Upload File (Universal - Support Pengumuman, CalendarEvent, Task, File, Comment)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'upload' => 'required|file|max:10240', // Max 10MB
        ]);

        try {
            if (!$request->hasFile('upload')) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $file = $request->file('upload');
            $extension = $file->getClientOriginalExtension();

            // Generate unique filename
            $fileName = time() . '_' . Str::random(8) . '.' . $extension;

            // Store file
            $path = $file->storeAs('uploads/files', $fileName, 'public');
            $fileUrl = asset('storage/' . $path);

            $user = Auth::user();

            // 🔥 Support untuk berbagai model yang bisa punya attachments
            $attachableType = $request->input('attachable_type', 'App\\Models\\Pengumuman');
            $attachableId = $request->input('attachable_id'); // Bisa null untuk temporary uploads

            // Validate attachable_type
            $allowedTypes = [
                'App\\Models\\Pengumuman',
                'App\\Models\\CalendarEvent',
                'App\\Models\\Task',
                'App\\Models\\File',
                'App\\Models\\Comment',
            ];

            if (!in_array($attachableType, $allowedTypes)) {
                Storage::disk('public')->delete($path);
                return response()->json(['error' => 'Invalid attachable type'], 400);
            }

            // Create attachment record
            $attachment = Attachment::create([
                'id' => Str::uuid(),
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId, // Bisa null sementara
                'file_url' => $path, // Store relative path
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);

            return response()->json([
                'uploaded' => true,
                'url' => $fileUrl,
                'attachment_id' => $attachment->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $attachment->formatted_size,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🔥 Upload Image (Khusus untuk CKEditor)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|max:5120', // Max 5MB
        ]);

        try {
            if (!$request->hasFile('upload')) {
                return response()->json(['error' => 'Tidak ada file yang diupload.'], 400);
            }

            $file = $request->file('upload');

            // Validasi hanya gambar
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $allowedExtensions)) {
                return response()->json(['error' => 'File harus berupa gambar.'], 400);
            }

            // Generate unique filename
            $fileName = time() . '_' . Str::random(8) . '.' . $extension;

            // Store in images folder
            $path = $file->storeAs('uploads/images', $fileName, 'public');
            $fileUrl = asset('storage/' . $path);

            $user = Auth::user();

            // Support untuk berbagai model
            $attachableType = $request->input('attachable_type', 'App\\Models\\Pengumuman');
            $attachableId = $request->input('attachable_id');

            // Create attachment record
            $attachment = Attachment::create([
                'id' => Str::uuid(),
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
                'file_url' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);

            return response()->json([
                'uploaded' => true,
                'url' => $fileUrl,
                'attachment_id' => $attachment->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🔥 Delete Attachment
     */
    public function destroy($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);

            // Check permission
            if ($attachment->uploaded_by !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete file from storage
            if ($attachment->file_url && Storage::disk('public')->exists($attachment->file_url)) {
                Storage::disk('public')->delete($attachment->file_url);
            }

            // Delete record
            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 Get Attachments by Attachable
     */
    public function index(Request $request)
    {
        $attachableType = $request->input('attachable_type');
        $attachableId = $request->input('attachable_id');

        if (!$attachableType || !$attachableId) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $attachments = Attachment::where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->with('uploader:id,full_name,avatar')
            ->orderBy('uploaded_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'attachments' => $attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name ?? basename($attachment->file_url),
                    'file_url' => $attachment->url,
                    'file_size' => $attachment->formatted_size,
                    'file_type' => $attachment->file_type,
                    'is_image' => $attachment->is_image,
                    'uploaded_by' => [
                        'id' => $attachment->uploader->id ?? null,
                        'name' => $attachment->uploader->full_name ?? 'Unknown',
                    ],
                    'uploaded_at' => $attachment->uploaded_at->toIso8601String(),
                ];
            })
        ]);
    }
}
