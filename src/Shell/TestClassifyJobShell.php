<?php
namespace App\Shell;

use App\Job\Performer;
use Cake\Console\Shell;

/**
 * TestClassifyJob shell command.
 */
class TestClassifyJobShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addArgument('package_id', [
            'help' => 'ID of package to classify'
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        if (empty($this->args[0])) {
            $this->err('Missing package_id argument');
            return false;
        }

        $callable = ['\App\Job\ClassifyJob','perform'];
        $parameters = ['package_id' => $this->args[0]];
        $performer = new Performer($callable, $parameters);
        return $performer->execute();
    }
}
