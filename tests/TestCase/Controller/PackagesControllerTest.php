<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\PackagesController Test Case
 *
 * @link \App\Controller\PackagesController
 */
class PackagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Packages',
        'plugin.Tags.Tags',
        'plugin.Tags.Tagged',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Configure::write('Packages.featured', ['markstory/asset_compress']);
    }

    /**
     * @return void
     */
    public function testIndexShowsFeaturedSliderOnFirstPageWithoutFilters(): void
    {
        $this->get('/?sort=downloads&direction=desc');

        $this->assertResponseOk();
        $this->assertResponseContains('Featured');
        $this->assertResponseContains('data-featured-packages-slider');
        $this->assertResponseContains('Latest Release');
    }

    /**
     * @return void
     */
    public function testIndexHidesFeaturedSliderOnSubsequentPages(): void
    {
        $this->get('/?sort=downloads&direction=desc&page=2');

        $this->assertResponseOk();
        $this->assertResponseNotContains('data-featured-packages-slider');
        $this->assertResponseNotContains('Previous featured package');
    }

    /**
     * @return void
     */
    public function testIndexHidesFeaturedSliderWhenSearching(): void
    {
        $this->get('/?sort=downloads&direction=desc&search=package-02');

        $this->assertResponseOk();
        $this->assertResponseNotContains('data-featured-packages-slider');
        $this->assertResponseContains('vendor/package-02');
    }

    /**
     * @return void
     */
    public function testAutocompleteReturnsJsonResults(): void
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/autocomplete?q=asset');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = (string)$this->_response->getBody();
        $results = json_decode($body, true);

        $this->assertNotEmpty($results);
        $this->assertSame('markstory/asset_compress', $results[0]['package']);
        $this->assertArrayHasKey('description', $results[0]);
        $this->assertArrayHasKey('repo_url', $results[0]);
        $this->assertArrayHasKey('downloads', $results[0]);
        $this->assertArrayHasKey('stars', $results[0]);
        $this->assertArrayHasKey('latest_version', $results[0]);
        $this->assertArrayHasKey('cakephp_versions', $results[0]);
        $this->assertArrayHasKey('php_versions', $results[0]);
    }

    /**
     * @return void
     */
    public function testAutocompleteReturnsEmptyForShortQuery(): void
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/autocomplete?q=a');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = (string)$this->_response->getBody();
        $results = json_decode($body, true);

        $this->assertEmpty($results);
    }

    /**
     * @return void
     */
    public function testAutocompleteReturnsEmptyForMissingQuery(): void
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/autocomplete');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = (string)$this->_response->getBody();
        $results = json_decode($body, true);

        $this->assertEmpty($results);
    }

    /**
     * @return void
     */
    public function testAutocompleteReturnsEmptyForNoMatch(): void
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/autocomplete?q=zzzznonexistent');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $body = (string)$this->_response->getBody();
        $results = json_decode($body, true);

        $this->assertEmpty($results);
    }

    /**
     * @return void
     */
    public function testAutocompleteLimitsResults(): void
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/autocomplete?q=package');

        $this->assertResponseOk();

        $body = (string)$this->_response->getBody();
        $results = json_decode($body, true);

        $this->assertLessThanOrEqual(8, count($results));
    }
}
