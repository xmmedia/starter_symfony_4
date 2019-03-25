<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'       => 'Phone Number',
            'constraints' => [
                new PhoneNumber(),
            ],
        ]);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }
}
