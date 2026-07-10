<?php

namespace App\Services\AI;

class PromptBuilderService 
{
    public function build(array $documents): string 
    {
        return implode("\n\n", [
            $this->buildSection('SYSTEM', $this->loadPrompt('system')),

            $this->buildSection('GUARDRAILS', $this->loadPrompt('guardrails')),

            $this->buildSection('OUTPUT RULES', $this->loadPrompt('output-rules')),

            $this->buildSection('JSON SCHEMA', $this->loadPrompt('schema')),
            
            $this->buildDocuments($documents),
        ]);
    }

    protected function buildSection(string $title, string $content): string
    {
        return implode(PHP_EOL, [
            "===== {$title} =====",
            '',
            trim($content),
        ]);
    }

    protected function loadPrompt(string $name):string
    {
        return file_get_contents(
            resource_path("prompts/brief-parser/{$name}.md")
        );
    }


    protected function buildDocuments(array $documents): string
    {
        $sections = [];

        foreach ($documents as $document) {

            $sections[] = implode(PHP_EOL, [
                '===== DOCUMENT =====',
                'Filename: ' . $document['filename'],
                'Type: ' . ($document['extension'] ?? '-'),
                '',
                $document['content'],
            ]);
        }

        return implode(PHP_EOL . PHP_EOL, [
            'DOCUMENT START',
            '',
            implode(PHP_EOL . PHP_EOL, $sections),
            '',
            'DOCUMENT END',
        ]);
    }


}

