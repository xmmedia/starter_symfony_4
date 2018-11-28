<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\DataProvider\RoleProvider;
use App\Form\AdminUserEditType;
use App\Model\Email;
use App\Model\User\Name;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AdminUserEditTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;
    use MockeryPHPUnitIntegration;

    protected function getTypes()
    {
        $extensions = parent::getTypes();

        $roleHierarchy = Mockery::mock(RoleHierarchyInterface::class);
        $roleHierarchy->shouldReceive('getReachableRoles')
            ->andReturn([new Role('ROLE_USER')]);
        $extensions[] = new AdminUserEditType(new RoleProvider($roleHierarchy));

        return $extensions;
    }

    public function test()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'id'             => $faker->uuid,
            'changePassword' => true,
            'password'       => $faker->password,
            'email'          => $faker->email,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $form = $this->factory->create(AdminUserEditType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());

        $this->assertInstanceOf(Email::class, $form->getData()['email']);
        $this->assertInstanceOf(Role::class, $form->getData()['role']);
        $this->assertInstanceOf(Name::class, $form->getData()['firstName']);
        $this->assertInstanceOf(Name::class, $form->getData()['lastName']);
    }

    public function testNoPassword()
    {
        $faker = Faker\Factory::create();

        $formData = [
            'email'          => $faker->email,
            'changePassword' => false,
            'firstName'      => $faker->name,
            'lastName'       => $faker->name,
            'role'           => 'ROLE_USER',
        ];

        $form = $this->factory->create(AdminUserEditType::class)
            ->submit($formData);

        $this->assertTrue($form->isValid());
    }
}
