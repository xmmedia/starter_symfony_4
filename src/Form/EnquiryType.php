<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\DataTransformer\EmailTransformer;
use App\Model\Enquiry\Command\SubmitEnquiryForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnquiryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['maxlength' => 50],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => SubmitEnquiryForm::NAME_MIN_LENGTH,
                        'max' => SubmitEnquiryForm::NAME_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['maxlength' => 150],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email([
                        'strict'  => true,
                        'checkMX' => true,
                    ]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['maxlength' => 5000],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => SubmitEnquiryForm::MESSAGE_MIN_LENGTH,
                        'max' => SubmitEnquiryForm::MESSAGE_MAX_LENGTH,
                    ]),
                ],
            ])
        ;

        $builder->get('email')
            ->addModelTransformer(new EmailTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
