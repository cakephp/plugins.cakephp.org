<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;
use Override;

/**
 * Exercises the featured selection independently of catalogue pagination.
 */
class FeaturedPluginsIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Refreshing preserves the shuffled order and honors removed configuration entries.
     */
    public function testSelectionOrderSurvivesRefresh(): void
    {
        Configure::write('Packages.featured', [
            'markstory/asset_compress' => ['logo' => 'https://example.com/asset-compress.svg'],
            'cakedc/users' => ['logo' => null],
        ]);
        $island = $this->testIsland('FeaturedPlugins');
        $order = $island->result()->state['order'];
        $island->sync()->assertSet('order', $order)
            ->assertSee('markstory/asset_compress')->assertSee('cakedc/users')
            ->assertSee('https://example.com/asset-compress.svg');

        Configure::write('Packages.featured', ['cakedc/users' => ['logo' => null]]);
        $island->sync()->assertSee('cakedc/users')->assertDontSee('markstory/asset_compress');
    }

    /**
     * Empty or missing configured packages leave no featured entries behind.
     */
    public function testMissingSelectionHasNoFeaturedEntries(): void
    {
        Configure::write('Packages.featured', []);
        $this->testIsland('FeaturedPlugins')->assertDontSee('data-featured-plugins');
        Configure::write('Packages.featured', ['missing/package' => ['logo' => null]]);
        $this->testIsland('FeaturedPlugins')->assertDontSee('data-featured-plugins');
    }

    /**
     * The static recommendation section contains at most three configured packages.
     */
    public function testSelectionIsBoundedToThreePackages(): void
    {
        Configure::write('Packages.featured', [
            'markstory/asset_compress' => ['logo' => null],
            'cakedc/users' => ['logo' => null],
            'dereuromark/cakephp-tools' => ['logo' => null],
            'vendor/package-01' => ['logo' => null],
        ]);
        $island = $this->testIsland('FeaturedPlugins');
        $this->assertSame(3, substr_count($island->result()->html, 'data-plugin-tone='));
    }
}
