<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Exception\InvalidCountry;
use App\Model\Country;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CountryTransformer implements DataTransformerInterface
{
    /**
     * From Country to form value.
     *
     * @param Country|null $country
     */
    public function transform($country): ?string
    {
        if (null === $country) {
            return null;
        }

        return $country->toString();
    }

    /**
     * From user input to Country.
     *
     * @param string|null $country
     */
    public function reverseTransform($country): ?Country
    {
        if (null === $country) {
            return null;
        }

        try {
            return Country::fromString($country);
        } catch (InvalidCountry $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
