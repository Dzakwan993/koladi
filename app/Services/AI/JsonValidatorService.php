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
        'decisions' => 'array',
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

        // 1. Pastikan JSON valid secara sintaksis (dengan auto-repair jika terpotong)
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Coba perbaiki jika JSON terpotong di akhir (misal: ',"missing_information":')
            $repairedResponse = preg_replace('/,?\s*"[a-zA-Z0-9_]+"\s*:\s*$/', '', $cleanResponse);
            $openBraces = substr_count($repairedResponse, '{') - substr_count($repairedResponse, '}');
            $openBrackets = substr_count($repairedResponse, '[') - substr_count($repairedResponse, ']');
            $repairedResponse .= str_repeat(']', max(0, $openBrackets));
            $repairedResponse .= str_repeat('}', max(0, $openBraces));

            $repairedData = json_decode($repairedResponse, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($repairedData)) {
                $data = $repairedData;
                Log::warning("JSON was automatically repaired after truncation.");
            } else {
                Log::error("Failed to parse JSON. Raw response: " . $response);
                throw new RuntimeException('Invalid JSON response from AI.');
            }
        }

        // 2. Pastikan hasil parse berbentuk Object/Array terstruktur
        if (!is_array($data)) {
            Log::error("JSON response is not an array. Raw response: " . $response);
            throw new RuntimeException('AI response must be a JSON object.');
        }

        // 3. Pastikan semua key wajib ada dan bertipe data benar
        foreach ($this->schemaRules as $key => $type) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = [];
            }

            if ($type === 'array') {
                if (!is_array($data[$key])) {
                    $data[$key] = [];
                }
            }
        }

        return $data;
    }
}
