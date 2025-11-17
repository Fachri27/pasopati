<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;

class ImportToHtmlService
{
    public function parseToHtml(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("File tidak ditemukan: {$path}");
        }

        $phpWord = IOFactory::load($path);
        $tempFile = tempnam(sys_get_temp_dir(), 'word_html_');

        // Save ke HTML sementara
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
        $htmlWriter->save($tempFile);

        // Ambil isinya
        $html = file_get_contents($tempFile);

        // Ambil isi di dalam <body> aja (tanpa DOCTYPE & <html>)
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Bersihkan spasi berlebih
        $html = trim($html);

        unlink($tempFile);
        return $html;
    }
}
