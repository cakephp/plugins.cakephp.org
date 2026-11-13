<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddUserAccess extends BaseMigration
{
    /**
     * Existing GitHub accounts start as active ordinary users.
     */
    public function change(): void
    {
        $this->table('users')
            ->addColumn('role', 'string', ['limit' => 16, 'default' => 'user'])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->update();
    }
}
