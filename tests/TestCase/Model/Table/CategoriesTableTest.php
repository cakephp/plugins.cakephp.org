<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CategoriesTable;
use Cake\TestSuite\TestCase;
use Override;

/**
 * Category projections remain composable and preserve their slug keys.
 */
class CategoriesTableTest extends TestCase
{
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.Categories', 'app.CategoriesPackages'];

    /**
     * Formatting works for hydrated and unhydrated queries, after caller conditions.
     */
    public function testCatalogueOptionsComposition(): void
    {
        $categories = $this->fetchTable(CategoriesTable::class);
        foreach ([$categories->find('catalogueOptions'), $categories->unhydratedFind('catalogueOptions')] as $query) {
            $this->assertSame([
                'code-analysis' => ['name' => 'Code Analysis', 'count' => 1],
            ], $query->where(['Categories.slug' => 'code-analysis'])->all()->toArray());
        }
    }

    /**
     * A standalone category finder scopes counts without loading reverse associations.
     */
    public function testCatalogueOptionsNamespaceScope(): void
    {
        $categories = $this->fetchTable(CategoriesTable::class);
        $this->assertCount(2, $categories->unhydratedFind('catalogueOptions', namespace: 'markstory')->all()->toArray());
        $this->assertSame([], $categories->unhydratedFind('catalogueOptions', namespace: 'vendor')->all()->toArray());
    }
}
