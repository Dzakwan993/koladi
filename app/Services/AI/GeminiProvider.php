<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiProvider implements AIProvider
{
    public function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?? env('GEMINI_MODEL', 'gemini-3.5-flash');

        if (!$apiKey) {
            throw new RuntimeException('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file.');
        }

        try {
            return $this->callApi($model, $prompt, $apiKey);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Check if error is related to high demand, rate limits, quota, or temporary failure
            $isOverloaded = str_contains(strtolower($errorMsg), 'high demand') ||
                            str_contains(strtolower($errorMsg), 'resource exhausted') ||
                            str_contains(strtolower($errorMsg), 'quota') ||
                            str_contains(strtolower($errorMsg), 'limit') ||
                            str_contains(strtolower($errorMsg), '503') ||
                            str_contains(strtolower($errorMsg), 'temporary') ||
                            str_contains(strtolower($errorMsg), 'rate_limit');

            if ($isOverloaded) {
                // If primary model failed, fallback to gemini-2.0-flash
                if ($model === 'gemini-3.5-flash' || $model === 'gemini-3-flash-preview') {
                    Log::warning("Primary model {$model} failed. Falling back to gemini-2.0-flash: " . $errorMsg);
                    try {
                        return $this->callApi('gemini-2.0-flash', $prompt, $apiKey);
                    } catch (\Exception $fallbackEx) {
                        Log::error("Fallback to gemini-2.0-flash also failed: " . $fallbackEx->getMessage());
                    }
                }
            }
            throw $e;
        }
    }

    protected function callApi(string $model, string $prompt, string $apiKey): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        Log::info("Sending prompt to Gemini model: {$model}");

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.2,
            ]
        ]);

        if ($response->failed()) {
            try {
                // Log model diagnostic list to help debug if it fails on other codes
                $listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
                $listResponse = Http::get($listUrl);
                Log::error("Gemini Available Models Diagnostic: " . ($listResponse->body() ?? 'Empty response'));
            } catch (\Exception $listEx) {
                Log::error("Failed to fetch available models: " . $listEx->getMessage());
            }

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
