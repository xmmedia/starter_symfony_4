<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UuidType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'       => 'UUID',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Uuid(),
            ],
        ]);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\TextType::class;
    }
}
