<?php

declare(strict_types=1);

namespace App\Form\User;

use App\DataProvider\RoleProvider;
use App\Form\DataTransformer\NameTransformer;
use App\Form\DataTransformer\SecurityRoleTransformer;
use App\Form\DataTransformer\UserIdTransformer;
use App\Form\Type\EmailType;
use App\Model\User\Name;
use App\Model\User\User;
use App\Validator\Constraints\UniqueExistingUserEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserUpdateType extends AbstractType
{
    /** @var RoleProvider */
    private $roleProvider;

    /** @var SecurityRoleTransformer */
    private $securityRoleTransformer;

    public function __construct(
        RoleProvider $roleProvider,
        SecurityRoleTransformer $securityRoleTransformer
    ) {
        $this->roleProvider = $roleProvider;
        $this->securityRoleTransformer = $securityRoleTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userId', HiddenType::class, [
                'label'           => 'User ID',
                'invalid_message' => 'Invalid UUID.',
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('changePassword', CheckboxType::class, [
                'label' => 'Change Password',
            ])
            // @todo additional validation: check common passwords
            ->add('password', PasswordType::class, [
                'label'       => 'Password',
                'constraints' => [
                    new Assert\NotBlank(['groups' => ['password']]),
                    new Assert\Length([
                        'min'    => User::PASSWORD_MIN_LENGTH,
                        'max'    => BasePasswordEncoder::MAX_PASSWORD_LENGTH,
                        'groups' => ['password'],
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
                'invalid_message' => '"{{ value }}" is not a valid name.',
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
                'invalid_message' => '"{{ value }}" is not a valid name.',
            ])
            ->add('role', ChoiceType::class, [
                'label'           => 'Role',
                'choices'         => ($this->roleProvider)(),
                'invalid_message' => '"{{ value }}" is not a valid role.',
            ])
        ;

        $builder->get('userId')
            ->addModelTransformer(new UserIdTransformer());
        $builder->get('role')
            ->addModelTransformer($this->securityRoleTransformer);
        $builder->get('firstName')
            ->addModelTransformer(new NameTransformer());
        $builder->get('lastName')
            ->addModelTransformer(new NameTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $data = $form->getData();

                if ($data['changePassword']) {
                    $groups[] = 'password';
                }

                return $groups;
            },
            'constraints'       => [
                new UniqueExistingUserEmail(),
            ],
        ]);
    }
}
