<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\EmailTransformer;
use App\Form\DataTransformer\NameTransformer;
use App\Form\DataTransformer\SecurityRoleTransformer;
use App\Model\User\Name;
use App\Model\User\User;
use App\Validator\Constraints\UniqueNewUserEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserCreateType extends AbstractType
{
    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $roles = array_map(function (Role $role) {
            return $role->getRole();
        }, $this->roleHierarchy->getReachableRoles([new Role('ROLE_SUPER_ADMIN')]));

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
                    new UniqueNewUserEmail(),
                ],
            ])
            ->add('setPassword', CheckboxType::class, [
                'label' => 'Set Password',
            ])
            // @todo additional validation: check common passwords
            ->add('password', PasswordType::class, [
                'label'       => 'Password',
                'attr'        => ['maxlength' => BasePasswordEncoder::MAX_PASSWORD_LENGTH],
                'constraints' => [
                    new Assert\Length([
                        'min'    => User::PASSWORD_MIN_LENGTH,
                        'max'    => BasePasswordEncoder::MAX_PASSWORD_LENGTH,
                        'groups' => ['password'],
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label'       => 'First Name',
                'attr'        => ['maxlength' => Name::NAME_MAX_LENGTH],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::NAME_MIN_LENGTH,
                        'max' => Name::NAME_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'       => 'Last Name',
                'attr'        => ['maxlength' => Name::NAME_MAX_LENGTH],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::NAME_MIN_LENGTH,
                        'max' => Name::NAME_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => $roles,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Active',
            ])
        ;

        $builder->get('email')
            ->addModelTransformer(new EmailTransformer());
        $builder->get('role')
            ->addModelTransformer(new SecurityRoleTransformer());
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

                if ($data['setPassword']) {
                    $groups[] = 'password';
                }

                return $groups;
            },
        ]);
    }
}
