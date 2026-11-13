<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\NumberHelper as CakeNumberHelper;

/**
 * Adds compact counters to CakePHP's locale-aware number formatting.
 */
class NumberHelper extends CakeNumberHelper
{
    /**
     * Format a count with at most one decimal and a k, M, or B suffix.
     */
    public function compact(int $value): string
    {
        $divisor = match (true) {
            $value >= 1_000_000_000 => 1_000_000_000,
            $value >= 1_000_000 => 1_000_000,
            $value >= 1_000 => 1_000,
            default => 1,
        };
        $suffix = match ($divisor) {
            1_000_000_000 => 'B',
            1_000_000 => 'M',
            1_000 => 'k',
            default => '',
        };

        return $this->format($value / $divisor, [
            'precision' => $divisor === 1 ? 0 : 1,
            'after' => $suffix,
        ]);
    }
}
