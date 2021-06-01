<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\ReporteAction;

return function (App $app) {
    $app->group('/api/v1/reporte', function (Group $group) {

        $group->get('/entrada/notaingreso/{id}', ReporteAction::class.':getEntradaNotaIngreso');
        $group->get('/entrada/actarecepcion/{id}', ReporteAction::class.':getEntradaActaRecepcion');

        $group->get('/cotizacion/{id}', ReporteAction::class.':getCotizacionReporte');
        
    });
};
