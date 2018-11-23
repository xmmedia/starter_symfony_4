<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\User;
use App\Serializer\SerializerGroupTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class UserNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;
    use SerializerGroupTrait;

    /**
     * @param User $user
     */
    public function normalize(
        $user,
        $format = null,
        array $context = array()
    ): array {
        if (!$user instanceof User) {
            throw new InvalidArgumentException(
                sprintf(
                    'The object must be an instance of "%s".',
                    User::class
                )
            );
        }

        $this->context = $context;

        $data = [];

        if ($this->hasGroup(['user_admin'])) {
            $data['id'] = $user->id()->toString();
            $data['email'] = $user->email()->toString();
            $data['name'] = $user->name();
            $data['firstName'] = $user->firstName()->toString();
            $data['lastName'] = $user->lastName()->toString();
            $data['enabled'] = $user->enabled();
            $data['locked'] = $user->locked();
            $data['roles'] = $user->roles();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }
}
