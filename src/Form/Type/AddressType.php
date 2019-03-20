<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\DataProvider\CountryProvider;
use App\DataProvider\ProvinceProvider;
use App\Form\DataTransformer\AddressTransformer;
use App\Form\DataTransformer\CountryTransformer;
use App\Form\DataTransformer\PostalCodeTransformer;
use App\Form\DataTransformer\ProvinceTransformer;
use App\Model\Address;
use App\Model\PostalCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('line1', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Address::LINE1_MIN_LENGTH,
                        'max' => Address::LINE1_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('line2', TextType::class, [
                'constraints' => [
                    new Assert\Length([
                        'min' => Address::LINE2_MIN_LENGTH,
                        'max' => Address::LINE2_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Address::CITY_MIN_LENGTH,
                        'max' => Address::CITY_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('province', ChoiceType::class, [
                'choices'     => ProvinceProvider::all(),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'invalid_message' => 'The province or state "{{ value }}" is not allowed.',
            ])
            ->add('postalCode', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => PostalCode::MIN_LENGTH,
                        'max' => PostalCode::MAX_LENGTH,
                    ]),
                ],
                'invalid_message' => '"{{ value }}" is not a valid Postal or Zip Code.',
            ])
            ->add('country', ChoiceType::class, [
                'choices'     => CountryProvider::all(),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'invalid_message' => 'The country "{{ value }}" is not an allowed country.',
            ])
        ;

        $builder->addModelTransformer(new AddressTransformer());
        $builder->get('province')
            ->addModelTransformer(new ProvinceTransformer());
        $builder->get('postalCode')
            ->addModelTransformer(new PostalCodeTransformer());
        $builder->get('country')
            ->addModelTransformer(new CountryTransformer());
    }
}
