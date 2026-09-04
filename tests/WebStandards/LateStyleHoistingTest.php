<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\WebStandards {
    use MunicipioQualityExtensions\Tests\Support\WordPressState;
    use MunicipioQualityExtensions\WebStandards\LateStyleHoisting;
    use PHPUnit\Framework\TestCase;

    final class LateStyleHoistingTest extends TestCase
    {
        protected function setUp(): void
        {
            WordPressState::reset();
            require_once dirname(__DIR__) . '/Fixtures/Template.php';
        }

        public function testItRegistersImmediatelyBeforeMunicipiosTemplateRenderer(): void
        {
            (new LateStyleHoisting())->register();

            static::assertCount(1, WordPressState::$filters);
            static::assertSame('template_include', WordPressState::$filters[0]['hook']);
            static::assertSame(14, WordPressState::$filters[0]['priority']);
            static::assertSame(1, WordPressState::$filters[0]['acceptedArgs']);
        }

        public function testItDoesNotRegisterBeforeWordPress69(): void
        {
            WordPressState::$wordpressVersion = '6.8.3';

            (new LateStyleHoisting())->register();

            static::assertSame([], WordPressState::$filters);
        }

        public function testItStartsTheCoreBufferAndPreservesTheTemplateValue(): void
        {
            $template = '/theme/page.php';

            static::assertSame($template, (new LateStyleHoisting())->startBuffer($template));
            static::assertSame(1, WordPressState::$bufferStarts);
        }

        public function testItLeavesBlockThemesToWordPress(): void
        {
            WordPressState::$blockTheme = true;

            (new LateStyleHoisting())->startBuffer('/theme/page.html');

            static::assertSame(0, WordPressState::$bufferStarts);
        }
    }
}
