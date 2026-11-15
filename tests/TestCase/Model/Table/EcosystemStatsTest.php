<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use Cake\TestSuite\TestCase;
use Override;

/**
 * Global counts use listed packages and deduplicate their compatibility tags.
 */
class EcosystemStatsTest extends TestCase
{
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.PublisherVersions', 'app.PublisherTagged'];

    /**
     * Several plugins from one namespace count as one publisher.
     */
    public function testGlobalTotals(): void
    {
        $this->assertSame([
            'plugins' => 26,
            'publishers' => 4,
            'monthly_downloads' => 5351,
            'current_support' => ['major' => '5', 'plugins' => 25],
        ], $this->fetchTable(PackagesTable::class)->ecosystemStats());
    }

    /**
     * Count current support once per package and exclude unrelated or orphaned tags.
     */
    public function testCompatibilityCountsUniqueListedPackages(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        foreach ([[25, 'Articles'], [9999, 'Packages']] as [$id, $model]) {
            $packages->getConnection()->insert('tags_tagged', [
                'tag_id' => 2,
                'fk_id' => $id,
                'fk_model' => $model,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ]);
        }
        $this->assertSame(['major' => '5', 'plugins' => 25], $packages->ecosystemStats()['current_support']);
    }

    /**
     * Missing metadata does not invent a current compatibility figure.
     */
    public function testNoCompatibilityMetadata(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->getConnection()->delete('tags_tagged', []);
        $packages->getConnection()->delete('tags_tags', []);
        $stats = $packages->ecosystemStats();
        $this->assertNull($stats['current_support']);
        $this->assertSame(26, $stats['plugins']);
    }

    /**
     * A partial download sum must not appear to be a complete ecosystem total.
     */
    public function testMissingMonthlyCountRemainsUnknown(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->updateAll(['monthly_downloads' => null], ['id' => 1]);
        $this->assertNull($packages->ecosystemStats()['monthly_downloads']);
    }

    /**
     * An empty catalogue produces finite zero totals.
     */
    public function testEmptyCatalogue(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->deleteAll([]);
        $this->assertSame([
            'plugins' => 0,
            'publishers' => 0,
            'monthly_downloads' => 0,
            'current_support' => ['major' => '5', 'plugins' => 0],
        ], $packages->ecosystemStats());
    }
}
