<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\Address;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AddressTransformer implements DataTransformerInterface
{
    /**
     * From Address to form value.
     *
     * @param Address|null $address
     */
    public function transform($address): ?array
    {
        if (null === $address) {
            return null;
        }

        return $address->toArray();
    }

    /**
     * From user input to Address.
     *
     * @param array|null $address
     */
    public function reverseTransform($address): ?Address
    {
        if (null === $address) {
            return null;
        }

        try {
            return Address::fromArray($address);
        } catch (\Throwable $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
