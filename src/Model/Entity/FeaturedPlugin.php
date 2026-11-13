<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $package
 * @property bool $enabled
 * @property string|null $logo
 * @property string|null $title
 * @property string|null $description
 * @property \App\Model\Entity\Package|null $catalogue_package
 */
class FeaturedPlugin extends Entity
{
    protected array $_accessible = [
        'package' => true,
        'enabled' => true,
        'logo' => true,
        'title' => true,
        'description' => true,
    ];
}
