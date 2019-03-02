<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\User\UserVerifyType;
use App\Model\User\Token;
use Faker;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

class UserVerifyTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'token'       => $faker->password,
            'password'    => [
                'first'  => $faker->password,
                'second' => $faker->password,
            ],
        ];

        $form = $this->factory->create(UserVerifyType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Token::class, $form->getData()['token']);
    }
}
