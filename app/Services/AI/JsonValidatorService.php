<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class JsonValidatorService
{
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to parse JSON. Raw response: " . $response);
            throw new RuntimeException('Invalid JSON response from AI.');
        }

        return $data;
    }
}

