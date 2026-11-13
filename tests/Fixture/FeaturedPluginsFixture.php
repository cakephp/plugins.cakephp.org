<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FeaturedPluginsFixture extends TestFixture
{
    /**
     * Keep the public test catalogue's previous featured selection.
     */
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'package' => 'markstory/asset_compress', 'enabled' => true, 'logo' => null, 'created' => '2026-01-01 00:00:00'],
            ['id' => 2, 'package' => 'cakedc/users', 'enabled' => true, 'logo' => null, 'created' => '2026-01-01 00:00:00'],
            ['id' => 3, 'package' => 'dereuromark/cakephp-tools', 'enabled' => true, 'logo' => null, 'created' => '2026-01-01 00:00:00'],
        ];
        parent::init();
    }
}
