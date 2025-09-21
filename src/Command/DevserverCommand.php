<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Exception\StopException;
use Symfony\Component\Process\Process;

/**
 * Devserver command.
 */
class DevserverCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'devserver';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'devserver';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Run the `bin/cake server` and `npm run dev` together';
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription());
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $cwd = getcwd();
        if ($cwd === false) {
            throw new StopException('Cannot read CWD');
        }

        // Input is a 'server'.
        // Servers have a name, command to run, and environment vars.
        $io->verbose('Starting bin/cake/server');
        $cakeCommand = ['bin/cake', 'server'];
        $npmCommand = ['npm', 'run', 'dev'];

        $cakeProcess = new Process($cakeCommand, $cwd, ['CAKE_DEVSERVER' => '1', 'PATH' => getenv('PATH')]);
        $npmProcess = new Process($npmCommand, $cwd);

        $cakeProcess->start();
        $io->verbose('Cake server started');
        $npmProcess->start();
        $io->verbose('Starting npm run dev');

        $servers = [
            [
                'name' => 'cake',
                'process' => $cakeProcess,
            ],
            [
                'name' => 'npm',
                'process' => $npmProcess,
            ],
        ];
        $poll = true;
        while ($poll) {
            foreach ($servers as $server) {
                $process = $server['process'];
                $name = $server['name'];

                if (!$process->isRunning()) {
                    $poll = false;
                    $exitCode = $process->getExitCode();
                    $io->error("$name has died with code $exitCode.");
                    $errorOutput = trim($process->getErrorOutput());
                    if ($errorOutput !== '') {
                        $io->error("$name | $errorOutput");
                    }
                    break;
                }

                // Read any incremental output
                $output = $process->getIncrementalOutput();
                if (!empty($output)) {
                    foreach (explode("\n", trim($output)) as $line) {
                        if ($line !== '') {
                            $io->info("$name | $line");
                        }
                    }
                }

                $error = $process->getIncrementalErrorOutput();
                if (!empty($error)) {
                    foreach (explode("\n", trim($error)) as $line) {
                        if ($line !== '') {
                            $io->comment("$name | $line");
                        }
                    }
                }
            }

            usleep(100); // Small delay to prevent high CPU usage
        }

        $io->verbose('Start shutdown');
        foreach ($servers as $server) {
            /** @var \Symfony\Component\Process\Process $process */
            $process = $server['process'];
            if ($process->isRunning()) {
                $process->stop(1); // graceful timeout of 1 second
            }
        }
        $io->out('Shutdown complete');

        // We exit error as, normally this process gets killed with ctrl-c, and we
        // should only get here if a server died.
        return static::CODE_ERROR;
    }
}
