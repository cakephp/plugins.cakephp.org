<?php
declare(strict_types=1);

namespace App\Service;

/**
 * A category being assembled while walking the curated Markdown document.
 */
final class ParsedCategory
{
    /**
     * @var list<string>
     */
    public array $repositories = [];

    /**
     * Keep the heading stable while its repository entries are collected.
     */
    public function __construct(public readonly string $name)
    {
    }

    /**
     * @return array{name: string, repositories: list<string>}
     */
    public function toArray(): array
    {
        return ['name' => $this->name, 'repositories' => $this->repositories];
    }
}
