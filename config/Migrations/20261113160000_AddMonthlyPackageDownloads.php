<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMonthlyPackageDownloads extends BaseMigration
{
    /**
     * Unknown counts remain distinct from packages with zero monthly downloads.
     */
    public function change(): void
    {
        $this->table('packages')
            ->addColumn('monthly_downloads', 'integer', [
                'null' => true,
                'default' => null,
            ])
            ->update();
    }
}
