<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use App\Entity\User;
use Faker;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserNormalizerTest extends KernelTestCase
{
    /**
     * @dataProvider userProvider
     */
    public function test(User $user, array $expected, array $groups): void
    {
        self::bootKernel();

        $result = self::$container->get('serializer')
            ->normalize($user, 'json', ['groups' => $groups])
        ;

        $this->assertEquals($expected, $result);
    }

    public function userProvider(): \Generator
    {
        $faker = Faker\Factory::create();

        $uuid = $faker->uuid;
        $email = $faker->email;
        $firstName = $faker->name;
        $lastName = $faker->name;

        $user = new User();
        $reflection = new \ReflectionClass(User::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, Uuid::fromString($uuid));
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user, $email);
        $property = $reflection->getProperty('firstName');
        $property->setAccessible(true);
        $property->setValue($user, $firstName);
        $property = $reflection->getProperty('lastName');
        $property->setAccessible(true);
        $property->setValue($user, $lastName);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user, ['ROLE_USER']);

        $expected = [
            'id'         => $uuid,
            'email'      => $email,
            'name'       => $firstName.' '.$lastName,
            'firstName'  => $firstName,
            'lastName'   => $lastName,
            'verified'   => false,
            'active'     => false,
            'roles'      => ['ROLE_USER'],
            'lastLogin'  => null,
            'loginCount' => 0,
        ];

        yield [$user, $expected, ['user_admin']];

        $user = clone $user;

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('lastLogin');
        $property->setAccessible(true);
        $property->setValue($user, new \DateTime('2018-01-01 15:00:00'));

        $expected['lastLogin'] = '2018-01-01T15:00:00+00:00';

        yield [$user, $expected, ['user_admin']];

        $user = clone $user;

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('firstName');
        $property->setAccessible(true);
        $property->setValue($user, null);
        $property = $reflection->getProperty('lastName');
        $property->setAccessible(true);
        $property->setValue($user, null);

        $expected['name'] = '';
        $expected['firstName'] = null;
        $expected['lastName'] = null;

        yield [$user, $expected, ['user_admin']];
    }
}
