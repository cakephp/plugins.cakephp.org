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
 * @property array<int, array<\Tags\Model\Entity\Tag>> $cake_php_tag_groups
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
     * @return array<int, array<\Tags\Model\Entity\Tag>>
     */
    protected function _getCakePhpTagGroups(): array
    {
        $groups = [];

        foreach ($this->cake_php_tags as $tag) {
            if (!preg_match('/^CakePHP:\s*(\d+)(?:\.\d+)?$/', $tag->label, $matches)) {
                continue;
            }

            $majorVersion = $matches[1];
            $groups[$majorVersion][] = $tag;
        }

        uksort($groups, static function (string $left, string $right): int {
            return version_compare($right, $left);
        });

        foreach ($groups as &$tags) {
            usort($tags, static function ($left, $right): int {
                $leftVersion = str_replace('CakePHP: ', '', $left->label);
                $rightVersion = str_replace('CakePHP: ', '', $right->label);

                return version_compare($rightVersion, $leftVersion);
            });
        }
        unset($tags);

        return $groups;
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
