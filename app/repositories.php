<?php
declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\MenuRepository;
use App\Domain\ParametricaRepository;
use App\Domain\RrhhRepository;
use App\Domain\LinameRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Persistence\DataMenuRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataRrhhRepository;
use App\Infrastructure\Persistence\DataLinameRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(InMemoryUserRepository::class),
        MenuRepository::class => \DI\autowire(DataMenuRepository::class),
        ParametricaRepository::class => \DI\autowire(DataParametricaRepository::class),
        RrhhRepository::class => \DI\autowire(DataRrhhRepository::class),
        LinameRepository::class => \DI\autowire(DataLinameRepository::class),
    ]);
};
