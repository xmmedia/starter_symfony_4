<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Form\DataTransformer\NameTransformer;
use App\Form\Type\EmailType;
use App\Model\User\Name;
use App\Validator\Constraints\UniqueCurrentUsersEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new UniqueCurrentUsersEmail(),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label'       => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::NAME_MIN_LENGTH,
                        'max' => Name::NAME_MAX_LENGTH,
                    ]),
                ],
                'invalid_message' => '"{{ value }}" is not a valid name.',
            ])
            ->add('lastName', TextType::class, [
                'label'       => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::NAME_MIN_LENGTH,
                        'max' => Name::NAME_MAX_LENGTH,
                    ]),
                ],
                'invalid_message' => '"{{ value }}" is not a valid name.',
            ])
        ;

        $builder->get('firstName')
            ->addModelTransformer(new NameTransformer());
        $builder->get('lastName')
            ->addModelTransformer(new NameTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
