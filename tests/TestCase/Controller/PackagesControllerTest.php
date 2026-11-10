<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;

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
    #[Override]
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
        Configure::write('Packages.featured', ['markstory/asset_compress' => ['logo' => null]]);
    }

    /**
     * @return void
     */
    public function testIndexShowsFeaturedSectionOnFirstPageWithoutFilters(): void
    {
        $this->get('/?sort=downloads&direction=desc');

        $this->assertResponseOk();
        $this->assertResponseContains('Featured');
        $this->assertResponseContains('data-featured-plugins');
        $this->assertResponseContains('Latest release');
    }

    /**
     * @return void
     */
    public function testIndexHidesFeaturedSectionOnSubsequentPages(): void
    {
        $this->get('/?sort=downloads&direction=desc&page=2');

        $this->assertResponseOk();
        $this->assertResponseNotContains('data-featured-plugins');
        $this->assertResponseNotContains('swiper');
    }

    /**
     * @return void
     */
    public function testIndexHidesFeaturedSectionWhenSearching(): void
    {
        $this->get('/?sort=downloads&direction=desc&search=package-02');

        $this->assertResponseOk();
        $this->assertResponseNotContains('data-featured-plugins');
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
     * Detail links have a public page fallback with the same plugin information.
     */
    public function testPublicPluginDetails(): void
    {
        $this->get('/plugins/1');
        $this->assertResponseOk();
        $this->assertResponseContains('composer require markstory/asset_compress');
        $this->assertResponseContains('https://packagist.org/packages/markstory/asset_compress');
    }

    /**
     * The normal detail route returns a 404 for missing plugins.
     */
    public function testMissingPluginDetails(): void
    {
        $this->get('/plugins/99999');
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
        $this->assertResponseContains('x-interact:model.live="search"');
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
