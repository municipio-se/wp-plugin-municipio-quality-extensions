<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

interface MaterialSymbolsStoreInterface
{
    public function getIcon(string $name, string $style, int $weight, int $fill): ?string;
}
