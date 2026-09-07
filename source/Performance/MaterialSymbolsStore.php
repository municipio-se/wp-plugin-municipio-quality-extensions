<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final class MaterialSymbolsStore implements MaterialSymbolsStoreInterface
{
    /** @var array<string, string|null> */
    private array $icons = [];
    private readonly MaterialSymbolsIndex $index;
    private readonly MaterialSymbolsPack $pack;

    public function __construct(
        private readonly string $root,
    ) {
        $this->index = new MaterialSymbolsIndex();
        $this->pack = new MaterialSymbolsPack();
    }

    public function getIcon(string $name, string $style, int $weight, int $fill): ?string
    {
        $variant = MaterialSymbolsVariant::fromValues($style, $weight, $fill);
        if ($variant === null || preg_match('/\A[a-z0-9_]+\z/D', $name) !== 1) {
            return null;
        }

        $cacheKey = $variant->id . ':' . $name;
        if (array_key_exists($cacheKey, $this->icons)) {
            return $this->icons[$cacheKey];
        }

        $release = MaterialSymbolsRelease::resolve($this->root);
        $record = $release === null ? null : $this->index->find($release, $variant, $name);
        if ($release === null || $record === null) {
            return $this->icons[$cacheKey] = null;
        }

        return $this->icons[$cacheKey] = $this->pack->read($release, $variant, $record);
    }
}
