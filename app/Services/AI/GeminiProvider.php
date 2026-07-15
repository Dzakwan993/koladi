<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiProvider implements AIProvider
{
    public function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (!$apiKey) {
            throw new RuntimeException('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        return $this->callApi($model, $prompt, $apiKey);
    }

    protected function callApi(string $model, string $prompt, string $apiKey): string
    {
        $baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $temperature = config('services.gemini.temperature', 0.2);
        $timeout = config('services.gemini.timeout', 120);

        $url = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";

        Log::info('Sending request to Gemini API', [
            'model' => $model,
            'prompt_length' => strlen($prompt),
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->timeout($timeout)
        ->retry(2, 1000)
        ->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => $temperature,
                'maxOutputTokens' => 8192,
            ]
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? $response->body() ?? 'Unknown API error';
            Log::error("Gemini API call failed for model {$model}: " . $errorMsg);
            throw new RuntimeException("Gemini API error: " . $errorMsg);
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (empty($text)) {
            Log::error("Gemini API returned an empty or invalid response for model {$model}: " . $response->body());
            throw new RuntimeException('Gemini API returned an empty response.');
        }

        return $text;
    }
}
