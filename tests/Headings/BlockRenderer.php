<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Headings;

final class BlockRenderer
{
    public function render(array $block): string
    {
        return serialize($block);
    }
}
