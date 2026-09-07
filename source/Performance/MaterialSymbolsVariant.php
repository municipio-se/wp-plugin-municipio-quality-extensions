<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final readonly class MaterialSymbolsVariant
{
    private const ALLOWED_STYLES = ['outlined', 'rounded', 'sharp'];
    private const ALLOWED_WEIGHTS = [200, 400, 600];
    private const ALLOWED_FILLS = [0, 1];

    private function __construct(
        public string $id,
    ) {}

    public static function fromValues(string $style, int $weight, int $fill): ?self
    {
        if (
            !in_array($style, self::ALLOWED_STYLES, true)
            || !in_array($weight, self::ALLOWED_WEIGHTS, true)
            || !in_array($fill, self::ALLOWED_FILLS, true)
        ) {
            return null;
        }

        return new self(sprintf('%s-wght%d-fill%d', $style, $weight, $fill));
    }
}
