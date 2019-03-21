<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserRecoverResetType;
use App\Model\User\Token;
use App\Tests\TypeTestCase;

class UserRecoverResetTypeTest extends TypeTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $newPassword = $faker->password(12, 250);

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

        $this->assertInstanceOf(Token::class, $form->getData()['token']);
    }
}
