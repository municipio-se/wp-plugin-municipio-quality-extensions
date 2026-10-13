<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Performance;

use Closure;
use RuntimeException;
use wpdb;

final class NavigationIndexSchema
{
    public function __construct(
        private readonly wpdb $db,
    ) {}

    public function tableName(): string
    {
        return $this->db->postmeta;
    }

    private function table(): string
    {
        return '`' . str_replace('`', '``', $this->db->postmeta) . '`';
    }

    public function indexes(): array
    {
        $previous = $this->db->suppress_errors(true);
        try {
            $rows = $this->db->get_results('SHOW INDEX FROM ' . $this->table(), ARRAY_A);
            if ($this->db->last_error !== '' || !is_array($rows)) {
                throw new RuntimeException('Could not inspect the current site metadata indexes.');
            }
        } finally {
            $this->db->suppress_errors($previous);
        }
        $indexes = [];
        foreach ($rows as $row) {
            $indexes[$row['Key_name']][(int) $row['Seq_in_index']] = [
                $row['Column_name'],
                $row['Sub_part'] === null ? null : (int) $row['Sub_part'],
                $row['Index_type'],
                ($row['Ignored'] ?? 'NO') === 'NO' && ($row['Visible'] ?? 'YES') === 'YES',
            ];
        }
        return $indexes;
    }

    public static function matches(array $columns): bool
    {
        ksort($columns);
        return (
            array_values($columns) === [
                ['meta_key',   32,   'BTREE', true],
                ['meta_value', 1,    'BTREE', true],
                ['post_id',    null, 'BTREE', true],
            ]
        );
    }

    public function alter(string $clause): void
    {
        $previous = $this->db->suppress_errors(true);
        $sql = 'ALTER TABLE ' . $this->table() . ' ' . $clause;
        try {
            if ($this->db->query($sql) === false) {
                throw new RuntimeException('Index DDL failed: ' . $this->db->last_error);
            }
        } finally {
            $this->db->suppress_errors($previous);
        }
    }

    public function locked(Closure $operation): array
    {
        // Advisory locks are server-wide; hash the database and current blog table to keep sites independent.
        $database = $this->db->get_var('SELECT DATABASE()');
        if (!is_string($database) || $database === '') {
            throw new RuntimeException('Could not identify the current database.');
        }
        $name = hash('sha256', $database . '.' . $this->db->postmeta);
        if ((string) $this->db->get_var($this->db->prepare('SELECT GET_LOCK(%s, 0)', $name)) !== '1') {
            throw new RuntimeException(
                'Another index migration is running, or database advisory locks are unavailable.',
            );
        }
        try {
            return $operation();
        } finally {
            $this->db->get_var($this->db->prepare('SELECT RELEASE_LOCK(%s)', $name));
        }
    }
}
