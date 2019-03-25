<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\EmailTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new EmailTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => 'Email',
            'constraints'     => [
                new Assert\Email(['mode' => 'strict']),
                new Assert\Length([
                    'max' => 150,
                ]),
            ],
            'invalid_message' => '"{{ value }}" is not a valid email.',
        ]);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\EmailType::class;
    }
}
