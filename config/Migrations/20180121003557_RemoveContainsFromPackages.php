<?php
use Migrations\AbstractMigration;

class RemoveContainsFromPackages extends AbstractMigration
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
        $table->removeColumn('contains_model');
        $table->removeColumn('contains_view');
        $table->removeColumn('contains_controller');
        $table->removeColumn('contains_behavior');
        $table->removeColumn('contains_helper');
        $table->removeColumn('contains_component');
        $table->removeColumn('contains_shell');
        $table->removeColumn('contains_theme');
        $table->removeColumn('contains_datasource');
        $table->removeColumn('contains_vendor');
        $table->removeColumn('contains_test');
        $table->removeColumn('contains_lib');
        $table->removeColumn('contains_resource');
        $table->removeColumn('contains_config');
        $table->removeColumn('contains_app');
        $table->update();
    }
}
