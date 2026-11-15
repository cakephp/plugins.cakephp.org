<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Exception\StopException;
use Override;
use Symfony\Component\Process\Process;

/**
 * Devserver command.
 */
class DevserverCommand extends Command
{
    /**
     * The name of this command.
     */
    #[Override]
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
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription());
    }

    /**
     * Implement this method with your command's logic.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $cwd = getcwd();
        if ($cwd === false) {
            throw new StopException('Cannot read CWD');
        }
        // Input is a 'server'.
        // Servers have a name, command to run, and environment vars.
        $this->io->verbose('Starting bin/cake/server');
        $cakeCommand = ['bin/cake', 'server'];
        $npmCommand = ['npm', 'run', 'dev'];
        $cakeProcess = new Process($cakeCommand, $cwd, ['CAKE_DEVSERVER' => '1', 'PATH' => getenv('PATH')]);
        $npmProcess = new Process($npmCommand, $cwd);
        $cakeProcess->start();
        $this->io->verbose('Cake server started');
        $npmProcess->start();
        $this->io->verbose('Starting npm run dev');
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
                    $this->io->error(sprintf('%s has died with code %s.', $name, $exitCode));
                    $errorOutput = trim($process->getErrorOutput());
                    if ($errorOutput !== '') {
                        $this->io->error(sprintf('%s | %s', $name, $errorOutput));
                    }
                    break;
                }

                // Read any incremental output
                $output = $process->getIncrementalOutput();
                if (!empty($output)) {
                    foreach (explode("\n", trim($output)) as $line) {
                        if ($line !== '') {
                            $this->io->info(sprintf('%s | %s', $name, $line));
                        }
                    }
                }

                $error = $process->getIncrementalErrorOutput();
                if (!empty($error)) {
                    foreach (explode("\n", trim($error)) as $line) {
                        if ($line !== '') {
                            $this->io->comment(sprintf('%s | %s', $name, $line));
                        }
                    }
                }
            }

            usleep(100); // Small delay to prevent high CPU usage
        }
        $this->io->verbose('Start shutdown');
        foreach ($servers as $server) {
            $process = $server['process'];
            if ($process->isRunning()) {
                $process->stop(1); // graceful timeout of 1 second
            }
        }
        $this->io->out('Shutdown complete');
        // We exit error as, normally this process gets killed with ctrl-c, and we
        // should only get here if a server died.
        return static::CODE_ERROR;
    }
}
