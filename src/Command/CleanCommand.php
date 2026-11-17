<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\PackagesTable;
use Cake\Cache\Cache;
use Cake\Command\Command;
use Override;

/**
 * Clean command.
 */
class CleanCommand extends Command
{
    /**
     * The name of this command.
     */
    #[Override]
    protected string $name = 'clean';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'clean';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Truncate all packages from the database.';
    }

    /**
     * Implement this method with your command's logic.
     *
     * @return void The exit code or null for success
     */
    public function execute(): void
    {
        $confirmation = $this->io->ask(
            'Are you sure you want to clean all packages data and related tags? This action cannot be undone. (yes/no)',
        );
        if (strtolower($confirmation) !== 'yes' && strtolower($confirmation) !== 'y') {
            $this->io->out('Aborting.');

            return;
        }
        $packagesTable = $this->fetchTable(PackagesTable::class);
        $allPackages = $packagesTable->find()->all();
        foreach ($allPackages as $package) {
            $packagesTable->delete($package);
        }
        Cache::clear('catalogue');
    }
}
