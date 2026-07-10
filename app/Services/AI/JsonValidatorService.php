<?php

namespace App\Services\AI;

class JsonValidatorService
{
    public function validate(string $response): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response.');
        }

        return $data;

    }
}

