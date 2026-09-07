<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsPack
{
    public function read(string $release, MaterialSymbolsVariant $variant, MaterialSymbolsIndexRecord $record): ?string
    {
        $path = $release . '/' . $variant->id . '.pack';
        if (!is_readable($path)) {
            return null;
        }

        $pack = fopen($path, 'rb');
        if ($pack === false) {
            return null;
        }

        try {
            $svg = $this->readRange($pack, $record);
        } finally {
            fclose($pack);
        }

        return $this->isValid($svg, $record) ? $svg : null;
    }

    /** @param resource $pack */
    private function readRange($pack, MaterialSymbolsIndexRecord $record): ?string
    {
        if (fseek($pack, $record->offset) !== 0) {
            return null;
        }

        $svg = '';
        while (strlen($svg) < $record->length && !feof($pack)) {
            $chunk = fread($pack, $record->length - strlen($svg));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $svg .= $chunk;
        }

        return strlen($svg) === $record->length ? $svg : null;
    }

    private function isValid(?string $svg, MaterialSymbolsIndexRecord $record): bool
    {
        return (
            is_string($svg)
            && hash_equals($record->sha256, hash('sha256', $svg))
            && str_starts_with($svg, '<svg ')
            && str_contains($svg, '<path ')
            && !str_contains(strtolower($svg), '<script')
        );
    }
}
