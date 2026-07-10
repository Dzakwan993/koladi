<?php

namespace App\Services\Document;

use Illuminate\Http\UploadedFile; 
use Smalot\PdfParser\Parser;

class PdfParser
{
    public function parse(UploadedFile $file): string
    {
        $parser = new Parser();

        $pdf = $parser->parseFile($file->getRealPath());

        return $pdf->getText();
    }
}