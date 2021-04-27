<?php
declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\MenuRepository;
use App\Domain\ParametricaRepository;
use App\Domain\RrhhRepository;
use App\Domain\ProveedorRepository;
use App\Domain\ClienteRepository;
use App\Domain\LinameRepository;
use App\Domain\LinadimeRepository;
use App\Domain\CorrelativoRepository;
use App\Domain\NotificacionRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Persistence\DataMenuRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataRrhhRepository;
use App\Infrastructure\Persistence\DataProveedorRepository;
use App\Infrastructure\Persistence\DataClienteRepository;
use App\Infrastructure\Persistence\DataLinameRepository;
use App\Infrastructure\Persistence\DataLinadimeRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataNotificacionRepository;

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(InMemoryUserRepository::class),
        MenuRepository::class => \DI\autowire(DataMenuRepository::class),
        ParametricaRepository::class => \DI\autowire(DataParametricaRepository::class),
        RrhhRepository::class => \DI\autowire(DataRrhhRepository::class),
        ProveedorRepository::class => \DI\autowire(DataProveedorRepository::class),
        ClienteRepository::class => \DI\autowire(DataClienteRepository::class),
        LinameRepository::class => \DI\autowire(DataLinameRepository::class),
        LinadimeRepository::class => \DI\autowire(DataLinadimeRepository::class),
        CorrelativoRepository::class => \DI\autowire(DataCorrelativoRepository::class),
        NotificacionRepository::class => \DI\autowire(DataNotificacionRepository::class)
    ]);
};
