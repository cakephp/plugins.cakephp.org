<?php
namespace Users\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

/**
 * User shell command.
 */
class UserShell extends Shell
{

    /**
     * main() method.
     *
     * @return bool|int Success or error code.
     */
    public function main()
    {
        $config = Configure::read('Users');
        $data = [];
        $fields = [
            $config['fields']['username'],
            $config['fields']['password'],
        ];
        foreach ($fields as $field) {
            $value = null;
            $fieldName = Inflector::humanize($field);
            while (empty($value)) {
                $value = $this->in(sprintf('%s?', $fieldName));
            }
            $data[$field] = $value;
        }

        $this->out('');
        $continue = $this->in('Continue?', ['y', 'n'], 'n');
        if ($continue !== 'y') {
            return $this->error('User not saved.');
        }
        $this->out('');
        $this->hr();

        $table = TableRegistry::get($config['userModel']);
        $entity = $table->newEntity($data, ['validate' => false]);
        if (!$table->save($entity)) {
            return $this->error(sprintf('User could not be inserted: %s', print_r($entity->errors(), true)));
        }
        $this->out(sprintf('User inserted! ID: %d, Data: %s', $entity->id, print_r($entity->toArray(), true)));
    }


    /**
     * UserShell
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description('The User shell can create a user on the fly for local development.');

        return $parser;
    }
}
