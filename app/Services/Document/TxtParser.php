<?php
namespace App\Services\Document;

use Illuminate\Http\UploadedFile; 

class TxtParser
{
    public function parse(UploadedFile $file):string
    {
        return file_get_contents($file->getRealPath());
    }
}