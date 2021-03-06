<?php

declare(strict_types=1);

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class                      => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class       => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class                       => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class           => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class                        => ['all' => true],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class                  => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class                  => ['dev' => true, 'test' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                                => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class                          => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class                              => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class                              => ['dev' => true],
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class                       => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class               => ['dev' => true, 'test' => true],
    Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle::class                       => ['dev' => true, 'test' => true],
    Misd\PhoneNumberBundle\MisdPhoneNumberBundle::class                        => ['all' => true],
    Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle::class => ['dev' => true, 'test' => true],
    Liip\FunctionalTestBundle\LiipFunctionalTestBundle::class                  => ['dev' => true, 'test' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class                     => ['all' => true],
    Prooph\Bundle\EventStore\ProophEventStoreBundle::class                     => ['all' => true],
    Overblog\GraphQLBundle\OverblogGraphQLBundle::class                        => ['all' => true],
    Overblog\GraphiQLBundle\OverblogGraphiQLBundle::class                      => ['dev' => true],
    Xm\SymfonyBundle\XmSymfonyBundle::class                                    => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class                          => ['all' => true],
];
