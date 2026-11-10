<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 */
class Category extends Entity
{
    /**
     * @var array<string, bool>
     */
    #[Override]
    protected array $_accessible = ['name' => true, 'slug' => true];
}
