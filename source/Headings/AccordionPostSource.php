<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

final class AccordionPostSource
{
    public function get(mixed $post, mixed $id): ?string
    {
        if (
            !is_object($post)
            || !is_callable([$post, '__get'])
            || !is_callable([$post, 'getId'])
            || (string) $post->getId() !== (string) $id
        ) {
            return null;
        }
        // Read the legacy backing value, not the rewritten public property:
        // it retains original depths and upstream's password protection.
        $source = $post->__get('postContentFiltered');
        return is_string($source) ? $source : null;
    }

    public function prepareData(array $data): array
    {
        foreach ($data['prepareAccordion'] ?? [] as $index => $row) {
            $source = $this->get($data['posts'][$index] ?? null, $row['attributeList']['data-js-item-id'] ?? null);
            if (is_string($source)) {
                $data['prepareAccordion'][$index] = (new AccordionContent())->prepareRow($row, $source);
            }
        }
        return $data;
    }
}
