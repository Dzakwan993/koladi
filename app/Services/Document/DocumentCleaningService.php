<?php

namespace App\Services\Document;

class DocumentCleaningService 
{
    public function clean(string $text): string
    {
        // Samakan line ending
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Hilangkan spasi berlebih
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // Maksimal 2 newline
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        // Pastikan UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        return trim($text);
    }
        
    

}
