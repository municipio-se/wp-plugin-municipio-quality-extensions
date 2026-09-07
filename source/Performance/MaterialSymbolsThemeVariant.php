<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final readonly class MaterialSymbolsThemeVariant
{
    private function __construct(
        public string $style,
        public int $weight,
        public int $fill,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromData(array $data): self
    {
        $style = function_exists('get_theme_mod') ? get_theme_mod('icon_style', 'rounded') : 'rounded';
        $weight = function_exists('get_theme_mod') ? get_theme_mod('icon_weight', '400') : '400';
        $filled = $data['filled'] ?? null;
        $filled ??= $data['defaultFilled'] ?? true;

        return new self((string) $style, (int) $weight, $filled ? 1 : 0);
    }
}
