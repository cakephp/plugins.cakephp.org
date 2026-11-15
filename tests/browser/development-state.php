<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Cache\Engine\FileEngine;

// Development discovery fixtures never touch the application's database or Redis.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__, 2) . '/vendor/autoload.php';
Cache::setConfig('browser_catalogue', [
    'className' => FileEngine::class,
    'path' => dirname(__DIR__, 2) . '/tmp/browser-catalogue/',
    'prefix' => 'plugins_catalogue_',
]);
Cache::clear('browser_catalogue');
$connection = new PDO('sqlite:' . dirname(__DIR__, 2) . '/tmp/browser.sqlite');
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->exec('DELETE FROM tags_tagged WHERE tag_id = 9001 OR fk_id = 9001');
$connection->exec('DELETE FROM tags_tags WHERE id = 9001');
$connection->exec('DELETE FROM packages WHERE id = 9001');
$connection->exec('UPDATE packages SET development_compatibility = NULL WHERE id = 1');
if (($argv[1] ?? '') !== 'seed') {
    exit;
}
$release = json_encode([6 => [
    'version' => 'dev-next', 'stability' => 'dev',
    'cakephp_constraint' => '6.x-dev', 'php_constraint' => '^8.5',
]], JSON_THROW_ON_ERROR);
$connection->prepare('UPDATE packages SET development_compatibility = ? WHERE id = 1')->execute([$release]);
$connection->prepare('INSERT INTO packages (id, package, namespace, description, repo_url, downloads, monthly_downloads, stars, latest_stable_version, latest_stable_release_date, development_compatibility) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
    ->execute([9001, 'future/preview', 'future', 'A plugin available on a development branch.', 'https://github.com/future/preview', 0, 0, 0, null, null, $release]);
$connection->exec("INSERT INTO tags_tags (id, slug, label, created, modified) VALUES (9001, 'cakephp-6-x-dev', 'CakePHP: 6.x@dev', '2026-01-01', '2026-01-01')");
foreach ([1, 9001] as $id) {
    $connection->prepare('INSERT INTO tags_tagged (tag_id, fk_id, fk_model, created, modified) VALUES (9001, ?, ?, ?, ?)')
        ->execute([$id, 'Packages', '2026-01-01', '2026-01-01']);
}
