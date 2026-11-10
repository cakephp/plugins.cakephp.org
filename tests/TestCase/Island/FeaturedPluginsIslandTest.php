<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;

/**
 * Exercises the featured selection independently of catalogue pagination.
 */
class FeaturedPluginsIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = ['app.Packages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Refreshing preserves the shuffled order and honors removed configuration entries.
     */
    public function testSelectionOrderSurvivesRefresh(): void
    {
        Configure::write('Packages.featured', ['markstory/asset_compress', 'cakedc/users']);
        $island = $this->testIsland('FeaturedPlugins');
        $order = $island->result()->state['order'];
        $island->sync()->assertSet('order', $order)
            ->assertSee('markstory/asset_compress')->assertSee('cakedc/users');

        Configure::write('Packages.featured', ['cakedc/users']);
        $island->sync()->assertSee('cakedc/users')->assertDontSee('markstory/asset_compress');
    }

    /**
     * Empty or missing configured packages leave no slider controls behind.
     */
    public function testMissingSelectionHasNoSlider(): void
    {
        Configure::write('Packages.featured', []);
        $this->testIsland('FeaturedPlugins')->assertDontSee('data-featured-packages-slider');
        Configure::write('Packages.featured', ['missing/package']);
        $this->testIsland('FeaturedPlugins')->assertDontSee('data-featured-packages-slider');
    }
}
