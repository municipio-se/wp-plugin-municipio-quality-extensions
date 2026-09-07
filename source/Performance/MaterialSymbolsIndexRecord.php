<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

final readonly class MaterialSymbolsIndexRecord
{
    private const MAXIMUM_SVG_BYTES = 131_072;

    private function __construct(
        public int $offset,
        public int $length,
        public string $sha256,
    ) {}

    public static function fromMixed(mixed $record): ?self
    {
        if (!is_array($record)) {
            return null;
        }

        $offset = $record['offset'] ?? null;
        $length = $record['length'] ?? null;
        $sha256 = $record['sha256'] ?? null;
        if (
            !is_int($offset)
            || !is_int($length)
            || !is_string($sha256)
            || preg_match('/\A[0-9a-f]{64}\z/D', $sha256) !== 1
            || $offset < 0
            || $length < 1
            || $length > self::MAXIMUM_SVG_BYTES
        ) {
            return null;
        }

        return new self($offset, $length, $sha256);
    }
}
