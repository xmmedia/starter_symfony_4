<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserChangePasswordType;
use App\Tests\TypeTestCase;
use Mockery;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;

class UserChangePasswordTypeTest extends TypeTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $validator = Mockery::mock(UserPasswordValidator::class);
        $validator->shouldReceive('initialize');
        $validator->shouldReceive('validate');

        $this->validatorContainer->set(
            'security.validator.user_password',
            $validator
        );
    }

    public function test()
    {
        $faker = $this->faker();

        $newPassword = $faker->password;

        $formData = [
            'currentPassword' => $faker->password,
            'newPassword'     => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserChangePasswordType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
