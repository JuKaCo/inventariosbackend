<?php
declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\Menu\MenuRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Persistence\Menu\DataMenuRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(InMemoryUserRepository::class),
        MenuRepository::class => \DI\autowire(DataMenuRepository::class),
    ]);
};
