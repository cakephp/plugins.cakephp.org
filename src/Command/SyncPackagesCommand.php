<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\AwesomeListImporter;
use App\Service\PackagistImporter;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Override;
use Throwable;

/**
 * Runs package imports with console progress and summaries.
 */
class SyncPackagesCommand extends Command
{
    #[Override]
    protected string $name = 'sync_packages';

    /**
     * Importers are supplied by CakePHP's command factory and container.
     */
    public function __construct(
        private readonly PackagistImporter $packagist,
        private readonly AwesomeListImporter $awesome,
    ) {
        parent::__construct();
    }

    /**
     * Get the default command name.
     */
    public static function defaultName(): string
    {
        return 'sync_packages';
    }

    /**
     * Describe the complete catalogue refresh.
     */
    public static function getDescription(): string
    {
        return 'Sync CakePHP plugins from Packagist and categories from Awesome CakePHP';
    }

    /**
     * Select a single source without changing the default complete refresh.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)->addOption('source', [
            'help' => 'Import all sources, Packagist packages only, or Awesome CakePHP categories only.',
            'default' => 'all',
            'choices' => ['all', 'packagist', 'awesome'],
        ]);
    }

    /**
     * Refresh the selected catalogue sources using CakePHP's command context.
     */
    public function execute(): int
    {
        if ($this->args->getOption('source') !== 'awesome') {
            $this->importPackages();
        }
        if ($this->args->getOption('source') !== 'packagist' && !$this->importCuration()) {
            return $this->args->getOption('source') === 'awesome' ? self::CODE_ERROR : self::CODE_SUCCESS;
        }

        return self::CODE_SUCCESS;
    }

    /**
     * Report package import progress and cleanup results.
     */
    private function importPackages(): void
    {
        $this->io->out('Fetching package list from Packagist...');
        /** @var \Cake\Console\Helper\ProgressHelper $progress */
        $progress = $this->io->helper('Progress');
        $result = $this->packagist->sync(function (string $name, int $current, int $total) use ($progress): void {
            if ($current === 1) {
                $this->io->out(sprintf('Found <info>%d</info> packages. Processing...', $total));
                $progress->init(['total' => $total, 'width' => 60]);
            }
            $this->io->out(sprintf('[%d/%d] %s', $current, $total, $name), 1, ConsoleIo::VERBOSE);
            $progress->increment()->draw();
        });
        $this->io->out('');
        $this->io->out(sprintf('Sync complete. Saved: %d, Skipped: %d, Failed: %d', $result['saved'], $result['skipped'], $result['failed']));
        $this->io->out(sprintf('Cleanup complete. Deleted: %d, Failed: %d', $result['deleted'], $result['deleteFailed']));
    }

    /**
     * A failed curation refresh must not invalidate a completed package import.
     */
    private function importCuration(): bool
    {
        $this->io->out('Refreshing Awesome CakePHP categories...');
        try {
            $curation = $this->awesome->sync();
            $this->io->out(sprintf('Awesome CakePHP: %d plugins matched, %d repository links unmatched.', $curation['matched'], count($curation['unmatched'])));
            foreach ($curation['unmatched'] as $repository) {
                $this->io->out('Unmatched or ambiguous: ' . $repository, 1, ConsoleIo::VERBOSE);
            }

            return true;
        } catch (Throwable $exception) {
            $this->io->warning('Awesome CakePHP refresh failed; previous curation retained. ' . $exception->getMessage());
            Log::warning('Awesome CakePHP refresh failed: ' . $exception->getMessage());

            return false;
        }
    }
}
