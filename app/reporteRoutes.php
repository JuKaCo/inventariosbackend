<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\ReportesAction;

return function (App $app) {
    $app->group('/api/v1/reporte/entrada', function (Group $group) {

        $group->get('/notaingreso/{id}', ReportesAction::class.':getEntradaNotaIngreso');
        
    });
};
