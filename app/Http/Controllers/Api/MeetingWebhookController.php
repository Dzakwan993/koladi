<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MeetingWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('Data Transkrip Masuk:', $request->all());

        $title = $request->input('title', '');
        $transcript = $request->input('transcript', '');
        $meetingUrl = $request->input('meeting_url', '');

        // Kalau formatnya masih raw_content (Meeting Link + Transcript digabung)
        if ($request->has('raw_content')) {
            $rawContent = $request->input('raw_content');

            preg_match('/Meeting Link:\s*(.+)/', $rawContent, $urlMatch);
            $meetingUrl = trim($urlMatch[1] ?? '');

            if (preg_match('/Transcript:\s*(.+)/s', $rawContent, $transcriptMatch)) {
                $transcript = trim($transcriptMatch[1]);
            }
        }

        // 🎯 Extract kode workspace dari judul, misal "[W-123] Rapat Mingguan"
        $workspaceId = null;
        if (preg_match('/\[W-([a-zA-Z0-9\-]+)\]/', $title, $wsMatch)) {
            $workspaceId = $wsMatch[1];
        }

        if (!$workspaceId) {
            Log::warning('Webhook meeting: tidak ditemukan kode workspace di judul', ['title' => $title]);
            return response()->json([
                'status' => 'error',
                'message' => 'Kode workspace tidak ditemukan di judul meeting',
            ], 422);
        }

        if (empty($transcript)) {
            Log::warning('Webhook meeting: transkrip kosong', ['title' => $title]);
            return response()->json([
                'status' => 'error',
                'message' => 'Transkrip kosong',
            ], 422);
        }

        $cleanTitle = preg_replace('/\[W-[a-zA-Z0-9\-]+\]\s*/', '', $title);
        $cleanTitle = trim($cleanTitle) ?: 'Meeting';
        $dateStr = Carbon::now()->format('d-m-Y H:i');

        $fileName = "Transkrip - {$cleanTitle} - {$dateStr}.txt";
        $storagePath = 'files/' . Str::slug($cleanTitle, '_') . '_' . time() . '.txt';

        $content = "Meeting Link: {$meetingUrl}\n\nTranscript:\n{$transcript}";
        Storage::disk('public')->put($storagePath, $content);

        $fileModel = File::create([
            'workspace_id' => $workspaceId,
            'company_id' => null,
            'uploaded_by' => null,
            'file_name' => $fileName,
            'file_path' => $storagePath,
            'file_size' => strlen($content),
            'file_type' => 'text/plain',
            'file_url' => asset('storage/' . $storagePath),
            'is_private' => false,
            'uploaded_at' => now(),
        ]);

        Log::info('Transkrip berhasil disimpan', [
            'file_id' => $fileModel->id,
            'workspace_id' => $workspaceId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transkrip meeting berhasil disimpan',
            'file_id' => $fileModel->id,
            'workspace_id' => $workspaceId,
        ], 200);
    }
}
