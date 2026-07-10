<?php

namespace App\Services\Document;

use Illuminate\Http\UploadedFile;

class DocumentParserService
{
    public function __construct(
        protected DocumentCleaningService $cleaningService,
        protected DocumentNormalizationService $normalizationService
    ) {}

    public function parse(array $files): array
    {
        $results = [];

        foreach ($files as $file) {

            $parser = ParserFactory::make($file);

            $text = $parser->parse($file);

            $text = $this->cleaningService->clean($text);

            $document = [
                'filename' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'content' => $text,
            ];

            $document = $this->normalizationService->normalize($document);

            $results[] = $document;
        }

        return $results;
    }
}