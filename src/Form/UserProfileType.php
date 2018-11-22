<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\EmailTransformer;
use App\Model\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                'label'       => 'Email',
                'attr'        => ['maxlength' => 150],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email([
                        'strict'  => true,
                        'checkMX' => true,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label'       => 'First Name',
                'attr'        => ['maxlength' => User::NAME_MAX_LENGTH],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => User::NAME_MIN_LENGTH,
                        'max' => User::NAME_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'       => 'Last Name',
                'attr'        => ['maxlength' => User::NAME_MAX_LENGTH],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => User::NAME_MIN_LENGTH,
                        'max' => User::NAME_MAX_LENGTH,
                    ]),
                ],
            ])
        ;

        $builder->get('email')
            ->addModelTransformer(new EmailTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
