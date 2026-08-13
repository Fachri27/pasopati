<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;

class ImportToHtmlService
{
    private function sofficePath(): string
    {
        $candidates = [
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
            '/usr/bin/libreoffice',
            '/usr/local/bin/libreoffice',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return 'libreoffice';
    }

    public function parseToHtml(string $path): string
    {
        if (! file_exists($path)) {
            throw new \Exception("File tidak ditemukan: {$path}");
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return $this->parsePdfToHtml($path);
        }

        return $this->parseWithLibreOffice($path);
    }

    private function parseWithLibreOffice(string $path): string
    {
        $soffice = $this->sofficePath();
        $outDir = sys_get_temp_dir();

        $outputPath = $outDir.'/'.pathinfo($path, PATHINFO_FILENAME).'.html';

        $cmd = sprintf(
            '%s --headless --convert-to html:"HTML" --outdir %s %s 2>&1',
            escapeshellcmd($soffice),
            escapeshellarg($outDir),
            escapeshellarg($path)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || ! file_exists($outputPath)) {
            return $this->parseWordToHtmlFallback($path);
        }

        $html = file_get_contents($outputPath);
        unlink($outputPath);

        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        return trim($html);
    }

    private function parseWordToHtmlFallback(string $path): string
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
        $tempFile = tempnam(sys_get_temp_dir(), 'word_html_');

        $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $htmlWriter->save($tempFile);

        $html = file_get_contents($tempFile);

        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        $html = trim($html);
        unlink($tempFile);

        return $html;
    }

    private function parsePdfToHtml(string $path): string
    {
        $soffice = $this->sofficePath();

        $outDir = sys_get_temp_dir();
        $outputPath = $outDir.'/'.pathinfo($path, PATHINFO_FILENAME).'.html';

        $cmd = sprintf(
            '%s --headless --convert-to html:"HTML" --outdir %s %s 2>&1',
            escapeshellcmd($soffice),
            escapeshellarg($outDir),
            escapeshellarg($path)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode === 0 && file_exists($outputPath)) {
            $html = file_get_contents($outputPath);
            unlink($outputPath);
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                return trim($matches[1]);
            }

            return trim($html);
        }

        $parser = new PdfParser;
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        $text = trim($text);
        if (empty($text)) {
            return '<p><em>[PDF tidak mengandung teks yang bisa diekstrak]</em></p>';
        }

        $paragraphs = preg_split('/\n\s*\n/', $text);
        $html = '';
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para !== '') {
                $html .= '<p>'.e($para).'</p>';
            }
        }

        return $html;
    }
}
