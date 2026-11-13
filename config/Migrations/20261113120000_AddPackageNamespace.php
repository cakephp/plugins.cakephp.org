<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPackageNamespace extends BaseMigration
{
    /**
     * Backfill the Composer vendor before requiring it on future inserts.
     */
    public function up(): void
    {
        $this->table('packages')
            ->addColumn('namespace', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->update();

        $this->execute("UPDATE packages SET namespace = LOWER(SUBSTR(package, 1, INSTR(package, '/') - 1))");

        $this->table('packages')
            ->changeColumn('namespace', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addIndex(['namespace'])
            ->update();
    }

    /**
     * The full Composer name remains available after rollback.
     */
    public function down(): void
    {
        $this->table('packages')
            ->removeIndex(['namespace'])
            ->removeColumn('namespace')
            ->update();
    }
}
