<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Form\DataTransformer\TokenTransformer;
use App\Model\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserVerifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('token', TextType::class, [
                'label'           => 'Token',
                'invalid_message' => '"{{ value }}" is not a valid token.',
            ])
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'label'           => 'New Password',
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

        $builder->get('token')
            ->addModelTransformer(new TokenTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
