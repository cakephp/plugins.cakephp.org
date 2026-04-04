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
}
