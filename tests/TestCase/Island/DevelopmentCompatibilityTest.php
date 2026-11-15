<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use App\Model\Table\PackagesTable;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;
use Override;

class DevelopmentCompatibilityTest extends TestCase
{
    use IslandTestTrait;

    #[Override]
    protected array $fixtures = ['app.Packages', 'app.FeaturedPlugins', 'app.Categories', 'app.CategoriesPackages', 'app.PublisherVersions', 'app.PublisherTagged'];

    /**
     * Dev-only plugins are discoverable explicitly without changing stable totals or rankings.
     */
    public function testDevelopmentSelectionAndStableDefaults(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $development = [6 => [
            'version' => 'dev-next', 'stability' => 'dev',
            'cakephp_constraint' => '6.x-dev', 'php_constraint' => '^8.5',
        ]];
        $package = $packages->newEntity([
            'package' => 'future/preview', 'description' => 'A plugin under development.',
            'repo_url' => 'https://github.com/future/preview', 'downloads' => 0, 'monthly_downloads' => 0, 'stars' => 0,
            'latest_stable_version' => null, 'latest_stable_release_date' => null,
            'development_compatibility' => $development, 'tag_list' => ['CakePHP: 6.x@dev', 'PHP: 8.5'],
        ]);
        $packages->saveOrFail($package);
        Cache::clear('catalogue');
        // MySQL normalizes JSON object key order; MariaDB preserves insertion order.
        $this->assertEquals($development, $packages->get($package->id)->development_compatibility);
        $this->assertSame(26, $packages->find('catalogue', filters: [])->count());
        $this->assertSame(['major' => '5', 'plugins' => 25], $packages->ecosystemStats()['current_support']);
        $this->assertSame(26, $packages->ecosystemStats()['plugins']);
        $this->assertSame(4, $packages->ecosystemStats()['publishers']);
        $this->assertSame(['5', '4'], $packages->publisherMajors());
        $this->assertSame(4, $packages->unhydratedFind('publishers')->count());
        $this->testIsland('Catalogue')->assertDontSee('data-plugin-name="future/preview"')
            ->set('cakephpSlugs', ['cakephp-6-x-dev'])->assertSee('future/preview')->assertSee('CakePHP 6.x@dev · PHP 8.x')
            ->assertDontSee('cakedc/users')->call('clearFilters')->assertDontSee('data-plugin-name="future/preview"');
        $this->testIsland('Catalogue', ['namespace' => 'future'])->set('cakephpSlugs', ['cakephp-6-x-dev'])->assertSee('future/preview');
        $this->testIsland('Publishers')->assertDontSee('future')->set('cakephp', '6.x@dev')->assertSee('future')->assertDontSee('markstory');
        $details = $packages->unhydratedFind('publisherDetails', namespaces: ['future'], major: '6.x@dev')->all()->toArray();
        $this->assertSame('future/preview', $details['future']['plugins'][0]['package']);
        $this->testIsland('PluginDetails')->call('show', $package->id)
            ->assertDontSee('Development support')->assertSee('dev-next')->assertSee('6.x@dev')
            ->assertSee("composer require 'future/preview:dev-next' 'cakephp/cakephp:6.x-dev'");
    }
}
