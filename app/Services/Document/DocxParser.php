<?php

namespace App\Services\Document;

use Illuminate\Http\UploadedFile; 
use PhpOffice\PhpWord\IOFactory;
use ZipArchive;

class DocxParser
{
    public function parse(UploadedFile $file): string
    {
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            throw new Exception('Unable to open DOCX file.');
        }

        $content = $zip->getFromName('word/document.xml');

        $zip->close();

        $content = str_replace(
            ['</w:p>', '<w:br/>', '<w:br />'],
            "\n",
            $content
        );

        $xml = strip_tags($content);

        return html_entity_decode($xml);
    }
}