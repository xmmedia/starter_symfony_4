<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\User\UserId;
use Symfony\Component\Form\DataTransformerInterface;

class UserIdTransformer implements DataTransformerInterface
{
    /**
     * From UserId to form value.
     *
     * @param UserId|null $uuid
     */
    public function transform($uuid): ?string
    {
        if (null === $uuid) {
            return null;
        }

        return $uuid->toString();
    }

    /**
     * From user input to UserId.
     *
     * @param string|null $uuid
     */
    public function reverseTransform($uuid): ?UserId
    {
        if (null === $uuid) {
            return null;
        }

        return UserId::fromString($uuid);
    }
}
