<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiProvider implements AIProvider
{
    /**
     * Models tried in order. Primary first, then fallback.
     * To add/change models, just edit this list — no retry logic needs to change.
     */
    protected array $models = [
        'gemini-3.5-flash',      // Primary
        'gemini-3.1-flash-lite', // Fallback
    ];

    /**
     * Loaded API keys from config. Null/empty keys are filtered out automatically.
     */
    protected array $apiKeys = [];

    public function __construct()
    {
        $this->apiKeys = array_values(array_filter([
            config('services.gemini.api_key'),
            config('services.gemini.api_key_2'),
            config('services.gemini.api_key_3'),
            config('services.gemini.api_key_4'),
            config('services.gemini.api_key_5'),
            config('services.gemini.api_key_6'),
            config('services.gemini.api_key_7'),
            config('services.gemini.api_key_8'),
            config('services.gemini.api_key_9'),
            config('services.gemini.api_key_10'),
        ]));

        if (empty($this->apiKeys)) {
            throw new RuntimeException('No Gemini API keys configured. Please set GEMINI_API_KEY in your .env file.');
        }
    }

    public function generate(string $prompt): string
    {
        $lastException = null;

        foreach ($this->models as $modelIndex => $model) {
            try {
                $result = $this->callWithRetry($model, $prompt);

                Log::info('Gemini API call completed successfully', [
                    'model'           => $model,
                    'model_index'     => $modelIndex,
                    'prompt_length'   => strlen($prompt),
                    'response_length' => strlen($result['text']),
                    'prompt_tokens'   => $result['usageMetadata']['promptTokenCount'] ?? 0,
                    'output_tokens'   => $result['usageMetadata']['candidatesTokenCount'] ?? 0,
                    'total_tokens'    => $result['usageMetadata']['totalTokenCount'] ?? 0,
                    'latency_seconds' => round($result['latency'], 3),
                    'finish_reason'   => $result['finishReason'],
                    'api_key_index'   => $result['api_key_index'],
                ]);

                Log::info('Gemini Raw Response Text: ' . $result['text']);
                return $result['text'];

            } catch (RuntimeException $e) {
                $lastException = $e;
                $nextModel     = $this->models[$modelIndex + 1] ?? null;

                Log::warning("Gemini model [{$model}] exhausted all API keys.", [
                    'model'      => $model,
                    'error'      => $e->getMessage(),
                    'next_model' => $nextModel ?? 'none — all models exhausted',
                ]);
            }
        }

        throw new RuntimeException(
            'All Gemini models and API keys failed. Last error: ' . ($lastException?->getMessage() ?? 'Unknown error')
        );
    }

    /**
     * Try each API key for a model.
     * - 429 (Rate Limit): switch to next key immediately.
     * - 503 (Overloaded): retry same key once after 2s delay.
     * - Other errors: throw immediately.
     */
    protected function callWithRetry(string $model, string $prompt): array
    {
        $lastException = null;

        foreach ($this->apiKeys as $keyIndex => $apiKey) {
            try {
                $result                  = $this->callApiWithBackoff($model, $prompt, $apiKey);
                $result['api_key_index'] = $keyIndex;

                return $result;

            } catch (RuntimeException $e) {
                $lastException = $e;
                $statusCode    = $e->getCode();

                if ($statusCode === 429) {
                    $nextKey = $this->apiKeys[$keyIndex + 1] ?? null;
                    Log::warning("Gemini [{$model}] API key #{$keyIndex} rate-limited (429), switching key.", [
                        'model'          => $model,
                        'key_index'      => $keyIndex,
                        'next_key_index' => $nextKey ? ($keyIndex + 1) : 'none',
                        'error'          => $e->getMessage(),
                    ]);
                    continue; // immediately try next key
                }

                // Non-429 errors (503 already retried, or other fatal errors) → stop trying keys for this model
                break;
            }
        }

        throw new RuntimeException(
            "Model [{$model}] failed on all API keys. Last error: " . ($lastException?->getMessage() ?? 'Unknown'),
            $lastException?->getCode() ?? 0
        );
    }

    /**
     * Call the API once; on 503/timeout, sleep 2s and retry once.
     * On 429, throw immediately with code 429 so the caller can rotate keys.
     */
    protected function callApiWithBackoff(string $model, string $prompt, string $apiKey): array
    {
        try {
            return $this->callApi($model, $prompt, $apiKey);
        } catch (RuntimeException $e) {
            // 429 → do NOT retry, let caller rotate keys
            if ($e->getCode() === 429) {
                throw $e;
            }

            // 503 or other → retry once after 2s
            Log::warning("Gemini [{$model}] attempt 1 failed (code {$e->getCode()}), retrying in 2s...", [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            sleep(2);

            // Attempt 2 — let exception bubble up if still failing
            return $this->callApi($model, $prompt, $apiKey);
        }
    }

    /**
     * Make a single HTTP request to the Gemini API and return structured result.
     * Throws RuntimeException with HTTP status code as exception code.
     */
    protected function callApi(string $model, string $prompt, string $apiKey): array
    {
        $baseUrl     = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $temperature = config('services.gemini.temperature', 0.2);
        $timeout     = config('services.gemini.timeout', 120);

        $url = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";

        // Read response schema from resources
        $schemaPath     = resource_path('prompts/brief-parser/response-schema.json');
        $responseSchema = [];
        if (file_exists($schemaPath)) {
            $schemaContent  = file_get_contents($schemaPath);
            $responseSchema = json_decode($schemaContent, true) ?? [];
        }

        Log::info('Sending request to Gemini API', [
            'model'               => $model,
            'prompt_length'       => strlen($prompt),
            'has_response_schema' => !empty($responseSchema),
        ]);

        $startTime = microtime(true);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
            ->timeout($timeout)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => array_filter([
                    'responseMimeType' => 'application/json',
                    'responseSchema'   => !empty($responseSchema) ? $responseSchema : null,
                    'temperature'      => $temperature,
                    'maxOutputTokens'  => 16384,
                ])
            ]);

        $latency    = microtime(true) - $startTime;
        $statusCode = $response->status();

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? $response->body() ?? 'Unknown API error';
            Log::error("Gemini API request failed for model [{$model}]", [
                'model'       => $model,
                'status_code' => $statusCode,
                'error'       => $errorMsg,
            ]);
            // Use HTTP status as exception code so callers can distinguish 429 vs 503 etc.
            throw new RuntimeException("Gemini API error [{$model}] (HTTP {$statusCode}): " . $errorMsg, $statusCode);
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (empty($text)) {
            Log::error("Gemini API returned empty response for model [{$model}]", [
                'model' => $model,
                'body'  => $response->body(),
            ]);
            throw new RuntimeException("Gemini API returned an empty response for model [{$model}].", $statusCode);
        }

        return [
            'text'          => $text,
            'usageMetadata' => $response->json('usageMetadata') ?? [],
            'finishReason'  => $response->json('candidates.0.finishReason') ?? 'UNKNOWN',
            'latency'       => $latency,
        ];
    }
}
