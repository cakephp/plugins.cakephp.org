<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Initial extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('packages')
            ->addColumn('package', 'string')
            ->addColumn('description', 'text')
            ->addColumn('repo_url', 'string')
            ->addColumn('downloads', 'integer')
            ->addColumn('stars', 'integer')
            ->create();
    }
}
