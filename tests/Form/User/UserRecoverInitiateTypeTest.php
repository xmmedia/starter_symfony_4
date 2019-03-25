<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserRecoverInitiateType;
use App\Tests\TypeTestCase;

class UserRecoverInitiateTypeTest extends TypeTestCase
{
    public function test()
    {
        $faker = $this->faker();

        $formData = [
            'email' => $faker->email,
        ];

        $form = $this->factory->create(UserRecoverInitiateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
