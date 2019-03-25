<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EmailType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => 'Email',
            'constraints'     => [
                new Assert\Email([
                    'mode' => Assert\Email::VALIDATION_MODE_STRICT,
                ]),
                new Assert\Length([
                    'max' => 150,
                ]),
            ],
        ]);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\EmailType::class;
    }
}
