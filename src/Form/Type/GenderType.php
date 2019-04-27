<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Model\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => 'Gender',
            'choices'         => Gender::getValues(),
            'invalid_message' => '"{{ value }}" is an invalid gender.',
        ]);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }
}
