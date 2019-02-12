<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\Email;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EmailTransformer implements DataTransformerInterface
{
    /**
     * From Email to form value.
     *
     * @param Email|null $email
     */
    public function transform($email): ?string
    {
        if (null === $email) {
            return null;
        }

        return $email->toString();
    }

    /**
     * From user input to Email.
     *
     * @param string|null $email
     */
    public function reverseTransform($email): ?Email
    {
        if (null === $email) {
            return null;
        }

        try {
            return Email::fromString($email);
        } catch (\Throwable $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
