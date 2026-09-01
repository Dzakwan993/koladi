<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FirefliesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FirefliesWebhookController extends Controller
{
    protected $firefliesService;

    public function __construct(FirefliesService $firefliesService)
    {
        $this->firefliesService = $firefliesService;
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Webhook Fireflies diterima:', $request->all());

        $eventType = $request->input('event');
        $meetingId = $request->input('meeting_id');

        if ($eventType !== 'meeting.transcribed' || !$meetingId) {
            Log::info('Webhook Fireflies diabaikan (event tidak relevan atau meetingId kosong)', [
                'eventType' => $eventType,
                'meetingId' => $meetingId,
            ]);
            return response()->json(['status' => 'ignored'], 200);
        }

        // Ambil detail transkrip lengkap dari Fireflies API
        $transcriptResult = $this->firefliesService->getTranscript($meetingId);

        if (!($transcriptResult['success'] ?? false) || empty($transcriptResult['data'])) {
            Log::error('Gagal mengambil transkrip dari Fireflies', $transcriptResult);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil transkrip dari Fireflies',
            ], 500);
        }

        $data = $transcriptResult['data'];
        $title = $data['title'] ?? '';
        $meetingUrl = $data['meeting_link'] ?? '';

        // Gabungkan semua kalimat jadi satu teks transkrip
        $transcript = collect($data['sentences'] ?? [])
            ->map(fn($s) => ($s['speaker_name'] ?? 'Unknown') . ': ' . ($s['text'] ?? ''))
            ->implode("\n");

        // 🎯 Extract kode workspace dari judul, misal "[W-123] Rapat Mingguan"
        $workspaceId = null;
        if (preg_match('/\[W-([a-zA-Z0-9\-]+)\]/', $title, $wsMatch)) {
            $workspaceId = $wsMatch[1];
        }

        if (!$workspaceId) {
            Log::warning('Webhook Fireflies: kode workspace tidak ditemukan di judul', ['title' => $title]);
            return response()->json([
                'status' => 'error',
                'message' => 'Kode workspace tidak ditemukan di judul meeting',
            ], 422);
        }

        if (empty($transcript)) {
            Log::warning('Webhook Fireflies: transkrip kosong', ['title' => $title]);
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

        Log::info('Transkrip Fireflies berhasil disimpan', [
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
