<?php
declare(strict_types=1);

use App\Test\Fixture\FeaturedPluginsFixture;
use App\Test\Fixture\UsersFixture;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;

// Test setup is CLI-only and always targets the isolated browser fixture DB.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$database = dirname(__DIR__, 2) . '/tmp/browser.sqlite';
ConnectionManager::setConfig('test', ['className' => Connection::class, 'driver' => Sqlite::class, 'database' => $database]);
$connection = new PDO('sqlite:' . $database);
$mode = $argv[1] ?? 'reset';
if ($mode === 'reset') {
    foreach (['users' => new UsersFixture(), 'featured_plugins' => new FeaturedPluginsFixture()] as $table => $fixture) {
        $connection->exec('DELETE FROM ' . $table);
        foreach ($fixture->records as $record) {
            $columns = implode(', ', array_keys($record));
            $parameters = implode(', ', array_fill(0, count($record), '?'));
            $connection->prepare("INSERT INTO $table ($columns) VALUES ($parameters)")->execute(array_values($record));
        }
    }
    exit;
}
if ($mode === 'revoke') {
    $connection->exec("UPDATE users SET role = 'user' WHERE id = 1");
    exit;
}
if ($mode === 'suspend') {
    $connection->exec('UPDATE users SET is_active = 0 WHERE id = 1');
    exit;
}
if (!in_array($mode, ['1', '2', '3'], true)) {
    throw new RuntimeException('Unknown browser fixture identity.');
}
session_save_path(dirname(__DIR__, 2) . '/tmp/browser-sessions');
session_id(bin2hex(random_bytes(16)));
session_start();
$_SESSION = ['AuthId' => (int)$mode];
$cookie = ['name' => session_name(), 'value' => session_id()];
session_write_close();
echo json_encode($cookie, JSON_THROW_ON_ERROR);
