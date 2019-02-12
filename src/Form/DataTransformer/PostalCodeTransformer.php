<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Exception\InvalidPostalCode;
use App\Model\PostalCode;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PostalCodeTransformer implements DataTransformerInterface
{
    /**
     * From PostalCode to form value.
     *
     * @param PostalCode|null $email
     */
    public function transform($email): ?string
    {
        if (null === $email) {
            return null;
        }

        return $email->toString();
    }

    /**
     * From user input to PostalCode.
     *
     * @param string|null $email
     */
    public function reverseTransform($email): ?PostalCode
    {
        if (null === $email) {
            return null;
        }

        try {
            return PostalCode::fromString($email);
        } catch (InvalidPostalCode $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
