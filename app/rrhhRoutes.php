<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\RrhhAction;

return function (App $app) {
    $app->group('/api/v1/rrhh', function (Group $group) {
        $group->post('/reportegeneral', RrhhAction::class.':genReporteGeneral');
    });
};
