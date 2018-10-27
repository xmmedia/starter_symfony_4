<?php

declare(strict_types=1);

namespace App\DataFixtures\Processor;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserProcessor implements ProcessorInterface
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User $object
     */
    public function preProcess(string $id, $object): void
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
    public function postProcess(string $id, $object): void
    {
        // do nothing
    }
}
