<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Public avatar URLs are shared by profile services and view helpers without network access.
 */
class GithubAvatar
{
    /**
     * Build a public image URL only for a valid GitHub account name.
     */
    public static function url(string $owner, int $size = 192): ?string
    {
        if (!preg_match('/^[a-z0-9][a-z0-9-]*$/iD', $owner)) {
            return null;
        }

        return 'https://github.com/' . rawurlencode(strtolower($owner)) . '.png?size=' . $size;
    }
}
