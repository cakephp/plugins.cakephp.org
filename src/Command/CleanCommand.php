<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/**
 * Clean command.
 */
class CleanCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
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
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $confirmation = $io->ask(
            'Are you sure you want to clean all packages data and related tags? This action cannot be undone. (yes/no)',
        );
        if (strtolower($confirmation) !== 'yes' && strtolower($confirmation) !== 'y') {
            $io->out('Aborting.');

            return;
        }

        $packagesTable = $this->fetchTable('Packages');
        $allPackages = $packagesTable->find()->all();

        foreach ($allPackages as $package) {
            $packagesTable->delete($package);
        }
    }
}
