<?php

namespace App\Services\AI;

class GeminiProvider implements AIProvider
{
    public function generate(string $prompt): string
    {
        throw new RuntimeException(
            'Gemini provider has not been implemented.'
        );
    }
}
