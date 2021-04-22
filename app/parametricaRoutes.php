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
use App\Application\Actions\Action\ParametricaAction;
use App\Application\Actions\Action\ProveedorAction;

return function (App $app) {
    $app->group('/api/v1/param', function (Group $group) {
        $group->get('/gen/{cod_grupo}', ParametricaAction::class.':genParam');
        $group->get('/gen/{cod_grupo}/{id_padre}', ParametricaAction::class.':genParamPadre');
        $group->get('/biometrico/terminal', ParametricaAction::class.':genParamBiometrico');
    });
    $app->group('/api/v1/proveedor', function(Group $group){
        $group->get('/obtener/{id_proveedor}',ProveedorAction::class.':obtiene_Proveedor');
        $group->put('/editar/{id_proveedor}',ProveedorAction::class.':edita_Proveedor');
        $group->post('/crear',ProveedorAction::class.':crea_Proveedor');
        $group->put('/cambiaestado/{id_proveedor}/{estado}',ProveedorAction::class.':cambiaestado_Proveedor');
        $group->get('/listar',ProveedorAction::class.':lista_Proveedor');
    });
    $app->group('/api/v1/liname', function (Group $group) {
        $group->get('/listar', LinameAction::class.':getListLiname');
        $group->post('/cargar/validar', LinameAction::class.':cargarValidUpload');
        $group->post('/cargar/consolidar', LinameAction::class.':cargarConsolida');
        $group->put('/cambia_estado/{estado}/{uuid}', LinameAction::class.':setInhabilitaHabilita');
        $group->get('/descargar/{uuid}', LinameAction::class.':getArchive');
    });
};
