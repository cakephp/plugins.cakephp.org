<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddDevelopmentCompatibility extends BaseMigration
{
    /**
     * Indexed tags filter development support; JSON retains its source release metadata.
     */
    public function change(): void
    {
        $this->table('packages')
            ->addColumn('development_compatibility', 'json', ['null' => true, 'default' => null])
            ->update();
    }
}
