<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

class KeywordsFixture extends TestFixture
{
    /**
     * @var array<array<string, mixed>>
     */
    #[Override]
    public array $records = [
        ['id' => 1, 'name' => 'sass'],
        ['id' => 2, 'name' => 'less'],
        ['id' => 3, 'name' => 'cakephp'],
        ['id' => 4, 'name' => 'oauth2'],
        ['id' => 5, 'name' => 'static analysis'],
        ['id' => 6, 'name' => 'my_tool'],
    ];
}
