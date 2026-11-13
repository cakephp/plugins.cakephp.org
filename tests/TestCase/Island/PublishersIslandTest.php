<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use App\Model\Table\PackagesTable;
use App\Service\GithubApiService;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;
use Override;

/**
 * Directory state is transported independently of request-local aggregate results.
 */
class PublishersIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.PublisherVersions', 'app.PublisherTagged'];

    /**
     * Version options must reflect this test's compatibility fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete('package_filter_tags_v1');
    }

    /**
     * Filtering and sorting do not trigger external profile lookups.
     */
    public function testSearchSortAndClear(): void
    {
        $github = $this->createMock(GithubApiService::class);
        $github->expects($this->never())->method('get');
        $this->mockService(GithubApiService::class, static fn(): GithubApiService => $github);
        $this->testIsland('Publishers')
            ->assertSee('data-publisher-rank')
            ->assertDontSee('/plugins/cakedc')
            ->set('cakephp', 'all')
            ->set('sort', 'stars')
            ->assertSet('sort', 'stars')
            ->set('search', 'cakedc')
            ->assertSee('/plugins/cakedc')
            ->assertDontSee('/plugins/vendor')
            ->assertDontSee('data-publisher-rank')
            ->call('clearSearch')
            ->assertSet('search', '')
            ->assertSet('sort', 'stars')
            ->assertSet('cakephp', 'all')
            ->set('sort', 'invalid')
            ->assertSet('sort', 'monthly_downloads');
    }

    /**
     * Search changes return to page one and rank numbering spans pages.
     */
    public function testPaginationAndFilterReset(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $record = $packages->get(1)->toArray();
        unset($record['id']);
        $record['tag_list'] = ['CakePHP: 5.4'];
        for ($i = 1; $i <= 21; $i++) {
            $name = sprintf('publisher-%02d', $i);
            $record['package'] = $name . '/plugin';
            $packages->saveOrFail($packages->newEntity($record));
        }
        $this->testIsland('Publishers')
            ->set('paging.page', 2)
            ->assertSet('paging.page', 2)
            ->assertSee('Rank 21')
            ->set('cakephp', '4')
            ->assertSet('paging.page', 1)
            ->assertSee('/plugins/cakedc')
            ->set('cakephp', 'current')
            ->set('search', 'vendor')
            ->assertSet('paging.page', 1)
            ->assertDontSee('data-publisher-rank');
    }
}
