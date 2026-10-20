<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Headings;

/** Remove selected empty headings without serializing their surrounding markup. */
final class EmptyHeadingTags extends \WP_HTML_Tag_Processor
{
    public function remove(array $indices): string
    {
        $index = -1;
        $open = null;
        $spans = [];
        while ($this->next_tag(['tag_closers' => 'visit'])) {
            $tag = $this->get_tag() ?? '';
            if (!preg_match('/^H[1-6]$/D', $tag)) {
                continue;
            }
            $this->set_bookmark('qx-empty-heading');
            $span = $this->bookmarks['qx-empty-heading'];
            $this->release_bookmark('qx-empty-heading');
            if (!$this->is_tag_closer()) {
                if ($open !== null) {
                    return $this->html;
                }
                $open = [$tag, $span->start];
                $index++;
                continue;
            }
            if ($open === null || $open[0] !== $tag) {
                return $this->html;
            }
            if (in_array($index, $indices, true)) {
                $spans[$open[1]] = $span->start + $span->length - $open[1];
            }
            $open = null;
        }
        if ($open !== null) {
            return $this->html;
        }
        $html = $this->html;
        krsort($spans);
        foreach ($spans as $start => $length) {
            $html = substr_replace($html, '', $start, $length);
        }
        return $html;
    }
}
