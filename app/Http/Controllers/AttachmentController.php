<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttachmentController extends Controller
{
    public function upload(Request $request)
    {
        try {
            if ($request->hasFile('upload')) {
                $file = $request->file('upload');
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '_' . Str::random(8) . '.' . $extension;
                $path = $file->storeAs('uploads/files', $fileName, 'public');

                $user = Auth::user();
                $fileUrl = asset('storage/' . $path);

                // 🔥 FIX: Ambil dari request, jangan hardcode
                $attachableType = $request->input('attachable_type', 'App\\Models\\Comment');
                $attachableId = $request->input('attachable_id');

                DB::table('attachments')->insert([
                    'id' => Str::uuid(),
                    'attachable_type' => $attachableType, // ✅ FIXED!
                    'attachable_id' => $attachableId,
                    'file_url' => $fileUrl,
                    'uploaded_by' => $user->id,
                    'uploaded_at' => now(),
                ]);

                return response()->json([
                    'uploaded' => true,
                    'url' => $fileUrl
                ]);
            }

            return response()->json(['error' => 'No file uploaded'], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            if ($request->hasFile('upload')) {
                $file = $request->file('upload');

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = strtolower($file->getClientOriginalExtension());
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json(['error' => 'File harus berupa gambar.'], 400);
                }

                $fileName = time() . '_' . Str::random(8) . '.' . $extension;
                $path = $file->storeAs('uploads/images', $fileName, 'public');

                $user = Auth::user();
                $fileUrl = asset('storage/' . $path);

                // 🔥 FIX: Ambil dari request, jangan hardcode
                $attachableType = $request->input('attachable_type', 'App\\Models\\Comment');
                $attachableId = $request->input('attachable_id');

                DB::table('attachments')->insert([
                    'id' => Str::uuid(),
                    'attachable_type' => $attachableType, // ✅ FIXED!
                    'attachable_id' => $attachableId,
                    'file_url' => $fileUrl,
                    'uploaded_by' => $user->id,
                    'uploaded_at' => now(),
                ]);

                return response()->json([
                    'uploaded' => true,
                    'url' => $fileUrl
                ]);
            }

            return response()->json(['error' => 'Tidak ada file yang diupload.'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}