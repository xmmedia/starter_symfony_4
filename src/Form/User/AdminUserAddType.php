<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Form\Type\EmailType;
use App\Form\Type\UuidType;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\User;
use App\Validator\Constraints\UniqueNewUserEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Validator\Constraints as Assert;

class AdminUserAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userId', UuidType::class, [
                'label' => 'User ID',
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new UniqueNewUserEmail(),
                ],
            ])
            ->add('setPassword', CheckboxType::class, [
                'label' => 'Set Password',
            ])
            ->add('password', PasswordType::class, [
                'label'       => 'Password',
                'constraints' => [
                    new Assert\NotBlank(['groups' => ['password']]),
                    new Assert\Length([
                        'min'    => User::PASSWORD_MIN_LENGTH,
                        'max'    => BasePasswordEncoder::MAX_PASSWORD_LENGTH,
                        'groups' => ['password'],
                    ]),
                    new Assert\NotCompromisedPassword([
                        'threshold' => 3,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label'       => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::MIN_LENGTH,
                        'max' => Name::MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'       => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::MIN_LENGTH,
                        'max' => Name::MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label'           => 'Role',
                'choices'         => Role::getValues(),
                'invalid_message' => '"{{ value }}" is not a valid role.',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Active',
            ])
            ->add('sendInvite', CheckboxType::class, [
                'label' => 'Send Invite',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $data = $form->getData();

                if ($data['setPassword']) {
                    $groups[] = 'password';
                }

                return $groups;
            },
        ]);
    }
}
