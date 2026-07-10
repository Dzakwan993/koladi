<?php

namespace App\Services\AI;

interface AIProvider
{
    public function generate(string $prompt): string;
}