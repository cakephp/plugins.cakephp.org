<?php
declare(strict_types=1);

use App\Application;
use App\Test\Fixture\PackagesFixture;
use Cake\Cache\Cache;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Migrations\TestSuite\Migrator;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = new Application(dirname(__DIR__, 2) . '/config');
$app->bootstrap();
$connection = ConnectionManager::get('default');
if (
    !$connection instanceof Connection ||
    !$connection->getDriver() instanceof Sqlite ||
    !str_ends_with($connection->config()['database'], '/browser.sqlite')
) {
    throw new RuntimeException('Browser fixtures require their dedicated SQLite database.');
}
// Only catalogue and tag tables are needed; OAuth itself is not exercised here.
new Migrator()->runMany([['connection' => 'default'], ['connection' => 'default', 'plugin' => 'Tags']]);
$fixture = new PackagesFixture();
foreach ($fixture->records as $record) {
    $connection->insert('packages', $record);
}
foreach ([1 => ['cakephp-5-4', 'CakePHP: 5.4'], 2 => ['php-8-5', 'PHP: 8.5'], 3 => ['cakephp-4-4', 'CakePHP: 4.4']] as $id => [$slug, $label]) {
    $connection->insert('tags_tags', ['id' => $id, 'slug' => $slug, 'label' => $label, 'created' => '2026-01-01', 'modified' => '2026-01-01']);
}
foreach ($fixture->records as $record) {
    foreach ([$record['id'] === 25 ? 3 : 1, 2] as $tagId) {
        $connection->insert('tags_tagged', ['tag_id' => $tagId, 'fk_id' => $record['id'], 'fk_model' => 'Packages', 'created' => '2026-01-01', 'modified' => '2026-01-01']);
    }
}
Cache::clearAll();
