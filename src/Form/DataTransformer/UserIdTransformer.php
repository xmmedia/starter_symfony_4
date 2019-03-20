<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\User\UserId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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

        try {
            return UserId::fromString($uuid);
        } catch (InvalidUuidStringException $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
