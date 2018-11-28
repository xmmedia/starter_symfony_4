<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\EnquiryType;
use App\Model\Email;
use Faker;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class EnquiryTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function testSubmitValidData()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'name'    => $faker->name,
            'email'   => $faker->email,
            'message' => $faker->text,
        ];

        $form = $this->factory->create(EnquiryType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
    }
}
