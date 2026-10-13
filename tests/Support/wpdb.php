<?php

declare(strict_types=1);

// The test suite does not boot WordPress. Real SQL is covered by the isolated integration run.
class wpdb
{
    public string $postmeta = 'wp_postmeta';
    public string $last_error = '';
    public array $rows = [];
    public array $ddl = [];
    public bool $locked = false;
    public bool $busy = false;
    public bool $failDdl = false;

    public function suppress_errors(bool $suppress): bool
    {
        return false;
    }

    public function prepare(string $sql, string $value): string
    {
        return str_replace('%s', "'" . $value . "'", $sql);
    }

    public function get_var(string $sql): string
    {
        if (str_contains($sql, 'GET_LOCK')) {
            $this->locked = !$this->busy;
            return $this->busy ? '0' : '1';
        }
        if (str_contains($sql, 'RELEASE_LOCK')) {
            $this->locked = false;
        }
        return 'database';
    }

    public function get_results(string $sql, string $format): array
    {
        $this->last_error = '';
        return $this->rows;
    }

    public function query(string $sql): int|false
    {
        $this->ddl[] = $sql;
        if ($this->failDdl) {
            $this->last_error = 'Simulated DDL failure';
            return false;
        }
        if (str_contains($sql, ' ADD INDEX ')) {
            $this->seed('municipio_qx_navigation');
        } else {
            $this->rows = array_values(array_filter(
                $this->rows,
                static fn(array $row): bool => $row['Key_name'] !== 'municipio_qx_navigation',
            ));
        }
        return 0;
    }

    public function seed(string $name): void
    {
        foreach ([['meta_key', 32], ['meta_value', 1], ['post_id', null]] as $i => [$column, $prefix]) {
            $this->rows[] = [
                'Key_name' => $name,
                'Seq_in_index' => $i + 1,
                'Column_name' => $column,
                'Sub_part' => $prefix,
                'Index_type' => 'BTREE',
                'Ignored' => 'NO',
            ];
        }
    }
}
