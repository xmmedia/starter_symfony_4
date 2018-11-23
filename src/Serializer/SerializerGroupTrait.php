<?php

namespace App\Serializer;

trait SerializerGroupTrait
{
    /** @var array */
    private $context;

    private function hasGroup(array $groups): bool
    {
        return !empty(array_intersect($groups, $this->context['groups']));
    }
}
