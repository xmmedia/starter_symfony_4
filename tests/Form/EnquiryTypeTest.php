<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\EnquiryType;
use Xm\SymfonyBundle\Tests\TypeTestCase;

class EnquiryTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
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
    }
}
