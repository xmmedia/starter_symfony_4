<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\EnquiryType;
use App\Model\Email;
use App\Tests\TypeTestCase;

class EnquiryTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $faker = $this->faker();

        $formData = [
            'name'    => $faker->name,
            'email'   => $faker->email,
            'message' => $faker->text,
        ];

        $form = $this->factory->create(EnquiryType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
    }
}
