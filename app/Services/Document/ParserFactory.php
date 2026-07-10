<?php

namespace App\Services\Document;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class ParserFactory
{
    public static function make(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
           'pdf' => new PdfParser(), 
           'docx' => new DocxParser(), 
           'txt' => new TxtParser(),

           default => throw new InvalidArgumentException( 
            "Unsupported file type: {$extension}" 
            ),
        };
    }


}