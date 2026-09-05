<?php

namespace App\Services\AI;

class AIService
{
    public function __construct(
        private AIProvider $provider,
        private PromptBuilderService $promptBuilder,
    private JsonValidatorService $validator,

        // Fungsi beriktu belum jadi digunakan pada MVP
        // private BriefMapperService $mapper,
    ) {

    }

    public function generateBrief(array $documents)
    {
        $prompt = $this->promptBuilder->build($documents);

        $response = $this->provider->generate($prompt);

        return $this->validator->validate($response);

    }
}