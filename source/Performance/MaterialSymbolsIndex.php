<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsIndex
{
    /** @var array<string, array<string, MaterialSymbolsIndexRecord>> */
    private array $indexes = [];

    public function find(string $release, MaterialSymbolsVariant $variant, string $name): ?MaterialSymbolsIndexRecord
    {
        $cacheKey = $release . ':' . $variant->id;
        if (!array_key_exists($cacheKey, $this->indexes)) {
            $this->indexes[$cacheKey] = $this->load($release . '/' . $variant->id . '.json');
        }

        return $this->indexes[$cacheKey][$name] ?? null;
    }

    /** @return array<string, MaterialSymbolsIndexRecord> */
    private function load(string $path): array
    {
        if (!is_readable($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        $decoded = is_string($contents) ? json_decode($contents, true) : null;
        $records = is_array($decoded) ? $decoded['icons'] ?? null : null;
        if (!is_array($records)) {
            return [];
        }

        $validated = [];
        foreach ($records as $name => $value) {
            $record = MaterialSymbolsIndexRecord::fromMixed($value);
            if (is_string($name) && preg_match('/\A[a-z0-9_]+\z/D', $name) === 1 && $record !== null) {
                $validated[$name] = $record;
            }
        }

        return $validated;
    }
}
