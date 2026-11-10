<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Service\AwesomeListImporter;
use App\Service\PackagistImporter;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use RuntimeException;

/**
 * The command resolves importers from CakePHP's container and orchestrates them.
 */
class SyncPackagesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * A standalone failed source returns a useful exit status to automation.
     */
    public function testStandaloneCurationFailureReturnsAnError(): void
    {
        $packagist = $this->createMock(PackagistImporter::class);
        $packagist->expects($this->never())->method('sync');
        $awesome = $this->createMock(AwesomeListImporter::class);
        $awesome->expects($this->once())->method('sync')->willThrowException(new RuntimeException('Upstream unavailable'));
        $this->mockService(PackagistImporter::class, static fn(): PackagistImporter => $packagist);
        $this->mockService(AwesomeListImporter::class, static fn(): AwesomeListImporter => $awesome);
        $this->exec('sync_packages --source awesome');
        $this->assertExitError();
        $this->assertErrorContains('previous curation retained');
    }

    /**
     * A category-only refresh must never trigger the full Packagist import.
     */
    public function testAwesomeSourceRunsIndependently(): void
    {
        $packagist = $this->createMock(PackagistImporter::class);
        $packagist->expects($this->never())->method('sync');
        $awesome = $this->createMock(AwesomeListImporter::class);
        $awesome->expects($this->once())->method('sync')->willReturn(['matched' => 2, 'unmatched' => []]);
        $this->mockService(PackagistImporter::class, static fn(): PackagistImporter => $packagist);
        $this->mockService(AwesomeListImporter::class, static fn(): AwesomeListImporter => $awesome);
        $this->exec('sync_packages --source awesome');
        $this->assertExitSuccess();
        $this->assertOutputContains('2 plugins matched');
        $this->assertOutputNotContains('Fetching package list');
    }

    /**
     * Packagist can also be refreshed without fetching the curated list.
     */
    public function testPackagistSourceRunsIndependently(): void
    {
        $packagist = $this->createMock(PackagistImporter::class);
        $packagist->expects($this->once())->method('sync')->willReturn([
            'total' => 1, 'saved' => 1, 'skipped' => 0, 'failed' => 0, 'deleted' => 0, 'deleteFailed' => 0,
        ]);
        $awesome = $this->createMock(AwesomeListImporter::class);
        $awesome->expects($this->never())->method('sync');
        $this->mockService(PackagistImporter::class, static fn(): PackagistImporter => $packagist);
        $this->mockService(AwesomeListImporter::class, static fn(): AwesomeListImporter => $awesome);
        $this->exec('sync_packages --source packagist');
        $this->assertExitSuccess();
        $this->assertOutputContains('Saved: 1');
        $this->assertOutputNotContains('Refreshing Awesome');
    }

    /**
     * A successful package sync runs curation afterwards.
     */
    public function testImportsUseContainerServices(): void
    {
        $packagist = $this->createMock(PackagistImporter::class);
        $packagist->expects($this->once())->method('sync')->willReturn([
            'total' => 1, 'saved' => 1, 'skipped' => 0, 'failed' => 0, 'deleted' => 0, 'deleteFailed' => 0,
        ]);
        $awesome = $this->createMock(AwesomeListImporter::class);
        $awesome->expects($this->once())->method('sync')->willReturn(['matched' => 1, 'unmatched' => []]);
        $this->mockService(PackagistImporter::class, static fn(): PackagistImporter => $packagist);
        $this->mockService(AwesomeListImporter::class, static fn(): AwesomeListImporter => $awesome);
        $this->exec('sync_packages');
        $this->assertExitSuccess();
        $this->assertOutputContains('Saved: 1');
        $this->assertOutputContains('1 plugins matched');
    }

    /**
     * A failed curation refresh leaves the completed package import successful.
     */
    public function testCurationFailureIsReported(): void
    {
        $packagist = $this->createMock(PackagistImporter::class);
        $packagist->expects($this->once())->method('sync')->willReturn([
            'total' => 1, 'saved' => 1, 'skipped' => 0, 'failed' => 0, 'deleted' => 0, 'deleteFailed' => 0,
        ]);
        $awesome = $this->createMock(AwesomeListImporter::class);
        $awesome->expects($this->once())->method('sync')->willThrowException(new RuntimeException('Upstream unavailable'));
        $this->mockService(PackagistImporter::class, static fn(): PackagistImporter => $packagist);
        $this->mockService(AwesomeListImporter::class, static fn(): AwesomeListImporter => $awesome);
        $this->exec('sync_packages');
        $this->assertExitSuccess();
        $this->assertErrorContains('previous curation retained');
    }
}
