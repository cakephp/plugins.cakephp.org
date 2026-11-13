<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\NumberHelper;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Compact counters retain native locale formatting and omit unnecessary decimals.
 */
class NumberHelperTest extends TestCase
{
    /**
     * Keep small counts exact and round larger counts to one decimal.
     */
    #[DataProvider('compactCounts')]
    public function testCompact(int $value, string $expected): void
    {
        $this->assertSame($expected, new NumberHelper(new View())->compact($value));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function compactCounts(): array
    {
        return [
            'zero' => [0, '0'],
            'small count' => [42, '42'],
            'before thousands' => [999, '999'],
            'thousands' => [1_000, '1k'],
            'rounded thousands' => [22_701, '22.7k'],
            'millions' => [1_000_000, '1M'],
            'rounded millions' => [8_789_626, '8.8M'],
            'billions' => [1_000_000_000, '1B'],
        ];
    }

    /**
     * Decimal separators follow the current CakePHP locale.
     */
    public function testCompactUsesCurrentLocale(): void
    {
        $locale = I18n::getLocale();
        try {
            I18n::setLocale('de_DE');
            $this->assertSame('22,7k', new NumberHelper(new View())->compact(22_701));
        } finally {
            I18n::setLocale($locale);
        }
    }
}
