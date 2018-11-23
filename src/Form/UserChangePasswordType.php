<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label'       => 'Current Password',
                'constraints' => [
                    new Assert\NotBlank(),
                    new UserPassword([
                        'message' => 'Please check that this matches your current password.',
                    ]),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'label'           => 'New Password',
                'attr'            => ['maxlength' => BasePasswordEncoder::MAX_PASSWORD_LENGTH],
                'invalid_message' => 'The passwords must match.',
                'constraints'     => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => User::PASSWORD_MIN_LENGTH,
                        'max' => BasePasswordEncoder::MAX_PASSWORD_LENGTH,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
