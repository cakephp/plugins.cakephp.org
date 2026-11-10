<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * Imported categories shared by query and interaction tests.
 */
class CategoriesFixture extends TestFixture
{
    /**
     * @var array<array<string, mixed>>
     */
    #[Override]
    public array $records = [
        ['id' => 1, 'name' => 'Asset Management', 'slug' => 'asset-management'],
        ['id' => 2, 'name' => 'Authentication & Authorization', 'slug' => 'authentication-authorization'],
        ['id' => 3, 'name' => 'Code Analysis', 'slug' => 'code-analysis'],
    ];
}
