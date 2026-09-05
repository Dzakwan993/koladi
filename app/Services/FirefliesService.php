<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirefliesService
{
    protected ?string $apiKey;
    protected string $endpoint = 'https://api.fireflies.ai/graphql';

    public function __construct()
    {
        $this->apiKey = config('services.fireflies.key') ?: null;
    }

    /**
     * Suruh bot Fireflies join ke meeting yang sedang berlangsung.
     * Title akan dipakai Fireflies sebagai nama file transkrip,
     * jadi kita sisipkan title lengkap (termasuk kode [W-xxx]) di sini.
     */
    public function addToLiveMeeting(string $meetingLink, string $title): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Fireflies API key is not configured in environment.');
            return ['success' => false, 'message' => 'Fireflies API key is not configured. Please set FIREFLIES_API_KEY in .env'];
        }

        $query = '
            mutation AddToLiveMeeting($meeting_link: String!, $title: String) {
                addToLiveMeeting(meeting_link: $meeting_link, title: $title) {
                    success
                    message
                }
            }
        ';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, [
                'query' => $query,
                'variables' => [
                    'meeting_link' => $meetingLink,
                    'title' => $title,
                ],
            ]);

            $result = $response->json();

            Log::info('Fireflies addToLiveMeeting response:', $result ?? []);

            if (isset($result['errors'])) {
                Log::error('Fireflies API error:', $result['errors']);
                return ['success' => false, 'message' => $result['errors'][0]['message'] ?? 'Unknown error'];
            }

            return $result['data']['addToLiveMeeting'] ?? ['success' => false, 'message' => 'No response data'];
        } catch (\Exception $e) {
            Log::error('Fireflies API call failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ambil detail transkrip lengkap berdasarkan meetingId/transcriptId
     * dari Fireflies, dipanggil setelah menerima webhook "Transcription completed"
     */
    public function getTranscript(string $meetingId): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Fireflies API key is not configured in environment.');
            return ['success' => false, 'message' => 'Fireflies API key is not configured. Please set FIREFLIES_API_KEY in .env'];
        }

        $query = '
            query Transcript($transcriptId: String!) {
                transcript(id: $transcriptId) {
                    id
                    title
                    date
                    meeting_link
                    sentences {
                        text
                        speaker_name
                    }
                }
            }
        ';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, [
                'query' => $query,
                'variables' => [
                    'transcriptId' => $meetingId,
                ],
            ]);

            $result = $response->json();

            Log::info('Fireflies getTranscript response:', $result ?? []);

            if (isset($result['errors'])) {
                Log::error('Fireflies API error (getTranscript):', $result['errors']);
                return ['success' => false, 'message' => $result['errors'][0]['message'] ?? 'Unknown error'];
            }

            return ['success' => true, 'data' => $result['data']['transcript'] ?? null];
        } catch (\Exception $e) {
            Log::error('Fireflies getTranscript failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
