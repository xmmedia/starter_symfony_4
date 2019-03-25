<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserRecoverResetType;
use App\Tests\TypeTestCase;

class UserRecoverResetTypeTest extends TypeTestCase
{
    public function test()
    {
        $faker = $this->faker();

        $newPassword = $faker->password;

        $formData = [
            'token'       => $faker->password,
            'newPassword' => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserRecoverResetType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
