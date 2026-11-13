<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

enum UserStatus: string implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Active = 'active';
    case Suspended = 'suspended';

    /**
     * Derive the display status from the persisted access flag.
     */
    public static function fromActive(bool $active): self
    {
        return $active ? self::Active : self::Suspended;
    }
}
