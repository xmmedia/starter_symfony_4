<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\User\Name;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NameTransformer implements DataTransformerInterface
{
    /**
     * From Name to form value.
     *
     * @param Name|null $name
     */
    public function transform($name): ?string
    {
        if (null === $name) {
            return null;
        }

        return $name->toString();
    }

    /**
     * From user input to Name.
     *
     * @param string|null $name
     */
    public function reverseTransform($name): ?Name
    {
        if (null === $name) {
            return null;
        }

        try {
            return Name::fromString($name);
        } catch (\InvalidArgumentException $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
