<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

/**
 * DOM supplies ownership, while WordPress supplies source spans. Re-serializing
 * a module with libxml would also change unrelated SVG attributes and entities.
 */
final class HeadingTags extends \WP_HTML_Tag_Processor
{
    public function replace(array $headings, array $levels): string
    {
        $index = 0;
        $open = null;
        $replacements = [];
        while ($this->next_tag(['tag_closers' => 'visit'])) {
            $tag = strtolower($this->get_tag() ?? '');
            if (!preg_match('/^h[1-6]$/D', $tag)) {
                continue;
            }
            if (!$this->is_tag_closer()) {
                $expected = $open === null ? $headings[$index] ?? null : null;
                $open = $index++;
            } else {
                $expected = $headings[$open] ?? null;
            }
            if ($expected !== $tag) {
                return $this->html;
            }
            if (($levels[$open] ?? null) !== null) {
                $replacements[$this->levelOffset()] = $levels[$open];
            }
            if ($this->is_tag_closer()) {
                $open = null;
            }
        }
        if ($open !== null || $index !== count($headings)) {
            return $this->html;
        }
        $html = $this->html;
        foreach ($replacements as $offset => $level) {
            $html[$offset] = $level;
        }
        return $html;
    }

    private function levelOffset(): int
    {
        $this->set_bookmark('qx-heading');
        $offset = $this->bookmarks['qx-heading']->start + ($this->is_tag_closer() ? 3 : 2);
        $this->release_bookmark('qx-heading');
        return $offset;
    }
}
