<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Configure;

/**
 * Upcoming framework versions are explicit and never replace the current stable major.
 */
final class CakephpVersion
{
    /**
     * Stable framework targets, keyed by major version.
     *
     * @return array<int, list<int>>
     */
    public static function stableVersions(): array
    {
        $stable = [];
        foreach (self::versions() as $major => $minors) {
            if ($minors !== null) {
                $stable[$major] = $minors;
            }
        }

        return $stable;
    }

    /**
     * @return list<int>
     */
    public static function developmentMajors(): array
    {
        return array_keys(array_filter(self::versions(), static fn(?array $minors): bool => $minors === null));
    }

    /**
     * Only configured targets are offered as catalogue filters.
     *
     * @return list<string>
     */
    public static function slugs(): array
    {
        $slugs = [];
        foreach (self::versions() as $major => $minors) {
            if ($minors === null) {
                $slugs[] = self::developmentSlug($major);
                continue;
            }
            foreach ($minors as $minor) {
                $slugs[] = 'cakephp-' . $major . '-' . $minor;
            }
        }

        return $slugs;
    }

    /**
     * @return array<int, list<int>|null>
     */
    private static function versions(): array
    {
        /** @var array<int, list<int>|null> $versions */
        $versions = Configure::readOrFail('Packages.cakephpVersions');

        return $versions;
    }

    /**
     * Compact label shared by filters, badges and publisher choices.
     */
    public static function developmentLabel(int $major): string
    {
        return $major . '.x@dev';
    }

    /**
     * Stable tag key for an upcoming framework major.
     */
    public static function developmentSlug(int $major): string
    {
        return 'cakephp-' . $major . '-x-dev';
    }

    /**
     * Recognize only explicitly configured upcoming targets.
     */
    public static function isDevelopmentSlug(string $slug): bool
    {
        return array_any(self::developmentMajors(), static fn(int $major): bool => $slug === self::developmentSlug($major));
    }

    /**
     * Identify development publisher selections independently of stable majors.
     */
    public static function isDevelopmentLabel(string $label): bool
    {
        return array_any(self::developmentMajors(), static fn(int $major): bool => $label === self::developmentLabel($major));
    }
}
