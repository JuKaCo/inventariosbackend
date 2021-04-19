<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\MenuAction;
use App\Application\Actions\Action\ParametricaAction;
use App\Application\Actions\Action\ProveedorAction;

return function (App $app) {
    $app->group('/api/v1/param', function (Group $group) {
        $group->get('/gen/{cod_grupo}', ParametricaAction::class.':genParam');
        $group->get('/gen/{cod_grupo}/{id_padre}', ParametricaAction::class.':genParamPadre');
        $group->get('/biometrico/terminal', ParametricaAction::class.':genParamBiometrico');
    });
    $app->group('/api/v1/proveedor', function(Group $group){
        $group->get('/obtiene/{id_proveedor}',ProveedorAction::class.':obtiene_Proveedor');
        $group->put('/edit/{id_proveedor}',ProveedorAction::class.':edita_Proveedor');
        $group->post('/create',ProveedorAction::class.':crea_Proveedor');
        $group->delete('/delete/{id_proveedor}',ProveedorAction::class.':elimina_Proveedor');
        $group->get('/lista/{filtro}/{items_pagina}/{pagina}',ProveedorAction::class.':lista_Proveedor');
    });
    $app->group('/api/v1/liname', function (Group $group) {
        $group->get('/lista', ParametricaAction::class.':genParamBiometrico');
        $group->post('/carga', ParametricaAction::class.':genParamBiometrico');
        $group->post('/consolida', ParametricaAction::class.':genParamBiometrico');
        $group->post('/habilitar', ParametricaAction::class.':genParamBiometrico');
        $group->delete('/dasavilitar/{id}', ParametricaAction::class.':genParamBiometrico');
        
    });
};
