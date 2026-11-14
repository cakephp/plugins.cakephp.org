<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCatalogueLookupIndexes extends BaseMigration
{
    /**
     * Support page-scoped tag loading and the newer catalogue sort options.
     */
    public function change(): void
    {
        $this->table('tags_tagged')
            ->addIndex(['fk_id', 'fk_model', 'tag_id'], [
                'name' => 'tagged_package_lookup',
            ])
            ->update();

        $this->table('packages')
            ->addIndex(['latest_stable_release_date', 'id'], [
                'name' => 'packages_release_sort',
            ])
            ->addIndex(['monthly_downloads', 'id'], [
                'name' => 'packages_monthly_downloads_sort',
            ])
            ->update();
    }
}
