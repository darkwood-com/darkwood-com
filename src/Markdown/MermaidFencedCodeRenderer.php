<?php

declare(strict_types=1);

namespace App\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use Stringable;

final readonly class MermaidFencedCodeRenderer implements NodeRendererInterface
{
    public function __construct(
        private FencedCodeRenderer $defaultRenderer = new FencedCodeRenderer(),
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        if (!$node instanceof FencedCode) {
            throw new InvalidArgumentException('Expected instance of ' . FencedCode::class);
        }

        if (($node->getInfoWords()[0] ?? '') !== 'mermaid') {
            return $this->defaultRenderer->render($node, $childRenderer);
        }

        return new HtmlElement('pre', ['class' => 'mermaid'], Xml::escape($node->getLiteral()));
    }
}
