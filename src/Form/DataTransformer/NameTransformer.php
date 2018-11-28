<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\User\Name;
use Symfony\Component\Form\DataTransformerInterface;

class NameTransformer implements DataTransformerInterface
{
    /**
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
     * @param string|null $name
     */
    public function reverseTransform($name): ?Name
    {
        if (null === $name) {
            return null;
        }

        return Name::fromString($name);
    }
}
