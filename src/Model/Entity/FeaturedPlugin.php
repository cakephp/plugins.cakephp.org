<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Override;

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
    #[Override]
    protected array $_accessible = [
        'package' => true,
        'enabled' => true,
        'logo' => true,
        'title' => true,
        'description' => true,
    ];

    /**
     * Persist an omitted logo as null, consistently for every ORM caller.
     */
    protected function _setLogo(?string $value): ?string
    {
        return $this->normalizeOverride($value);
    }

    /**
     * Blank editorial titles fall back to the catalogue name.
     */
    protected function _setTitle(?string $value): ?string
    {
        return $this->normalizeOverride($value);
    }

    /**
     * Blank editorial descriptions fall back to imported metadata.
     */
    protected function _setDescription(?string $value): ?string
    {
        return $this->normalizeOverride($value);
    }

    /**
     * Preserve nonempty values, including the string "0".
     */
    private function normalizeOverride(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : '';

        return $value === '' ? null : $value;
    }
}
