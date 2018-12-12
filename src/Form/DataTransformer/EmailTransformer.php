<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\Email;
use Symfony\Component\Form\DataTransformerInterface;

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

        return Email::fromString($email);
    }
}
