<?php

namespace App\DataFixtures\Processor;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserProcessor implements ProcessorInterface
{
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    /**
     * @param UserPasswordEncoder $passwordEncoder
     */
    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * {@inheritdoc}
     *
     * @param User $object
     */
    public function preProcess(string $id, $object)
    {
        if (false === $object instanceof User) {
            return;
        }

        $object->setPassword($this->passwordEncoder->encodePassword(
            $object,
            $object->getPlainPassword()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess(string $id, $object)
    {
        // do nothing
    }
}