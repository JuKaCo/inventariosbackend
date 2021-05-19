<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\MenuAction;
use App\Application\Actions\Action\LinameAction;
use App\Application\Actions\Action\LinadimeAction;
use App\Application\Actions\Action\ParametricaAction;
use App\Application\Actions\Action\ProveedorAction;
use App\Application\Actions\Action\ClienteAction;

return function (App $app) {
    $app->group('/api/v1/param', function (Group $group) {
        $group->get('/gen/{cod_grupo}', ParametricaAction::class.':genParam');
        $group->get('/gen/{cod_grupo}/{id_padre}', ParametricaAction::class.':genParamPadre');
        
        $group->get('/biometrico/terminal', ParametricaAction::class.':genParamBiometrico');
        
        $group->get('/liname', ParametricaAction::class.':genParamLiname');
        $group->get('/linadime', ParametricaAction::class.':genParamLinadime');

        $group->get('/proveedor', ParametricaAction::class.':genParamProveedor');

        $group->get('/regional', ParametricaAction::class.':genParamRegional');

        $group->get('/programa', ParametricaAction::class.':genParamPrograma');

        $group->get('/producto', ParametricaAction::class.':genParamProducto');
        $group->get('/almacen', ParametricaAction::class.':genParamAlmacen');

        $group->get('/usuario', ParametricaAction::class.':genParamUsuario');

        $group->get('/compra', ParametricaAction::class.':genParamCompra');

        $group->get('/configuracion/{codigo}', ParametricaAction::class.':genParamConfiguracion');


    });
    //proveedor rutas 
    $app->group('/api/v1/proveedor', function(Group $group){
        $group->get('/obtener/{id_proveedor}',ProveedorAction::class.':obtiene_Proveedor');
        $group->put('/editar/{id_proveedor}',ProveedorAction::class.':edita_Proveedor');
        $group->post('/crear',ProveedorAction::class.':crea_Proveedor');
        $group->delete('/cambiarestado/{id_proveedor}',ProveedorAction::class.':cambiaestado_Proveedor');
        $group->get('/listar',ProveedorAction::class.':lista_Proveedor');
    });
    //cliente rutas
    $app->group('/api/v1/cliente', function(Group $group){
        $group->get('/obtener/{id_cliente}',ClienteAction::class.':obtiene_Cliente');
        $group->put('/editar/{id_cliente}',ClienteAction::class.':edita_Cliente');
        $group->post('/crear',ClienteAction::class.':crea_Cliente');
        $group->delete('/cambiarestado/{id_cliente}',ClienteAction::class.':cambiaestado_Cliente');
        $group->get('/listar',ClienteAction::class.':lista_Cliente');
    });
    //liname rutas
    $app->group('/api/v1/liname', function (Group $group) {
        $group->get('/listar', LinameAction::class.':getListLiname');
        $group->post('/cargar/validar', LinameAction::class.':cargarValidUpload');
        $group->post('/cargar/consolidar', LinameAction::class.':cargarConsolida');
        $group->put('/cambia_estado/{estado}/{uuid}', LinameAction::class.':setInhabilitaHabilita');
        $group->get('/descargar/{uuid}', LinameAction::class.':getArchive');
    });
    //linadime rutas
    $app->group('/api/v1/linadime', function (Group $group) {
        $group->get('/listar', LinadimeAction::class.':getListLinadime');
        $group->post('/cargar/validar', LinadimeAction::class.':cargarValidUpload');
        $group->post('/cargar/consolidar', LinadimeAction::class.':cargarConsolida');
        $group->put('/cambia_estado/{estado}/{uuid}', LinadimeAction::class.':setInhabilitaHabilita');
        $group->get('/descargar/{uuid}', LinadimeAction::class.':getArchive');
    });
};
