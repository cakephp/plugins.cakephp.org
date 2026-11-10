<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * One package belongs to two categories; most packages remain unlisted.
 */
class CategoriesPackagesFixture extends TestFixture
{
    /**
     * @var array<array<string, mixed>>
     */
    #[Override]
    public array $records = [
        ['category_id' => 1, 'package_id' => 1],
        ['category_id' => 3, 'package_id' => 1],
        ['category_id' => 2, 'package_id' => 25],
    ];
}
