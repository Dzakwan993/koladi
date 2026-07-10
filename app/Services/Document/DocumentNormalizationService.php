<?php

namespace App\Services\Document;

class DocumentNormalizationService
{
    public function normalize(array $document): array
    {
        $document['content'] = $this->removeBom($document['content']);

        $document['content'] = $this->removeHorizontalLine($document['content']);

        $document['content'] = $this->normalizeBullets($document['content']);

        $document['content'] = $this->normalizeColonSpacing($document['content']);

        $document['content'] = $this->prependMetadata($document);

        return $document;
    }

    private function normalizeBullets(string $text): string
    {
        return str_replace(
            ['•', '◦', '▪'],
            '-',
            $text
        );
    }

    private function normalizeColonSpacing(string $text): string
    {
        return preg_replace('/\s*:\s*/', ': ', $text);
    }

    private function prependMetadata(array $document): string
    {
        return
            "=== DOCUMENT ===\n" .
            "Filename: {$document['filename']}\n" .
            "Type: {$document['extension']}\n\n" .
            $document['content'];
    }

    private function removeBom(string $text): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $text);
    }

    private function removeHorizontalLine(string $text): string
    {
        return preg_replace('/^[-_=]{5,}$/m', '', $text);
    }

}