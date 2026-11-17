<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array<\App\Model\Entity\Package> $packages
 * @property \Cake\ORM\Entity $_joinData
 */
class Category extends Entity
{
    /**
     * @var array<string, bool>
     */
    #[Override]
    protected array $patchable = ['name' => true, 'slug' => true];
}
