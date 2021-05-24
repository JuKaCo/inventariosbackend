<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\DosificacionAction;

return function (App $app) {
    $app->group('/api/v1/factura', function (Group $group) {
        $group->get('/dosificacion/obtener/{id_dosificacion}', DosificacionAction::class.':obtiene_dosificacion');
        $group->get('/dosificacion/listar', DosificacionAction::class.':lista_dosificacion');
        $group->post('/dosificacion/crear', DosificacionAction::class.':crea_docificacion');
        $group->put('/dosificacion/editar/{id_dosificacion}', DosificacionAction::class.':edita_docificacion');
        $group->patch('/dosificacion/modificar/{id_dosificacion}', DosificacionAction::class.':modifica_docificacion');
        $group->delete('/dosificacion/cambiarestado/{id_dosificacion}', DosificacionAction::class.':cambiaestado_dosificacion');
    });
};
