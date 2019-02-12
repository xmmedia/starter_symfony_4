<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Exception\InvalidProvince;
use App\Model\Province;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProvinceTransformer implements DataTransformerInterface
{
    /**
     * From Province to form value.
     *
     * @param Province|null $province
     */
    public function transform($province): ?string
    {
        if (null === $province) {
            return null;
        }

        return $province->toString();
    }

    /**
     * From user input to Province.
     *
     * @param string|null $province
     */
    public function reverseTransform($province): ?Province
    {
        if (null === $province) {
            return null;
        }

        try {
            return Province::fromString($province);
        } catch (InvalidProvince $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
