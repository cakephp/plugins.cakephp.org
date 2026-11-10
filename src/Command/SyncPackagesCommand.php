<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\AwesomeListImporter;
use App\Service\PackagistImporter;
use Cake\Command\Command;
use Cake\Console\Arguments;
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
     * @param \Cake\Console\Arguments $args Command arguments.
     * @param \Cake\Console\ConsoleIo $io Console output.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        if ($args->getOption('source') !== 'awesome') {
            $this->importPackages($io);
        }
        if ($args->getOption('source') !== 'packagist' && !$this->importCuration($io)) {
            return $args->getOption('source') === 'awesome' ? self::CODE_ERROR : self::CODE_SUCCESS;
        }

        return self::CODE_SUCCESS;
    }

    /**
     * Report package import progress and cleanup results.
     */
    private function importPackages(ConsoleIo $io): void
    {
        $io->out('Fetching package list from Packagist...');
        /** @var \Cake\Console\Helper\ProgressHelper $progress */
        $progress = $io->helper('Progress');
        $result = $this->packagist->sync(static function (string $name, int $current, int $total) use ($io, $progress): void {
            if ($current === 1) {
                $io->out(sprintf('Found <info>%d</info> packages. Processing...', $total));
                $progress->init(['total' => $total, 'width' => 60]);
            }
            $io->out(sprintf('[%d/%d] %s', $current, $total, $name), 1, ConsoleIo::VERBOSE);
            $progress->increment()->draw();
        });
        $io->out('');
        $io->out(sprintf('Sync complete. Saved: %d, Skipped: %d, Failed: %d', $result['saved'], $result['skipped'], $result['failed']));
        $io->out(sprintf('Cleanup complete. Deleted: %d, Failed: %d', $result['deleted'], $result['deleteFailed']));
    }

    /**
     * A failed curation refresh must not invalidate a completed package import.
     */
    private function importCuration(ConsoleIo $io): bool
    {
        $io->out('Refreshing Awesome CakePHP categories...');
        try {
            $curation = $this->awesome->sync();
            $io->out(sprintf('Awesome CakePHP: %d plugins matched, %d repository links unmatched.', $curation['matched'], count($curation['unmatched'])));
            foreach ($curation['unmatched'] as $repository) {
                $io->out('Unmatched or ambiguous: ' . $repository, 1, ConsoleIo::VERBOSE);
            }

            return true;
        } catch (Throwable $exception) {
            $io->warning('Awesome CakePHP refresh failed; previous curation retained. ' . $exception->getMessage());
            Log::warning('Awesome CakePHP refresh failed: ' . $exception->getMessage());

            return false;
        }
    }
}
