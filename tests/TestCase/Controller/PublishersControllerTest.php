<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\PackagesTable;
use App\Service\GithubApiService;
use Cake\Cache\Cache;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * The public directory renders without requesting GitHub metadata.
 */
class PublishersControllerTest extends TestCase
{
    use IntegrationTestTrait;

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
     * The first response includes rankings, avatar URLs, and canonical detail links.
     */
    public function testPublicDirectory(): void
    {
        $github = $this->createMock(GithubApiService::class);
        $github->expects($this->never())->method('get');
        $this->mockService(GithubApiService::class, static fn(): GithubApiService => $github);
        $this->get('/publishers');
        $this->assertResponseOk();
        $this->assertResponseContains('data-publisher-namespace="vendor"');
        $this->assertResponseContains('href="/plugins/vendor"');
        $this->assertResponseContains('href="/plugins/vendor/package-01"');
        $this->assertResponseContains('https://github.com/vendor.png?size=96');
        $this->assertResponseContains('action="/publishers"');
        $this->assertResponseContains('data-publisher-rank');
        $this->assertResponseNotContains('data-publisher-namespace="cakedc"');
        $this->assertResponseContains('Monthly downloads');
        $this->assertResponseContains('Latest release');
    }

    /**
     * Native GET search and invalid sorting remain usable without JavaScript.
     */
    public function testSearchAndSortFallback(): void
    {
        $this->get('/publishers?search=MARK&sort=invalid');
        $this->assertResponseOk();
        $this->assertResponseContains('data-publisher-namespace="markstory"');
        $this->assertResponseContains('data-publisher-namespace="dereuromark"');
        $this->assertResponseNotContains('data-publisher-namespace="vendor"');
        $this->assertResponseNotContains('data-publisher-rank');
        $this->get('/publishers?search=no-such-publisher');
        $this->assertResponseOk();
        $this->assertResponseContains('No publishers found');
    }

    /**
     * Older compatible publishers are discoverable through explicit version selection.
     */
    public function testVersionSelectionAndMissingMonthlyData(): void
    {
        $this->get('/publishers?cakephp=4');
        $this->assertResponseOk();
        $this->assertResponseContains('data-publisher-namespace="cakedc"');
        $this->assertResponseNotContains('data-publisher-namespace="vendor"');
        $this->get('/publishers?cakephp=all');
        $this->assertResponseContains('data-publisher-namespace="cakedc"');
        $this->assertResponseContains('data-publisher-namespace="vendor"');
        $this->fetchTable(PackagesTable::class)->updateAll(['monthly_downloads' => null], ['id' => 1]);
        $this->get('/publishers?search=markstory&cakephp=invalid');
        $this->assertResponseOk();
        $this->assertResponseContains('Counts and rankings include plugins compatible with CakePHP 5.');
        $this->assertResponseContains('Awaiting package sync');
    }
}
