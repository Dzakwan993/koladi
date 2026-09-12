<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\File;
use App\Services\FirefliesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

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

        // Format event fireflies bisa: "Transcription completed", "meeting.transcribed", dll.
        $rawEvent = $request->input('event')
            ?? $request->input('eventType')
            ?? $request->input('event_type')
            ?? '';

        $eventType = strtolower(trim($rawEvent));

        // ID meeting bisa dikirim sebagai meetingId, meeting_id, transcriptId, atau di dalam data
        $meetingId = $request->input('meetingId')
            ?? $request->input('meeting_id')
            ?? $request->input('transcriptId')
            ?? $request->input('id')
            ?? $request->input('data.meetingId')
            ?? $request->input('data.transcriptId');

        $isRelevant = empty($eventType)
            || str_contains($eventType, 'transcrib')
            || str_contains($eventType, 'complete');

        if (!$isRelevant || !$meetingId) {
            Log::info('Webhook Fireflies diabaikan (event tidak relevan atau meetingId kosong)', [
                'eventType' => $eventType,
                'meetingId' => $meetingId,
                'payload'   => $request->all(),
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

        if (empty($transcript)) {
            Log::warning('Webhook Fireflies: transkrip kosong', ['title' => $title]);
            return response()->json([
                'status' => 'error',
                'message' => 'Transkrip kosong',
            ], 422);
        }

        // 🎯 1. Ekstrak kode workspace dari judul jika ada, misal "[W-123] Rapat Mingguan"
        $workspaceId = null;
        if (preg_match('/\[W-([a-zA-Z0-9\-]+)\]/', $title, $wsMatch)) {
            $workspaceId = $wsMatch[1];
        }

        $eventId = null;

        // 🎯 2. Jika judul Google Meet tidak mengandung [W-...], cari via meeting_link
        if ($meetingUrl) {
            $meetCode = trim(parse_url($meetingUrl, PHP_URL_PATH) ?? '', '/');
            if ($meetCode) {
                // Cek cache
                $eventId = Cache::get("meeting_link_event:{$meetCode}");
                $workspaceId = $workspaceId ?: Cache::get("meeting_link_workspace:{$meetCode}");

                // Cek database calendar_events
                if (!$workspaceId || !$eventId) {
                    $matchingEvent = CalendarEvent::where('meeting_link', 'LIKE', "%{$meetCode}%")
                        ->latest('updated_at')
                        ->first();

                    if ($matchingEvent) {
                        $workspaceId = $workspaceId ?: $matchingEvent->workspace_id;
                        $eventId     = $eventId ?: $matchingEvent->id;
                    }
                }
            }
        }

        // 🎯 3. Jika masih belum ketemu eventId tapi punya workspaceId, cek active meeting di cache
        if (!$eventId && $workspaceId) {
            $eventId = Cache::get("active_meeting_event:{$workspaceId}");
        }

        // 🎯 4. Fallback: cari event online meeting terbaru di workspace tersebut
        if (!$eventId && $workspaceId) {
            $latestEvent = CalendarEvent::where('workspace_id', $workspaceId)
                ->where('is_online_meeting', true)
                ->latest('updated_at')
                ->first();
            if ($latestEvent) {
                $eventId = $latestEvent->id;
            }
        }

        if (!$workspaceId) {
            Log::warning('Webhook Fireflies: workspace tidak dapat diidentifikasi', [
                'title'      => $title,
                'meetingUrl' => $meetingUrl
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode workspace tidak dapat diidentifikasi dari judul maupun link meeting',
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
            'company_id'   => null,
            'uploaded_by'  => null,
            'file_name'    => $fileName,
            'file_path'    => $storagePath,
            'file_size'    => strlen($content),
            'file_type'    => 'text/plain',
            'file_url'     => asset('storage/' . $storagePath),
            'is_private'   => false,
            'uploaded_at'  => now(),
        ]);

        // ✅ Tandai transkrip event ini sudah siap di Cache
        if ($eventId) {
            Cache::put("transcript_status:{$eventId}", 'ready', now()->addHours(6));
            Cache::put("transcript_file:{$eventId}", $fileModel->id, now()->addHours(6));
        }

        Log::info('Transkrip Fireflies berhasil disimpan', [
            'file_id'      => $fileModel->id,
            'workspace_id' => $workspaceId,
            'event_id'     => $eventId,
        ]);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Transkrip meeting berhasil disimpan',
            'file_id'      => $fileModel->id,
            'workspace_id' => $workspaceId,
            'event_id'     => $eventId,
        ], 200);
    }
}
