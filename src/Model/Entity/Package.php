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
 * @property \Cake\I18n\Date|null $latest_stable_release_date
 *
 * @property \Tags\Model\Entity\Tag[] $tags
 *
 * @property \Tags\Model\Entity\Tag[] $cake_php_tags
 * @property array<int, array<\Tags\Model\Entity\Tag>> $cake_php_tag_groups
 * @property \Tags\Model\Entity\Tag[] $php_tags
 * @property array<int, array<\Tags\Model\Entity\Tag>> $php_tag_groups
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
        return $this->extractVersionTags('CakePHP');
    }

    /**
     * @return array<int, array<\Tags\Model\Entity\Tag>>
     */
    protected function _getCakePhpTagGroups(): array
    {
        return $this->groupVersionTags($this->cake_php_tags, 'CakePHP');
    }

    /**
     * @return array<\Tags\Model\Entity\Tag>
     */
    protected function _getPhpTags(): array
    {
        return $this->extractVersionTags('PHP');
    }

    /**
     * @return array<int, array<\Tags\Model\Entity\Tag>>
     */
    protected function _getPhpTagGroups(): array
    {
        return $this->groupVersionTags($this->php_tags, 'PHP');
    }

    /**
     * @param string $prefix
     * @return array<\Tags\Model\Entity\Tag>
     */
    protected function extractVersionTags(string $prefix): array
    {
        return array_filter($this->tags, static function ($tag) use ($prefix) {
            return str_starts_with($tag->label, $prefix . ':');
        });
    }

    /**
     * @param array<\Tags\Model\Entity\Tag> $tags
     * @param string $prefix
     * @return array<int, array<\Tags\Model\Entity\Tag>>
     */
    protected function groupVersionTags(array $tags, string $prefix): array
    {
        $groups = [];
        $quotedPrefix = preg_quote($prefix, '/');

        foreach ($tags as $tag) {
            if (!preg_match('/^' . $quotedPrefix . ':\s*(\d+)(?:\.\d+)?$/', $tag->label, $matches)) {
                continue;
            }

            $majorVersion = $matches[1];
            $groups[$majorVersion][] = $tag;
        }

        uksort($groups, static function (string $left, string $right): int {
            return version_compare($right, $left);
        });

        foreach ($groups as &$groupedTags) {
            usort($groupedTags, static function ($left, $right) use ($prefix): int {
                $leftVersion = preg_replace('/^' . preg_quote($prefix, '/') . ':\s*/', '', $left->label) ?: $left->label;
                $rightVersion = preg_replace('/^' . preg_quote($prefix, '/') . ':\s*/', '', $right->label) ?: $right->label;

                return version_compare($rightVersion, $leftVersion);
            });
        }
        unset($groupedTags);

        return $groups;
    }
}
