<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\AwesomeListParser;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use UnexpectedValueException;

/**
 * Parse the curated list structurally instead of treating every URL as a plugin.
 */
class AwesomeListParserTest extends TestCase
{
    /**
     * Only primary links under plugin category headings are imported.
     */
    public function testCategoryBoundariesAndPrimaryLinks(): void
    {
        $markdown = <<<'MD'
# Awesome CakePHP
## Resources
### Books
- [Book](https://github.com/example/book)
## Plugins
### Asset **Management**
- [AssetCompress](https://github.com/MarkStory/asset_compress.git/) - uses [Other](https://github.com/example/secondary).
- See [another section](https://github.com/example/reference).
- Plain text
- [Hosted elsewhere](https://example.com/vendor/plugin)
### Code `Analysis`
- [Asset feature](https://github.com/markstory/asset_compress/blob/main/README.md#feature)
## Software
### Apps
- [App](https://github.com/example/app)
MD;
        $this->assertSame([
            'asset-management' => ['name' => 'Asset Management', 'repositories' => ['github.com/markstory/asset_compress']],
            'code-analysis' => ['name' => 'Code Analysis', 'repositories' => ['github.com/markstory/asset_compress']],
        ], new AwesomeListParser()->parse($markdown));
    }

    /**
     * Repository identities match across casing, SSH and feature URLs.
     */
    #[DataProvider('repositoryProvider')]
    public function testRepositoryNormalization(string $url, ?string $expected): void
    {
        $this->assertSame($expected, new AwesomeListParser()->repository($url));
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function repositoryProvider(): array
    {
        return [
            'ssh' => ['git@github.com:Vendor/Plugin.git', 'github.com/vendor/plugin'],
            'https' => ['https://www.github.com/Vendor/Plugin/?tab=readme', 'github.com/vendor/plugin'],
            'feature' => ['https://github.com/Vendor/Plugin/tree/main/src#feature', 'github.com/vendor/plugin'],
            'other host' => ['https://gitlab.com/vendor/plugin', null],
            'missing repository' => ['https://github.com/vendor', null],
        ];
    }

    /**
     * A changed or empty source cannot silently clear previous curation.
     */
    public function testMissingPluginEntriesAreRejected(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new AwesomeListParser()->parse('## Plugins' . "\n### Empty\nNo entries.");
    }
}
