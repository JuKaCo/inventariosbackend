<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Actions\Action\ProductoAction;

return function (App $app) {

    //producto rutas
    $app->group('/api/v1/producto', function(Group $group){
        $group->get('/obtener/{id_producto}',ProductoAction::class.':obtiene_Producto');
        $group->put('/editar/{id_producto}',ProductoAction::class.':edita_Producto');
        $group->post('/crear',ProductoAction::class.':crea_Producto');
        $group->delete('/cambiarestado/{id_producto}',ProductoAction::class.':cambiaestado_Producto');
        $group->get('/listar',ProductoAction::class.':lista_Producto');
    });

};
