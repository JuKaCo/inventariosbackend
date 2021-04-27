<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\NotificacionAction;

return function (App $app) {

    $app->group('/api/v1/notificacion', function (Group $group) {
        $group->get('/listar', NotificacionAction::class.':lista_notificacion');
        $group->get('/{id_notificacion}', NotificacionAction::class.':lista_id_notificacion');
        $group->post('/crear', NotificacionAction::class.':crea_notificacion');
        $group->delete('/cambia_estado/{id_notificacion}', NotificacionAction::class.':inactiva_notificacion');
        $group->patch('/confirma/{id_notificacion}', NotificacionAction::class.':confirma_notificacion');
    });

};