<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

final class AccordionViewData
{
    public function prepareData(array $data): array
    {
        $type = $data['post_type'] ?? '';
        if ($type === 'mod-posts' && ($data['posts_display_as'] ?? '') === 'expandable-list') {
            return (new AccordionPostSource())->prepareData($data);
        }
        if (
            $type === 'mod-manualinput'
            && in_array('module.manual-input.accordion', (array) ($data['context'] ?? []), true)
        ) {
            foreach ($data['manualInputs'] ?? [] as $index => $row) {
                if (is_string($row['content'] ?? '')) {
                    $data['manualInputs'][$index] = (new AccordionContent())->prepareRow($row, $row['content'] ?? '');
                }
            }
        }
        return $data;
    }
}
