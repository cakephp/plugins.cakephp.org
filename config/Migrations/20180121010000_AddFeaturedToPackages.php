<?php
use Migrations\AbstractMigration;

class AddFeaturedToPackages extends AbstractMigration
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
        $table = $this->table('packages');
        $table->addColumn('featured', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->update();
    }
}
