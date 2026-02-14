<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Package Entity
 *
 * @property int $id
 * @property string $package
 * @property string $description
 * @property string $repo_url
 * @property int $downloads
 * @property int $stars
 * @property string|null $latest_stable_version
 *
 * @property \Tags\Model\Entity\Tag[] $tags
 *
 * @property \Tags\Model\Entity\Tag[] $cake_php_tags
 * @property \Tags\Model\Entity\Tag[] $php_tags
 */
class Package extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'id' => false,
        '*' => true,
    ];

    /**
     * @return array<\Tags\Model\Entity\Tag>
     */
    protected function _getCakePhpTags(): array
    {
        return array_filter($this->tags, function ($tag) {
            return str_starts_with($tag->label, 'CakePHP');
        });
    }

    /**
     * @return array<\Tags\Model\Entity\Tag>
     */
    protected function _getPhpTags(): array
    {
        return array_filter($this->tags, function ($tag) {
            return str_starts_with($tag->label, 'PHP');
        });
    }
}
