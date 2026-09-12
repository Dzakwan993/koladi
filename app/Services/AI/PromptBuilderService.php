<?php

namespace App\Services\AI;

use Illuminate\Support\Str;

class PromptBuilderService 
{
    public function build(array $documents, array $existingTasks = []): string 
    {
        $sections = [
            $this->buildSection('SYSTEM', $this->loadPrompt('system')),

            $this->buildSection('GUARDRAILS', $this->loadPrompt('guardrails')),

            $this->buildSection('OUTPUT RULES', $this->loadPrompt('output-rules')),

            $this->buildSection('JSON SCHEMA', $this->loadPrompt('schema')),
        ];

        if (!empty($existingTasks)) {
            $sections[] = $this->buildExistingTasks($existingTasks);
        }

        $sections[] = $this->buildDocuments($documents);

        return implode("\n\n", $sections);
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

    protected function buildExistingTasks(array $tasks): string
    {
        $lines = [];
        foreach ($tasks as $task) {
            $id = $task['id'] ?? '-';
            $title = $task['title'] ?? 'Untitled';
            $phase = !empty($task['phase']) ? " | Fase: {$task['phase']}" : '';
            $deadline = !empty($task['due_datetime']) ? " | Deadline: " . (is_string($task['due_datetime']) ? substr($task['due_datetime'], 0, 10) : $task['due_datetime']) : '';
            $desc = !empty($task['description']) ? " | Desc: " . Str::limit(trim(preg_replace('/\s+/', ' ', $task['description'])), 120) : '';

            $lines[] = "- [ID: {$id}] \"{$title}\"{$phase}{$deadline}{$desc}";
        }

        return implode(PHP_EOL, [
            '===== EXISTING ACTIVE TASKS IN THIS PROJECT =====',
            'Daftar task yang saat ini aktif di Kanban:',
            implode(PHP_EOL, $lines),
            '',
            'Tentukan apakah bahasan dokumen merupakan update dari salah satu task di atas (action: "update") atau task baru (action: "create").',
        ]);
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

