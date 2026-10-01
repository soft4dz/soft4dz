<?php

namespace App\Services;

/**
 * Minimal single/multi-page PDF writer (Helvetica core fonts).
 */
class SimplePdf {
    private array $lines = [];
    private float $width = 595.28;
    private float $height = 841.89;
    private float $y;
    private float $margin = 42;
    private string $title;
    private array $pages = [];

    public function __construct(string $title = 'Document') {
        $this->title = $title;
        $this->y = $this->height - $this->margin;
        $this->lines = [];
    }

    public function setFont(string $style = '', int $size = 11): self {
        $font = match ($style) {
            'B', 'bold' => 'F2',
            'I', 'italic' => 'F3',
            default => 'F1',
        };
        $this->lines[] = ['op' => 'font', 'font' => $font, 'size' => $size];
        return $this;
    }

    public function line(string $text, int $size = 11, string $style = ''): self {
        $this->ensureSpace($size + 8);
        $this->setFont($style, $size);
        $this->lines[] = ['op' => 'text', 'x' => $this->margin, 'y' => $this->y, 'text' => $text];
        $this->y -= ($size + 6);
        return $this;
    }

    public function pair(string $label, string $value, int $size = 10): self {
        $this->ensureSpace($size + 8);
        $this->setFont('', $size);
        $this->lines[] = ['op' => 'text', 'x' => $this->margin, 'y' => $this->y, 'text' => $label];
        $this->setFont('B', $size);
        $this->lines[] = ['op' => 'text', 'x' => 210, 'y' => $this->y, 'text' => $value];
        $this->y -= ($size + 6);
        return $this;
    }

    public function spacer(float $h = 10): self {
        $this->y -= $h;
        return $this;
    }

    public function hr(): self {
        $this->ensureSpace(14);
        $this->lines[] = [
            'op' => 'hr',
            'x1' => $this->margin,
            'x2' => $this->width - $this->margin,
            'y'  => $this->y + 2,
        ];
        $this->y -= 12;
        return $this;
    }

    private function ensureSpace(float $needed): void {
        if ($this->y - $needed < $this->margin) {
            $this->flushPage();
        }
    }

    private function flushPage(): void {
        $this->pages[] = $this->lines;
        $this->lines = [];
        $this->y = $this->height - $this->margin;
    }

    private function escape(string $text): string {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        $text = $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '?', $text);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function buildContent(array $ops): string {
        $out = "BT\n/F1 11 Tf\n";
        $inText = true;
        foreach ($ops as $op) {
            if ($op['op'] === 'font') {
                if (!$inText) {
                    $out .= "BT\n";
                    $inText = true;
                }
                $out .= "/{$op['font']} {$op['size']} Tf\n";
            } elseif ($op['op'] === 'text') {
                if (!$inText) {
                    $out .= "BT\n";
                    $inText = true;
                }
                $out .= sprintf(
                    "1 0 0 1 %.2F %.2F Tm (%s) Tj\n",
                    $op['x'],
                    $op['y'],
                    $this->escape($op['text'])
                );
            } elseif ($op['op'] === 'hr') {
                if ($inText) {
                    $out .= "ET\n";
                    $inText = false;
                }
                $out .= sprintf("%.2F %.2F m %.2F %.2F l 0.6 w S\n", $op['x1'], $op['y'], $op['x2'], $op['y']);
            }
        }
        if ($inText) {
            $out .= "ET\n";
        }
        return $out;
    }

    public function output(): string {
        if (!empty($this->lines)) {
            $this->flushPage();
        }
        if (empty($this->pages)) {
            $this->pages[] = [];
        }

        $contents = [];
        foreach ($this->pages as $ops) {
            $contents[] = $this->buildContent($ops);
        }

        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $pageCount = count($contents);
        $pageIds = [];
        $nextId = 3;
        for ($i = 0; $i < $pageCount; $i++) {
            $pageIds[] = $nextId;
            $nextId += 2; // page + content
        }
        $f1 = $nextId;
        $f2 = $nextId + 1;
        $f3 = $nextId + 2;

        $kids = implode(' ', array_map(static fn($id) => $id . ' 0 R', $pageIds));
        $objs[2] = "<< /Type /Pages /Kids [{$kids}] /Count {$pageCount} >>";

        foreach ($contents as $i => $stream) {
            $pageId = $pageIds[$i];
            $contentId = $pageId + 1;
            $objs[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Contents %d 0 R /Resources << /Font << /F1 %d 0 R /F2 %d 0 R /F3 %d 0 R >> >> >>',
                $this->width,
                $this->height,
                $contentId,
                $f1,
                $f2,
                $f3
            );
            $objs[$contentId] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
        }

        $objs[$f1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objs[$f2] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $objs[$f3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique >>';

        ksort($objs);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $size = max(array_keys($objs)) + 1;
        $pdf .= "xref\n0 {$size}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            $off = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $titleEsc = $this->escape($this->title);
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R /Info << /Title ({$titleEsc}) /Producer (Soft4dz) >> >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }

    public function save(string $path): bool {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return file_put_contents($path, $this->output()) !== false;
    }
}
