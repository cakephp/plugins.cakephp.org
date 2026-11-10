<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Table\PackagesTable;
use App\Service\AwesomeListImporter;
use App\Service\AwesomeListParser;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use Override;
use RuntimeException;
use UnexpectedValueException;

/**
 * Refresh curation atomically without affecting compatibility metadata.
 */
class AwesomeListImporterTest extends TestCase
{
    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.Categories', 'app.CategoriesPackages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Repeated imports deduplicate links and remove obsolete memberships.
     */
    public function testRefreshReconcilesCategoriesWithoutChangingPackages(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $before = $packages->get(1, contain: ['Tags']);
        $importer = new AwesomeListImporter($packages, $this->createStub(Client::class), new AwesomeListParser());
        $markdown = "## Plugins\n### Assets\n- [Assets](https://github.com/MARKSTORY/asset_compress)\n"
            . "- [Duplicate](https://github.com/markstory/asset_compress#feature)\n"
            . "### Development\n- [Assets again](https://github.com/markstory/asset_compress)\n"
            . "- [Missing](https://github.com/vendor/not-in-directory)\n";
        $expected = ['matched' => 1, 'unmatched' => ['github.com/vendor/not-in-directory']];
        $this->assertSame($expected, $importer->import($markdown));
        $this->assertSame($expected, $importer->import($markdown));
        $this->assertSame(2, $packages->Categories->junction()->find()->count());
        $this->assertSame(['assets', 'development'], array_keys($packages->categoryOptions()));
        $after = $packages->get(1, contain: ['Tags', 'Categories']);
        $this->assertEquals($before->tags, $after->tags);
        $this->assertSame($before->latest_stable_version, $after->latest_stable_version);
        $this->assertSame($before->description, $after->description);
        $this->assertCount(2, $after->categories);
        $this->assertSame([], $packages->get(25, contain: ['Categories'])->categories);
    }

    /**
     * An explicit alias resolves shared repositories without guessing.
     */
    public function testAmbiguousRepositoriesRequireAnAlias(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->updateAll(['repo_url' => 'https://github.com/markstory/asset_compress'], ['id' => 2]);
        $importer = new AwesomeListImporter($packages, $this->createStub(Client::class), new AwesomeListParser());
        $markdown = "## Plugins\n### Assets\n- [Assets](https://github.com/markstory/asset_compress)\n";
        $this->assertSame(['matched' => 0, 'unmatched' => ['github.com/markstory/asset_compress']], $importer->import($markdown));
        $this->assertSame(0, $packages->Categories->junction()->find()->count());
        $this->assertSame(['matched' => 1, 'unmatched' => []], $importer->import($markdown, [
            'https://github.com/markstory/asset_compress.git' => 'markstory/asset_compress',
        ]));
        $this->assertCount(1, $packages->get(1, contain: ['Categories'])->categories);
        $this->assertSame([], $packages->get(2, contain: ['Categories'])->categories);
    }

    /**
     * Transport failures preserve previously imported associations.
     */
    public function testFailedFetchRetainsPreviousCuration(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('get')->with(AwesomeListImporter::README_URL, [], ['timeout' => 15])
            ->willReturn(new Response(['HTTP/1.1 503 Service Unavailable'], 'Unavailable'));
        try {
            new AwesomeListImporter($packages, $client, new AwesomeListParser())->sync();
            $this->fail('The failed fetch should have been reported.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('503', $exception->getMessage());
        }
        $this->assertSame(3, $packages->Categories->junction()->find()->count());
    }

    /**
     * Empty or incompatible Markdown never reaches the replacement transaction.
     */
    public function testInvalidSourceRetainsPreviousCuration(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        try {
            new AwesomeListImporter($packages, $this->createStub(Client::class), new AwesomeListParser())->import('# Changed format');
            $this->fail('An empty parse should have been rejected.');
        } catch (UnexpectedValueException) {
            $this->assertSame(3, $packages->Categories->junction()->find()->count());
        }
    }

    /**
     * A failed write rolls back deleted links and partially saved categories.
     */
    public function testWriteFailureRollsBackEntireRefresh(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $categories = $packages->Categories->getTarget();
        $writes = 0;
        $categories->getEventManager()->on('Model.beforeSave', static function () use (&$writes): void {
            if (++$writes === 2) {
                throw new RuntimeException('Simulated storage failure');
            }
        });
        try {
            new AwesomeListImporter($packages, $this->createStub(Client::class), new AwesomeListParser())
                ->import("## Plugins\n### New category\n- [Assets](https://github.com/markstory/asset_compress)\n"
                    . "### Another category\n- [Users](https://github.com/cakedc/users)");
            $this->fail('The write should have failed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated storage failure', $exception->getMessage());
        }
        $this->assertSame(3, $packages->Categories->junction()->find()->count());
        $this->assertSame(3, $categories->find()->count());
    }
}
