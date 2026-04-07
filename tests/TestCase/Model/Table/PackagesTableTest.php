<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PackagesTable Test Case
 */
class PackagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PackagesTable
     */
    protected $Packages;

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
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Packages') ? [] : ['className' => PackagesTable::class];
        $this->Packages = $this->getTableLocator()->get('Packages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Packages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PackagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findAutocomplete returns results matching the package name.
     *
     * @return void
     * @link \App\Model\Table\PackagesTable::findAutocomplete()
     */
    public function testFindAutocompleteMatchesPackageName(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'asset_compress')->toArray();

        $this->assertNotEmpty($results);
        $this->assertSame('markstory/asset_compress', $results[0]->package);
    }

    /**
     * Test findAutocomplete respects the limit option.
     *
     * @return void
     */
    public function testFindAutocompleteLimit(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'package', maxResults: 3)->toArray();

        $this->assertCount(3, $results);
    }

    /**
     * Test findAutocomplete default limit is 8.
     *
     * @return void
     */
    public function testFindAutocompleteDefaultLimit(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'package')->toArray();

        $this->assertLessThanOrEqual(8, count($results));
    }

    /**
     * Test findAutocomplete returns empty results for non-matching search.
     *
     * @return void
     */
    public function testFindAutocompleteNoResults(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'zzzznonexistent')->toArray();

        $this->assertEmpty($results);
    }

    /**
     * Test findAutocomplete prioritizes name matches over description-only matches.
     *
     * "users" appears in cakedc/users package name and also in the description
     * of dereuromark/cakephp-tools ("useful helpers"). The package with "users"
     * in its name should rank first even if the other has more downloads.
     *
     * @return void
     */
    public function testFindAutocompletePrioritizesNameMatch(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'users')->toArray();

        $this->assertNotEmpty($results);
        $this->assertSame('cakedc/users', $results[0]->package);
    }

    /**
     * Test findAutocomplete orders by downloads within the same match type.
     *
     * @return void
     */
    public function testFindAutocompleteOrdersByDownloads(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'package')->toArray();

        $count = count($results);
        $this->assertGreaterThanOrEqual(2, $count);
        for ($i = 1; $i < $count; $i++) {
            $this->assertGreaterThanOrEqual(
                $results[$i]->downloads,
                $results[$i - 1]->downloads,
                'Results should be ordered by downloads descending',
            );
        }
    }

    /**
     * Test findAutocomplete matches against description field.
     *
     * @return void
     */
    public function testFindAutocompleteMatchesDescription(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'slider')->toArray();

        $this->assertNotEmpty($results);
        $this->assertSame('markstory/asset_compress', $results[0]->package);
    }

    /**
     * Test findAutocomplete contains tags.
     *
     * @return void
     */
    public function testFindAutocompleteContainsTags(): void
    {
        $results = $this->Packages->find('autocomplete', search: 'asset_compress')->toArray();

        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('tags', $results[0]->toArray());
    }
}
