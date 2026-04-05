<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class MissingIndex extends BaseMigration
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
        $this->table('packages')
            ->addIndex(['package'], ['unique' => true])
            ->addIndex(['downloads'])
            ->addIndex(['stars'])
            ->update();
    }
}
