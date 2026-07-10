<?php

namespace App\Services\AI;

class AIService
{
    public function __construct(
        private AIProvider $provider,
        private PromptBuilderService $promptBuilder,
        private JsonValidatorService $validator,
        private BriefMapperService $mapper,
    ) {

    }

    public function generateBrief(array $documents) 
    {
        $prompt = $this->promptBuilder->build($documents);

        $response = $this->provider->generate($prompt);

        $validated = $this->validator->validate($response);

        return $this->mapper->map($validated);
    }
}