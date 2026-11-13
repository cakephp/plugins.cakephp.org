<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFeaturedPlugins extends BaseMigration
{
    /**
     * Snapshot the previous configuration once, independent of catalogue availability.
     */
    public function up(): void
    {
        $this->table('featured_plugins')
            ->addColumn('package', 'string', ['limit' => 191])
            ->addColumn('enabled', 'boolean', ['default' => true])
            ->addColumn('logo', 'string', ['limit' => 2048, 'null' => true])
            ->addColumn('title', 'string', ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addTimestamps('created', 'modified')
            ->addIndex(['package'], ['unique' => true])
            ->create();
        $featured = [
            // Optional logo URL or image path; null uses a name-based emblem.
            'cakedc/users' => ['logo' => null],
            'dereuromark/cakephp-ide-helper' => ['logo' => 'https://dereuromark.github.io/cakephp-ide-helper/logo.svg'],
            'dereuromark/cakephp-queue' => ['logo' => 'https://dereuromark.github.io/cakephp-queue/logo.svg'],
            'dereuromark/cakephp-tools' => ['logo' => 'https://dereuromark.github.io/cakephp-tools/logo.svg'],
            'friendsofcake/cakepdf' => ['logo' => null],
            'friendsofcake/crud' => ['logo' => null],
            'friendsofcake/search' => ['logo' => null],
            'josbeir/cakephp-interact' => ['logo' => 'https://www.cakeinteract.dev/docs/branding/interact-mark.svg'],
            'josbeir/cakephp-vite' => ['logo' => null],
            'josegonzalez/cakephp-upload' => ['logo' => null],
            'lordsimal/cakephp-sentry' => ['logo' => null],
            'markstory/asset_compress' => ['logo' => null],
        ];

        $rows = [];
        foreach ($featured as $package => $metadata) {
            $rows[] = ['package' => $package, 'enabled' => true, 'logo' => $metadata['logo'], 'created' => date('Y-m-d H:i:s')];
        }
        $this->table('featured_plugins')->insert($rows)->saveData();
    }

    /**
     * Remove only the editorial configuration on rollback.
     */
    public function down(): void
    {
        $this->table('featured_plugins')->drop()->save();
    }
}
