<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

class KeywordsPackagesFixture extends TestFixture
{
    /**
     * @var array<array<string, mixed>>
     */
    #[Override]
    public array $records = [
        ['keyword_id' => 1, 'package_id' => 1],
        ['keyword_id' => 2, 'package_id' => 1],
        ['keyword_id' => 3, 'package_id' => 1],
        ['keyword_id' => 4, 'package_id' => 25],
        ['keyword_id' => 5, 'package_id' => 2],
        ['keyword_id' => 6, 'package_id' => 2],
    ];
}
