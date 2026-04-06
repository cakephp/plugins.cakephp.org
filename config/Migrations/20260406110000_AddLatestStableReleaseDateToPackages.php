<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddLatestStableReleaseDateToPackages extends BaseMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        $this->table('packages')
            ->addColumn('latest_stable_release_date', 'date', [
                'default' => null,
                'null' => true,
            ])
            ->update();
    }
}
