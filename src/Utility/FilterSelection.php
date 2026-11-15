<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Keep generated links and island actions on the same three-state cycle.
 */
final class FilterSelection
{
    /**
     * @param list<string> $included Included options.
     * @param list<string> $excluded Excluded options.
     * @return array{included: list<string>, excluded: list<string>}
     */
    public static function cycle(array $included, array $excluded, string $slug, bool $multiple = true): array
    {
        if (in_array($slug, $excluded, true)) {
            $excluded = array_values(array_diff($excluded, [$slug]));
            $included = array_values(array_diff($included, [$slug]));
        } elseif (in_array($slug, $included, true)) {
            $included = array_values(array_diff($included, [$slug]));
            $excluded[] = $slug;
        } else {
            $included = $multiple ? [...$included, $slug] : [$slug];
        }

        return ['included' => $included, 'excluded' => $excluded];
    }
}
