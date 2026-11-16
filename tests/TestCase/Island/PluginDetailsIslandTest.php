<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;
use Override;

/**
 * Details load only after choosing a plugin, through the island transport.
 */
class PluginDetailsIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.Keywords', 'app.KeywordsPackages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Both useful links and the empty state render through the shared details element.
     */
    public function testKeywordsAreShownOnlyWhenUseful(): void
    {
        $this->testIsland('PluginDetails')->call('show', 1)
            ->assertSee('Keywords')->assertSee('keyword=sass')
            ->assertSee('Provided by the plugin author.')->assertDontSee('keyword=cakephp')
            ->call('show', 3)->assertDontSee('Keywords');
    }

    /**
     * Opening another plugin replaces the previous plugin's content.
     */
    public function testDetailsLoadOnDemand(): void
    {
        $this->testIsland('PluginDetails')
            ->assertSet('packageId', null)
            ->assertDontSee('composer require')
            ->call('show', 1)
            ->assertSee('composer require markstory/asset_compress')
            ->assertSee('https://github.com/markstory/asset_compress')
            ->assertSee('5.0.0')
            ->call('show', 25)
            ->assertSee('composer require cakedc/users')
            ->assertDontSee('composer require markstory/asset_compress');
    }

    /**
     * Missing records use the ORM's normal not-found handling.
     */
    public function testMissingPlugin(): void
    {
        $island = $this->testIsland('PluginDetails');
        $this->expectException(RecordNotFoundException::class);
        $island->call('show', 99999);
    }
}
