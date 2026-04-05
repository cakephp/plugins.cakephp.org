<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Users extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('users')
            ->addColumn('first_name', 'string', ['null' => true])
            ->addColumn('last_name', 'string', ['null' => true])
            ->addColumn('email', 'string')
            ->addColumn('username', 'string')
            ->addColumn('is_cakephp_dev', 'boolean', ['default' => false])
            ->addTimestamps('created', 'modified')
            ->addIndex('email', ['unique' => true])
            ->addIndex('username', ['unique' => true])
            ->create();
    }
}
