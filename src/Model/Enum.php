<?php

declare(strict_types=1);

namespace App\Model;

use MabeEnum\Enum as MabeEnum;
use MabeEnum\EnumSerializableTrait;
use Serializable;

abstract class Enum extends MabeEnum implements Serializable, ValueObject
{
    use EnumSerializableTrait;

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->is($object);
    }
}
