<?php

declare(strict_types=1);

namespace App\Tests\Form\Type;

use App\Form\Type\UuidType;
use App\Tests\TypeTestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UuidTypeTest extends TypeTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();

        $uuid = $faker->uuid;

        $formData = [
            'uuid' => $uuid,
        ];

        $form = $this->factory->create(UuidTypeTestForm::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);

        $this->assertEquals($uuid, $form->getData()['uuid']);
    }

    public function testInvalid(): void
    {
        $faker = $this->faker();

        $formData = [
            'uuid' => substr($faker->uuid, 0, \strlen($faker->uuid) - 1),
        ];

        $form = $this->factory->create(UuidTypeTestForm::class)
            ->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->isValid());

        $this->assertCount(1, $form->get('uuid')->getErrors(true, true));
    }
}

class UuidTypeTestForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('uuid', UuidType::class);
    }
}
