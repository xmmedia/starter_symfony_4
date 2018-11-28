<?php

declare(strict_types=1);

namespace App\Serializer;

trait SerializerGroupAwareTrait
{
    /** @var array */
    private $context;

    private function hasGroup(array $groups): bool
    {
        return !empty(array_intersect($groups, $this->context['groups']));
    }
}
