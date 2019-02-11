<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\PhoneNumberTransformer;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new PhoneNumberTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'          => 'Phone Number',
            'default_region' => 'CA',
            'format'         => PhoneNumberFormat::NATIONAL,
            'constraints'    => [
                new AssertPhoneNumber([
                    'defaultRegion' => 'CA',
                ]),
            ],
        ]);
    }

    public function getParent()
    {
        return \Misd\PhoneNumberBundle\Form\Type\PhoneNumberType::class;
    }
}
