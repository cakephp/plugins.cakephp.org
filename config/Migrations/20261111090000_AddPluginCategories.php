<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPluginCategories extends BaseMigration
{
    /**
     * Store imported curation separately from compatibility tags.
     */
    public function change(): void
    {
        $this->table('categories')
            ->addColumn('name', 'string')
            ->addColumn('slug', 'string', ['limit' => 191])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $this->table('categories_packages', ['id' => false, 'primary_key' => ['category_id', 'package_id']])
            ->addColumn('category_id', 'integer')
            ->addColumn('package_id', 'integer')
            ->addIndex(['package_id'])
            ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('package_id', 'packages', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
