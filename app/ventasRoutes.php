<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Actions\Action\CotizacionAction;
use App\Application\Actions\Action\VentaAction;
use App\Application\Actions\Action\ItemSecAction;
use App\Application\Actions\Action\SalidaAction;

return function (App $app) {
    //cotizacion rutas
    $app->group('/api/v1/cotizacion', function(Group $group){
        $group->get('/obtener/{id_cotizacion}',CotizacionAction::class.':obtiene_Cotizacion');
        $group->put('/editar/{id_cotizacion}',CotizacionAction::class.':edita_Cotizacion');
        $group->post('/crear',CotizacionAction::class.':crea_Cotizacion');
        $group->delete('/cambiarestado/{id_cotizacion}',CotizacionAction::class.':cambiaestado_Cotizacion');
        $group->get('/listar',CotizacionAction::class.':lista_Cotizacion');
        $group->patch('/modificar/{id_cotizacion}',CotizacionAction::class.':modifica_Cotizacion');
    });
    //ventas rutas
    $app->group('/api/v1/venta', function(Group $group){
        $group->get('/obtener/{id_venta}',VentaAction::class.':obtiene_Venta');
        $group->put('/editar/{id_venta}',VentaAction::class.':edita_Venta');
        $group->post('/crear',VentaAction::class.':crea_Venta');
        $group->delete('/cambiarestado/{id_venta}',VentaAction::class.':cambiaestado_Venta');
        $group->get('/listar',VentaAction::class.':lista_Venta');
        $group->patch('/modificar/{id_venta}',VentaAction::class.':modifica_Venta');
    });
    //itemsec rutas
    $app->group('/api/v1/itemsec', function(Group $group){
        $group->get('/obtener/{id_itemsec}',ItemSecAction::class.':obtiene_ItemSec');
        $group->put('/editar/{id_itemsec}',ItemSecAction::class.':edita_ItemSec');
        $group->post('/crear',ItemSecAction::class.':crea_ItemSec');
        $group->delete('/cambiarestado/{id_itemsec}',ItemSecAction::class.':cambiaestado_ItemSec');
        $group->get('/listar/{id_coti_vent}',ItemSecAction::class.':lista_ItemSec');
        $group->patch('/modificar/{id_itemsec}',ItemSecAction::class.':modifica_ItemSec');
        $group->post('/calcular',ItemSecAction::class.':calcula_Precio_ItemSec');
    });
    //salida rutas
    $app->group('/api/v1/salida', function(Group $group){
        $group->get('/obtener/{id_salida}',SalidaAction::class.':obtiene_Salida');
        $group->put('/editar/{id_salida}',SalidaAction::class.':edita_Salida');
        $group->post('/crear',SalidaAction::class.':crea_Salida');
        $group->delete('/cambiarestado/{id_salida}',SalidaAction::class.':cambiaestado_Salida');
        $group->get('/listar',SalidaAction::class.':lista_Salida');
        $group->patch('/modificar/{id_salida}',SalidaAction::class.':modifica_Salida');
        $group->post('/calcular',SalidaAction::class.':calcula_Precio_Salida');
    });

};
