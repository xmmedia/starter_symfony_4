<?php

declare(strict_types=1);

namespace App\Tests\Form\User;

use App\Form\User\UserChangePasswordType;
use App\Tests\TypeTestCase;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;

class UserChangePasswordTypeTest extends TypeTestCase
{
    use MockeryPHPUnitIntegration;

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
        $faker = Faker\Factory::create();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'currentPassword' => $faker->password(12, 250),
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
