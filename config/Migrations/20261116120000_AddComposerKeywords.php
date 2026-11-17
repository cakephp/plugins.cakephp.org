<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddComposerKeywords extends BaseMigration
{
    /**
     * Author-provided keywords are independent of curation and compatibility.
     */
    public function change(): void
    {
        $this->table('keywords')
            ->addColumn('name', 'string', ['limit' => 191])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('keywords_packages', ['id' => false, 'primary_key' => ['keyword_id', 'package_id']])
            ->addColumn('keyword_id', 'integer')
            ->addColumn('package_id', 'integer')
            ->addIndex(['package_id'])
            ->addForeignKey('keyword_id', 'keywords', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('package_id', 'packages', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
