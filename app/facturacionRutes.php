<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\FacturacionAction;

return function (App $app) {
    $app->group('/api/v1/factura', function (Group $group) {
        $group->get('/dosificacion/obtener/{id_dosificacion}', FacturacionAction::class.':obtiene_dosificacion');
        $group->get('/dosificacion/listar', FacturacionAction::class.':lista_dosificacion');
        $group->post('/dosificacion/crear', FacturacionAction::class.':crea_docificacion');
        $group->put('/dosificacion/editar/{id_dosificacion}', FacturacionAction::class.':edita_docificacion');
        $group->patch('/dosificacion/modificar/{id_dosificacion}', FacturacionAction::class.':modifica_docificacion');
        $group->delete('/dosificacion/cambiarestado/{id_dosificacion}', FacturacionAction::class.':cambiaestado_dosificacion');
    });
};
