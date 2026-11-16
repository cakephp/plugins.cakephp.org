<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Shared identities for imported keywords, links and exact filters.
 */
final class ComposerKeyword
{
    public const int MAX_LENGTH = 191;

    private const array GENERIC = ['cake', 'cakephp', 'cakephp-plugin', 'php', 'plugin', 'plugins'];

    /**
     * Preserve punctuation while ignoring case and redundant whitespace.
     */
    public static function normalize(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? ''));
    }

    /**
     * Accept Composer keyword characters without truncating distinct terms.
     */
    public static function isValid(string $name): bool
    {
        return $name !== '' && mb_strlen($name) <= self::MAX_LENGTH
            && preg_match('/^[\p{L}\p{N} ._-]+$/uD', $name) === 1;
    }

    /**
     * Generic terms remain searchable but add little to the details view.
     */
    public static function isVisible(string $name): bool
    {
        return !in_array($name, self::GENERIC, true);
    }
}
