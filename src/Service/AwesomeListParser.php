<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Utility\Text;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\Text as TextNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use UnexpectedValueException;

/**
 * Reads category headings and primary plugin links from the curated Markdown list.
 */
class AwesomeListParser
{
    /**
     * @return array<string, array{name: string, repositories: list<string>}>
     */
    public function parse(string $markdown): array
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $document = new MarkdownParser($environment)->parse($markdown);
        $inPlugins = false;
        $category = null;
        $categories = [];
        foreach ($document->children() as $node) {
            if ($node instanceof Heading) {
                $name = $this->text($node);
                if ($node->getLevel() === 2) {
                    $inPlugins = $name === 'Plugins';
                    $category = null;
                } elseif ($inPlugins && $node->getLevel() === 3) {
                    $category = new ParsedCategory($name);
                    $categories[mb_strtolower(Text::slug($name))] = $category;
                }
                continue;
            }
            if (!$inPlugins || !$category instanceof ParsedCategory || !$node instanceof ListBlock) {
                continue;
            }
            foreach ($node->children() as $item) {
                $link = $item->firstChild()?->firstChild();
                if (!$link instanceof Link) {
                    continue;
                }
                $repository = $this->repository($link->getUrl());
                if ($repository !== null) {
                    $category->repositories[] = $repository;
                }
            }
        }
        $categories = array_filter($categories, static fn(ParsedCategory $category): bool => $category->repositories !== []);
        if ($categories === []) {
            throw new UnexpectedValueException('Awesome CakePHP contained no linked plugin entries; keeping existing curation.');
        }

        return array_map(static fn(ParsedCategory $category): array => $category->toArray(), $categories);
    }

    /**
     * Normalize GitHub repository identities, including links to specific features.
     */
    public function repository(string $url): ?string
    {
        $url = preg_replace('#^git@github\.com:#i', 'https://github.com/', trim($url)) ?? $url;
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $parts = explode('/', trim((string)parse_url($url, PHP_URL_PATH), '/'));
        if (!in_array($host, ['github.com', 'www.github.com'], true) || count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }
        $name = preg_replace('/\.git$/i', '', $parts[1]);

        return strtolower('github.com/' . $parts[0] . '/' . $name);
    }

    /**
     * Collect heading text without Markdown formatting.
     */
    private function text(Node $node): string
    {
        $text = '';
        foreach ($node->iterator() as $child) {
            if ($child instanceof TextNode || $child instanceof Code) {
                $text .= $child->getLiteral();
            }
        }

        return trim($text);
    }
}
