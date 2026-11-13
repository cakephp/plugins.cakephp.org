<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Override;

/**
 * CakeDC targets the older major while the other fixture publishers support the current one.
 */
class PublisherTaggedFixture extends TestFixture
{
    #[Override]
    public string $table = 'tags_tagged';

    /**
     * Model-scoped membership also guards against unrelated tag associations.
     */
    public function init(): void
    {
        $this->records = [];
        for ($id = 1; $id <= 26; $id++) {
            $this->records[] = [
                'tag_id' => $id === 25 ? 3 : 1,
                'fk_id' => $id,
                'fk_model' => 'Packages',
                'created' => '2026-01-01 00:00:00',
                'modified' => '2026-01-01 00:00:00',
            ];
        }
        $this->records[] = [
            'tag_id' => 2,
            'fk_id' => 1,
            'fk_model' => 'Packages',
            'created' => '2026-01-01 00:00:00',
            'modified' => '2026-01-01 00:00:00',
        ];
        parent::init();
    }
}
