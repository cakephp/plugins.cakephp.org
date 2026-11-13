<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * Multiple minor tags in a major must not duplicate publisher totals.
 */
class PublisherVersionsFixture extends TestFixture
{
    #[Override]
    public string $table = 'tags_tags';

    /**
     * Seed the same compatibility vocabulary used by the browser catalogue.
     */
    public function init(): void
    {
        $this->records = [];
        foreach ([1 => ['cakephp-5-4', 'CakePHP: 5.4'], 2 => ['cakephp-5-3', 'CakePHP: 5.3'], 3 => ['cakephp-4-4', 'CakePHP: 4.4']] as $id => [$slug, $label]) {
            $this->records[] = [
                'id' => $id,
                'slug' => $slug,
                'label' => $label,
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ];
        }
        parent::init();
    }
}
