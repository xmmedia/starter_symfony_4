<?php

declare(strict_types=1);

namespace App\Model;

/**
 * @method static Gender MALE();
 * @method static Gender FEMALE();
 */
class Gender extends Enum
{
    public const MALE = 'm';
    public const FEMALE = 'f';
}
