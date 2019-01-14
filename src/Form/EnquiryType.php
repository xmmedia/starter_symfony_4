<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\EmailTransformer;
use App\Model\Enquiry\Enquiry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnquiryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'       => 'Name',
                'attr'        => ['maxlength' => Enquiry::NAME_MIN_LENGTH],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Enquiry::NAME_MIN_LENGTH,
                        'max' => Enquiry::NAME_MAX_LENGTH,
                        'minMessage' => 'The name is a bit short.',
                        'maxMessage' => 'This is a bit long. Only include your first and last name.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Email',
                'attr'        => ['maxlength' => 150],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(['mode' => 'strict']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label'       => 'Message',
                'attr'        => ['maxlength' => 5000],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Enquiry::MESSAGE_MIN_LENGTH,
                        'max' => Enquiry::MESSAGE_MAX_LENGTH,
                        'minMessage' => 'Your message needs to be a bit longer.',
                        'maxMessage' => 'Your message is too long (max 5000 characters). Try shortening it and we can confirm the details at a later date.',
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
