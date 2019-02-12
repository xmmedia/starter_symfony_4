<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\PhoneNumber;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberTransformer implements DataTransformerInterface
{
    /**
     * From PhoneNumber to form value.
     *
     * @param PhoneNumber|null $phoneNumber
     */
    public function transform($phoneNumber): ?array
    {
        if (null === $phoneNumber) {
            return null;
        }

        return $phoneNumber->toArray();
    }

    /**
     * From transformed user input to PhoneNumber.
     * Transformed user input is libphonenumber object.
     *
     * @param \libphonenumber\PhoneNumber|null $phoneNumber
     */
    public function reverseTransform($phoneNumber): ?PhoneNumber
    {
        if (null === $phoneNumber) {
            return null;
        }

        try {
            return PhoneNumber::fromObject($phoneNumber);
        } catch (\Throwable $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
