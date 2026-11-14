<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Cache\Engine\FileEngine;

// Extra publishers exist only for the directory pagination browser test.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__, 2) . '/vendor/autoload.php';
// Match Playwright's isolated cache, never the development application's Redis.
Cache::setConfig('browser_catalogue', [
    'className' => FileEngine::class,
    'path' => dirname(__DIR__, 2) . '/tmp/browser-catalogue/',
    'prefix' => 'plugins_catalogue_',
]);
Cache::clear('browser_catalogue');
$database = dirname(__DIR__, 2) . '/tmp/browser.sqlite';
$connection = new PDO('sqlite:' . $database);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->exec("DELETE FROM tags_tagged WHERE fk_model = 'Packages' AND fk_id BETWEEN 1001 AND 1021");
$connection->exec('DELETE FROM packages WHERE id BETWEEN 1001 AND 1021');
if (($argv[1] ?? '') !== 'seed') {
    exit;
}
$record = $connection->query('SELECT * FROM packages WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    throw new RuntimeException('Browser package fixture is missing.');
}
$columns = implode(', ', array_keys($record));
$parameters = implode(', ', array_fill(0, count($record), '?'));
$insert = $connection->prepare("INSERT INTO packages ($columns) VALUES ($parameters)");
for ($i = 1; $i <= 21; $i++) {
    $record['id'] = 1000 + $i;
    $record['namespace'] = sprintf('publisher-%02d', $i);
    $record['package'] = $record['namespace'] . '/plugin';
    $record['repo_url'] = 'https://gitlab.com/example/plugin';
    $record['downloads'] = 10;
    $record['monthly_downloads'] = 10;
    $record['stars'] = 0;
    $insert->execute(array_values($record));
    $connection->prepare("INSERT INTO tags_tagged (tag_id, fk_id, fk_model, created, modified) VALUES (1, ?, 'Packages', '2026-01-01', '2026-01-01')")->execute([$record['id']]);
}
