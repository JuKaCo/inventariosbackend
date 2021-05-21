<?php
declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Domain\MenuRepository;
use App\Domain\ParametricaRepository;
use App\Domain\RrhhRepository;
use App\Domain\ProveedorRepository;
use App\Domain\ClienteRepository;
use App\Domain\ProductoRepository;
use App\Domain\LinameRepository;
use App\Domain\LinadimeRepository;
use App\Domain\CorrelativoRepository;
use App\Domain\NotificacionRepository;
use App\Domain\RegionalRepository;
use App\Domain\ProgramaRepository;
use App\Domain\AlmacenRepository;
use App\Domain\CompraRepository;
use App\Domain\ItemRepository;
use App\Domain\EntradaRepository;
use App\Domain\ReporteRepository;
use App\Domain\KardexRepository;
use App\Domain\ItemSecRepository;
use App\Domain\CotizacionRepository;
use App\Domain\VentaRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Persistence\DataMenuRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataRrhhRepository;
use App\Infrastructure\Persistence\DataProveedorRepository;
use App\Infrastructure\Persistence\DataClienteRepository;
use App\Infrastructure\Persistence\DataProductoRepository;
use App\Infrastructure\Persistence\DataLinameRepository;
use App\Infrastructure\Persistence\DataLinadimeRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use App\Infrastructure\Persistence\DataNotificacionRepository;
use App\Infrastructure\Persistence\DataRegionalRepository;
use App\Infrastructure\Persistence\DataProgramaRepository;
use App\Infrastructure\Persistence\DataAlmacenRepository;
use App\Infrastructure\Persistence\DataCompraRepository;
use App\Infrastructure\Persistence\DataItemRepository;
use App\Infrastructure\Persistence\DataEntradaRepository;
use App\Infrastructure\Persistence\DataReporteRepository;
use App\Infrastructure\Persistence\DataKardexRepository;
use App\Infrastructure\Persistence\DataCotizacionRepository;
use App\Infrastructure\Persistence\DataItemSecRepository;
use App\Infrastructure\Persistence\DataVentaRepository;

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
        ProductoRepository::class => \DI\autowire(DataProductoRepository::class),
        LinameRepository::class => \DI\autowire(DataLinameRepository::class),
        LinadimeRepository::class => \DI\autowire(DataLinadimeRepository::class),
        CorrelativoRepository::class => \DI\autowire(DataCorrelativoRepository::class),
        NotificacionRepository::class => \DI\autowire(DataNotificacionRepository::class),
        RegionalRepository::class => \DI\autowire(DataRegionalRepository::class),
        ProgramaRepository::class => \DI\autowire(DataProgramaRepository::class),
        AlmacenRepository::class => \DI\autowire(DataAlmacenRepository::class),
        CompraRepository::class => \DI\autowire(DataCompraRepository::class),
        ItemRepository::class => \DI\autowire(DataItemRepository::class),


        EntradaRepository::class=> \DI\autowire(DataEntradaRepository::class),

        ReporteRepository::class=> \DI\autowire(DataReporteRepository::class),
        KardexRepository::class=> \DI\autowire(DataKardexRepository::class),
        ItemSecRepository::class=> \DI\autowire(DataCotizacionRepository::class),
        CotizacionRepository::class=> \DI\autowire(DataItemSecRepository::class),
        VentaRepository::class=> \DI\autowire(DataVentaRepository::class),

    ]);
};
