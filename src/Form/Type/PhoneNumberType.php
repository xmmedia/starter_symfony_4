<?php

declare(strict_types=1);

namespace App\Form\Type;

use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => 'Phone Number',
            'default_region'  => 'CA',
            'format'          => PhoneNumberFormat::NATIONAL,
            'constraints'     => [
                new PhoneNumber(),
            ],
        ]);
    }

    public function getParent()
    {
        return \Misd\PhoneNumberBundle\Form\Type\PhoneNumberType::class;
    }
}
