<?php

namespace App\Services;

use App\Models\AiKnowledgeEntry;
use App\Models\WhatsappAiAssistant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiKnowledgeService
{
    /** Tamaño máximo (caracteres) que se incluirá en el system prompt en cada llamada */
    public const MAX_PROMPT_CHARS = 16000;

    /**
     * Extrae texto de un archivo subido. Soporta TXT/MD nativo,
     * y PDF/DOCX si las extensiones están disponibles.
     */
    public function extractText(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?? '';
        $ext  = strtolower($file->getClientOriginalExtension());

        // Texto plano
        if (str_starts_with($mime, 'text/') || in_array($ext, ['txt', 'md', 'csv', 'log'], true)) {
            return $this->normalize(@file_get_contents($file->getRealPath()) ?: '');
        }

        // PDF — intentamos pdftotext si está disponible en el sistema
        if ($mime === 'application/pdf' || $ext === 'pdf') {
            $extracted = $this->tryPdfToText($file->getRealPath());
            if ($extracted !== null) return $this->normalize($extracted);
        }

        // DOCX — intentamos extraer XML interno si zip está disponible
        if (in_array($ext, ['docx'], true) || $mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $extracted = $this->tryDocxText($file->getRealPath());
            if ($extracted !== null) return $this->normalize($extracted);
        }

        // Fallback: intentar leer como texto crudo
        $raw = @file_get_contents($file->getRealPath()) ?: '';
        if (mb_check_encoding($raw, 'UTF-8')) {
            return $this->normalize($raw);
        }

        return '';
    }

    private function tryPdfToText(string $path): ?string
    {
        // 1) Intentar usar el binario `pdftotext` si está instalado
        $bin = trim((string) @shell_exec('which pdftotext 2>/dev/null'));
        if ($bin) {
            $out = @shell_exec(escapeshellcmd($bin) . ' -layout ' . escapeshellarg($path) . ' - 2>/dev/null');
            if (is_string($out) && trim($out) !== '') {
                return $out;
            }
        }

        // 2) Si está disponible smalot/pdfparser
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf    = $parser->parseFile($path);
                return $pdf->getText();
            } catch (\Throwable $e) {
                Log::warning('PDF parse falló: ' . $e->getMessage());
            }
        }

        return null;
    }

    private function tryDocxText(string $path): ?string
    {
        if (!class_exists(\ZipArchive::class)) return null;

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return null;
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) return null;

        // Quitar etiquetas XML pero conservando saltos
        $xml = str_replace(['</w:p>', '</w:tab>'], "\n", $xml);
        $text = strip_tags($xml);
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function normalize(string $text): string
    {
        // Normalizar saltos de línea y eliminar líneas vacías excesivas
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", (string) $text);
        return trim((string) $text);
    }

    /**
     * Construye el bloque de contexto a insertar en el system prompt
     * a partir de las entradas activas, respetando límite de chars.
     */
    public function buildPromptContext(WhatsappAiAssistant $assistant): string
    {
        $entries = AiKnowledgeEntry::where('whatsapp_ai_assistant_id', $assistant->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($entries->isEmpty()) return '';

        $out  = "BASE DE CONOCIMIENTO (información oficial de tu empresa, úsala para responder):\n\n";
        $used = mb_strlen($out);

        foreach ($entries as $e) {
            $title = $e->title ?: ($e->original_filename ?? 'Documento');
            $body  = (string) $e->content;

            // Si excede el límite, recortar
            $remaining = self::MAX_PROMPT_CHARS - $used;
            if ($remaining < 200) break; // ya casi no hay espacio

            $header = "### {$title}\n";
            if (mb_strlen($body) + mb_strlen($header) + 2 > $remaining) {
                $body = mb_substr($body, 0, max(100, $remaining - mb_strlen($header) - 80))
                      . "\n…(documento truncado)";
            }

            $block = $header . $body . "\n\n";
            $out  .= $block;
            $used += mb_strlen($block);
        }

        return $out;
    }
}
