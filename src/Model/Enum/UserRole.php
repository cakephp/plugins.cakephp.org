<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

enum UserRole: string implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Admin = 'admin';
    case User = 'user';
}
