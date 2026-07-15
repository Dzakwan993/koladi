<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class JsonValidatorService
{
    // Konfigurasi kontrak dasar JSON output
    protected array $schemaRules = [
        'summary' => 'array',
        'tasks' => 'array',
        'missing_information' => 'array',
        'clarification_questions' => 'array',
    ];

    public function validate(string $response): array
    {
        $cleanResponse = trim($response);

        // Strip markdown code fences if present
        if (str_starts_with($cleanResponse, '```')) {
            $cleanResponse = preg_replace('/^```(?:json)?/i', '', $cleanResponse);
            $cleanResponse = preg_replace('/```$/', '', $cleanResponse);
            $cleanResponse = trim($cleanResponse);
        }

        $data = json_decode($cleanResponse, true);

        // 1. Pastikan JSON valid secara sintaksis
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to parse JSON. Raw response: " . $response);
            throw new RuntimeException('Invalid JSON response from AI.');
        }

        // 2. Pastikan hasil parse berbentuk Object/Array terstruktur
        if (!is_array($data)) {
            Log::error("JSON response is not an array. Raw response: " . $response);
            throw new RuntimeException('AI response must be a JSON object.');
        }

        // 3. Pastikan semua key wajib ada dan bertipe data benar
        foreach ($this->schemaRules as $key => $type) {
            if (!array_key_exists($key, $data)) {
                throw new RuntimeException("Missing required JSON key: '{$key}'");
            }

            if ($type === 'array') {
                if (!is_array($data[$key])) {
                    throw new RuntimeException("JSON key '{$key}' must be an array.");
                }
            }
        }

        return $data;
    }
}
