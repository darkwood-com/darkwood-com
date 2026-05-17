<?php

declare(strict_types=1);

namespace App\Tests\Markdown;

use App\Markdown\MermaidExtension;
use League\CommonMark\CommonMarkConverter;
use PHPUnit\Framework\TestCase;

final class MermaidExtensionTest extends TestCase
{
    public function testRendersMermaidFencedBlockAsPreMermaid(): void
    {
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new MermaidExtension());

        $html = (string) $converter->convert("```mermaid\nflowchart LR\n    A --> B\n```");

        self::assertStringContainsString('<pre class="mermaid">', $html);
        self::assertStringContainsString('flowchart LR', $html);
        self::assertStringNotContainsString('language-mermaid', $html);
    }

    public function testLeavesOtherFencedBlocksUnchanged(): void
    {
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new MermaidExtension());

        $html = (string) $converter->convert("```php\necho 1;\n```");

        self::assertStringContainsString('<code class="language-php">', $html);
    }
}
