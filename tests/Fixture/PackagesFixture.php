<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PackagesFixture
 */
class PackagesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'package' => 'markstory/asset_compress',
                'description' => 'Featured package used to verify the homepage slider.',
                'repo_url' => 'https://github.com/markstory/asset_compress',
                'downloads' => 5000,
                'stars' => 450,
                'latest_stable_version' => '5.0.0',
                'latest_stable_release_date' => '2025-01-15',
            ],
        ];

        for ($i = 2; $i <= 24; $i++) {
            $packageNumber = str_pad((string)($i - 1), 2, '0', STR_PAD_LEFT);
            $this->records[] = [
                'id' => $i,
                'package' => sprintf('vendor/package-%s', $packageNumber),
                'description' => sprintf('Test package %s for controller pagination and search coverage.', $packageNumber),
                'repo_url' => sprintf('https://github.com/vendor/package-%s', $packageNumber),
                'downloads' => 1000 - $i,
                'stars' => 100 - $i,
                'latest_stable_version' => sprintf('1.%d.0', $i - 2),
                'latest_stable_release_date' => sprintf('2025-02-%02d', $i),
            ];
        }

        parent::init();
    }
}
