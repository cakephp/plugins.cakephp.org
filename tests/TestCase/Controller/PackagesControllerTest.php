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
     * The former JSON endpoint is no longer part of the public application.
     */
    public function testAutocompleteEndpointIsRemoved(): void
    {
        $this->get('/autocomplete?q=asset');
        $this->assertResponseCode(404);
    }

    /**
     * Public catalogue documents provide the runtime and navigation metadata.
     */
    public function testIndexLoadsInteractOnceAndProvidesCsrf(): void
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->assertResponseContains('/interact/js/runtime.js');
        $this->assertResponseContains('name="interact-navigation"');
        $this->assertResponseContains('x-interact:model.live="query"');
        $this->assertCookieIsSet('csrfToken');
        $this->assertResponseNotContains('hx-');
    }

    /**
     * The update endpoint is protected even for anonymous public islands.
     */
    public function testIslandUpdateRequiresCsrf(): void
    {
        $this->configRequest(['headers' => ['Content-Type' => 'application/json']]);
        $this->post('/_interact/update', json_encode(['version' => 1], JSON_THROW_ON_ERROR));
        $this->assertResponseCode(403);
    }
}
