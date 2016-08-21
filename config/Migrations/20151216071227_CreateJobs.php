<?php
use Migrations\AbstractMigration;

class CreateJobs extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('jobs');
        $table->addColumn('queue', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => false,
        ]);
        $table->addColumn('data', 'text', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('priority', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('expires_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('delay_until', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('locked', 'integer', [
            'default' => null,
            'limit' => 1,
            'null' => false,
        ]);
        $table->addIndex([
            'queue',
            'locked',
        ], [
            'name' => 'is_locked',
            'unique' => false,
        ]);
        $table->create();
    }
}
