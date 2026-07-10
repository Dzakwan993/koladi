<?php

namespace App\Services\AI;

class BriefMapperService
{
    public function map(array $data): array
    {
        return [
            'project' => [
                'name' => $data['project']['name'] ?? $data['name'] ?? 'Proyek Tanpa Nama',
                'goal' => $data['project']['goal'] ?? $data['summary'] ?? '',
                'deliverables' => $data['project']['deliverables'] ?? (is_array($data['deliverables'] ?? null) ? implode(', ', $data['deliverables']) : ($data['deliverables'] ?? '')),
                'deadline' => $data['project']['deadline'] ?? $data['deadline'] ?? null,
                'confidence_level' => $data['project']['confidence_level'] ?? $data['confidence_level'] ?? 90,
            ],
            'tasks' => array_map(function ($task) {
                return [
                    'title' => $task['title'] ?? 'Tugas Baru',
                    'description' => $task['description'] ?? '',
                    'priority' => strtolower($task['priority'] ?? 'medium'),
                    'deadline' => $task['deadline'] ?? null,
                    'suggested_owner' => $task['suggested_owner'] ?? null,
                ];
            }, $data['tasks'] ?? []),
            'clarification_questions' => $data['clarification_questions'] ?? $data['missing_information'] ?? [],
        ];
    }
}
