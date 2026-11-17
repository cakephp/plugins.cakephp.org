<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use App\Utility\ComposerKeyword;
use Cake\TestSuite\TestCase;
use Override;

/**
 * Keyword discovery must preserve catalogue membership and unique pagination.
 */
class KeywordsTest extends TestCase
{
    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = [
        'app.FeaturedPlugins', 'app.Packages', 'app.Categories', 'app.CategoriesPackages',
        'app.Keywords', 'app.KeywordsPackages', 'app.PublisherVersions', 'app.PublisherTagged',
    ];

    /**
     * New keyword matches supplement name and description matches without duplicates.
     */
    public function testSearchMatchesKeywordsAndRetainsWildcards(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        foreach (['sass', 'SASS', 'sa*', 'sa?s', 'asset_compress', 'slider'] as $term) {
            $query = $packages->find('catalogue', filters: ['search' => $term]);
            $this->assertSame(1, $query->count(), $term);
            $this->assertSame(['markstory/asset_compress'], $query->all()->extract('package')->toList());
        }
        $query = $packages->find('catalogue', filters: ['search' => '*s*']);
        $ids = $query->all()->extract('id')->toList();
        $this->assertSame(count(array_unique($ids)), $query->count());
        $this->assertSame(1, $packages->find('catalogue', filters: ['search' => 'my_tool'])->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['search' => 'my%tool'])->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['search' => "sass' OR 1=1 --"])->count());
    }

    /**
     * Exact keywords combine with search, compatibility, curation and namespace scope.
     */
    public function testExactKeywordCombinesWithOtherFilters(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame(1, $packages->find('catalogue', filters: ['keyword' => ' SASS '])->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['keyword' => 'sas'])->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['keyword' => 'unknown'])->count());
        $filters = ['keyword' => 'sass', 'category' => 'asset-management', 'awesome' => true, 'cakephp_slugs' => ['cakephp-5-4']];
        $this->assertSame(1, $packages->find('catalogue', filters: $filters, namespace: 'markstory')->count());
        $this->assertSame(0, $packages->find('catalogue', filters: $filters, namespace: 'vendor')->count());
        $this->assertSame(0, $packages->find('catalogue', filters: $filters + ['search' => 'users'])->count());
        $this->assertSame(0, $packages->find('catalogue', filters: $filters + ['exclude_categories' => ['code-analysis']])->count());
        $this->assertSame(1, $packages->find('catalogue', filters: ['keyword' => "static\t analysis"])->count());
        $this->assertSame(1, $packages->find('catalogue', filters: ['keyword' => 'cakephp'])->count());
    }

    /**
     * Details omit generic vocabulary while retaining it in the stored association.
     */
    public function testDetailsKeywordsAndPackageDeletion(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $package = $packages->get(1, finder: 'publicDetails');
        $this->assertSame(['cakephp', 'less', 'sass'], array_column($package->keywords, 'name'));
        $this->assertSame(['less', 'sass'], array_column($package->visible_keywords, 'name'));
        $this->assertSame([], $packages->get(3, finder: 'publicDetails')->visible_keywords);
        $packages->deleteOrFail($package);
        $this->assertSame(0, $packages->Keywords->junction()->find()->where(['package_id' => 1])->count());
        $this->assertSame(6, $packages->Keywords->find()->count());
    }

    /**
     * Import normalization accepts Unicode and Composer punctuation, not markup or truncation.
     */
    public function testKeywordNormalizationAndValidation(): void
    {
        $this->assertSame('static analysis', ComposerKeyword::normalize("  STATIC\t analysis\n"));
        foreach (['oauth2', 'my_tool', 'dot.name', 'coffee-script', '日本語', str_repeat('a', 191)] as $name) {
            $this->assertTrue(ComposerKeyword::isValid($name), $name);
        }
        foreach (['', 'bad/keyword', '<script>', str_repeat('a', 192)] as $name) {
            $this->assertFalse(ComposerKeyword::isValid($name));
        }
    }
}
