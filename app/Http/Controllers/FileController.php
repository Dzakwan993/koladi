<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;

// class FileController extends Controller
// {
//     public function upload(Request $request)
//     {
//         try {
//             // Pastikan ada file
//             if ($request->hasFile('upload')) {
//                 $file = $request->file('upload');

//                 // Pastikan file valid
//                 if (!$file->isValid()) {
//                     return response()->json(['error' => 'File tidak valid.'], 400);
//                 }

//                 // Buat nama file unik
//                 $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

//                 // Simpan ke storage/public/uploads
//                 $path = $file->storeAs('uploads', $fileName, 'public');

//                 // Dapatkan URL publiknya
//                 $url = asset('storage/' . $path);

//                 return response()->json([
//                     'url' => $url, // CKEditor akan pakai key "url"
//                     'uploaded' => true,
//                     'fileName' => $fileName
//                 ]);
//             } else {
//                 return response()->json(['error' => 'Tidak ada file dikirim.'], 400);
//             }
//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => 'Terjadi kesalahan saat upload: ' . $e->getMessage()
//             ], 500);
//         }
//     }
// }

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
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
                $workspaceId = DB::table('user_workspaces')
                    ->where('user_id', $user->id)
                    ->value('workspace_id');

                $uuid = Str::uuid();
                $fileUrl = asset('storage/' . $path);

                DB::insert("
                    INSERT INTO files (id, folder_id, workspace_id, file_url, is_private, uploaded_by, uploaded_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ", [
                    $uuid,
                    null,
                    $workspaceId,
                    $fileUrl,
                    false,
                    $user->id,
                    now(),
                ]);

                return response()->json(['url' => $fileUrl]);
            }

            return response()->json(['error' => 'No file uploaded'], 400);

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}

