<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\Gender;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GenderTransformer implements DataTransformerInterface
{
    /**
     * From Gender to form value.
     *
     * @param Gender|null $type
     */
    public function transform($type): ?string
    {
        if (null === $type) {
            return null;
        }

        return $type->getValue();
    }

    /**
     * From user input to Gender.
     *
     * @param string|null $type
     */
    public function reverseTransform($type): ?Gender
    {
        if (null === $type) {
            return null;
        }

        try {
            return Gender::byValue($type);
        } catch (\Throwable $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
