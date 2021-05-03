<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Actions\Action\ProductoAction;
use App\Application\Actions\Action\RegionalAction;

return function (App $app) {

    //producto rutas
    $app->group('/api/v1/producto', function(Group $group){
        $group->get('/obtener/{id_producto}',ProductoAction::class.':obtiene_Producto');
        $group->put('/editar/{id_producto}',ProductoAction::class.':edita_Producto');
        $group->post('/crear',ProductoAction::class.':crea_Producto');
        $group->delete('/cambiarestado/{id_producto}',ProductoAction::class.':cambiaestado_Producto');
        $group->get('/listar',ProductoAction::class.':lista_Producto');
    });
    //regional rutas
    $app->group('/api/v1/regional', function(Group $group){
        $group->get('/obtener/{id_regional}',RegionalAction::class.':obtiene_Regional');
        $group->put('/editar/{id_regional}',RegionalAction::class.':edita_Regional');
        $group->post('/crear',RegionalAction::class.':crea_Regional');
        $group->delete('/cambiarestado/{id_regional}',RegionalAction::class.':cambiaestado_Regional');
        $group->get('/listar',RegionalAction::class.':lista_Regional');
    });

};
