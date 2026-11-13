<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

enum FeaturedPluginStatus: string implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Enabled = 'enabled';
    case Disabled = 'disabled';
    case Unavailable = 'unavailable';

    /**
     * Availability remains independent of the persisted enabled flag.
     */
    public static function fromEnabled(bool $enabled): self
    {
        return $enabled ? self::Enabled : self::Disabled;
    }
}
