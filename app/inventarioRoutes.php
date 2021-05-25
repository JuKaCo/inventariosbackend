<?php

declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use App\Application\Actions\Action\ProductoAction;
use App\Application\Actions\Action\RegionalAction;
use App\Application\Actions\Action\ProgramaAction;
use App\Application\Actions\Action\AlmacenAction;
use App\Application\Actions\Action\CompraAction;
use App\Application\Actions\Action\ItemAction;
use App\Application\Actions\Action\EntradaAction;
use App\Application\Actions\Action\KardexAction;

return function (App $app) {

    //producto rutas
    $app->group('/api/v1/producto', function(Group $group){
        $group->get('/obtener/{id_producto}',ProductoAction::class.':obtiene_Producto');
        $group->put('/editar/{id_producto}',ProductoAction::class.':edita_Producto');
        $group->post('/crear',ProductoAction::class.':crea_Producto');
        $group->delete('/cambiarestado/{id_producto}',ProductoAction::class.':cambiaestado_Producto');
        $group->get('/listar',ProductoAction::class.':lista_Producto');
        // $group->patch('/modificar/{id_compra}',ProductoAction::class.':modifica_producto');
    });
    //regional rutas
    $app->group('/api/v1/regional', function(Group $group){
        $group->get('/obtener/{id_regional}',RegionalAction::class.':obtiene_Regional');
        $group->put('/editar/{id_regional}',RegionalAction::class.':edita_Regional');
        $group->post('/crear',RegionalAction::class.':crea_Regional');
        $group->delete('/cambiarestado/{id_regional}',RegionalAction::class.':cambiaestado_Regional');
        $group->get('/listar',RegionalAction::class.':lista_Regional');
    });
    //programa rutas
    $app->group('/api/v1/programa', function(Group $group){
        $group->get('/obtener/{id_programa}',ProgramaAction::class.':obtiene_Programa');
        $group->put('/editar/{id_programa}',ProgramaAction::class.':edita_Programa');
        $group->post('/crear',ProgramaAction::class.':crea_Programa');
        $group->delete('/cambiarestado/{id_programa}',ProgramaAction::class.':cambiaestado_Programa');
        $group->get('/listar',ProgramaAction::class.':lista_Programa');
    });
    //almacen rutas
    $app->group('/api/v1/almacen', function(Group $group){
        $group->get('/obtener/{id_almacen}',AlmacenAction::class.':obtiene_Almacen');
        $group->put('/editar/{id_almacen}',AlmacenAction::class.':edita_Almacen');
        $group->post('/crear',AlmacenAction::class.':crea_Almacen');
        $group->delete('/cambiarestado/{id_almacen}',AlmacenAction::class.':cambiaestado_Almacen');
        $group->get('/listar',AlmacenAction::class.':lista_Almacen');
    });
    //compras rutas
    $app->group('/api/v1/compra', function(Group $group){
        $group->get('/obtener/{id_compra}',CompraAction::class.':obtiene_Compra');
        $group->put('/editar/{id_compra}',CompraAction::class.':edita_Compra');
        $group->post('/crear',CompraAction::class.':crea_Compra');
        $group->delete('/cambiarestado/{id_compra}',CompraAction::class.':cambiaestado_Compra');
        $group->get('/listar',CompraAction::class.':lista_Compra');
        $group->patch('/modificar/{id_compra}',CompraAction::class.':modifica_Compra');
    });
    //item rutas
    $app->group('/api/v1/item', function(Group $group){
        $group->get('/obtener/{id_item}',ItemAction::class.':obtiene_Item');
        $group->put('/editar/{id_item}',ItemAction::class.':edita_Item');
        $group->post('/crear',ItemAction::class.':crea_Item');
        $group->delete('/cambiarestado/{id_item}',ItemAction::class.':cambiaestado_Item');
        $group->get('/listar/{id_entrada_salida}',ItemAction::class.':lista_Item');
        $group->patch('/modificar/{id_item}',ItemAction::class.':modifica_Item');
        $group->post('/calcular',ItemAction::class.':calcula_Precio_Item');
    });
    //entrada rutas
    $app->group('/api/v1/entrada', function(Group $group){
        $group->get('/obtener/{id_entrada}',EntradaAction::class.':obtiene_Entrada');
        $group->put('/editar/{id_entrada}',EntradaAction::class.':edita_Entrada');
        $group->post('/crear',EntradaAction::class.':crea_Entrada');
        $group->delete('/cambiarestado/{id_entrada}',EntradaAction::class.':cambiaestado_Entrada');
        $group->get('/listar',EntradaAction::class.':lista_Entrada');
        $group->patch('/modificar/{id_entrada}',EntradaAction::class.':modifica_Entrada');
        $group->post('/calcular',EntradaAction::class.':calcula_Precio_Entrada');
    });
    //kardex rutas
    $app->group('/api/v1/kardex', function(Group $group){
        $group->get('/obtener/{id_kardex}',KardexAction::class.':obtiene_Kardex');
        $group->put('/editar/{id_kardex}',KardexAction::class.':edita_Kardex');
        $group->post('/crear',KardexAction::class.':crea_Kardex');
        $group->delete('/cambiarestado/{id_kardex}',KardexAction::class.':cambiaestado_Kardex');
        $group->get('/listar',KardexAction::class.':lista_Kardex');
        $group->patch('/modificar/{id_kardex}',KardexAction::class.':modifica_Kardex');
        $group->get('/obtenerprods/{id_almacen}',KardexAction::class.':obtiene_prods_Kardex');
    });
};
