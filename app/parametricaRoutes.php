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

return function (App $app) {
    $app->group('/api/v1/param', function (Group $group) {
        $group->get('/gen/{cod_grupo}', ParametricaAction::class.':genParam');
        $group->get('/gen/{cod_grupo}/{id_padre}', ParametricaAction::class.':genParamPadre');
        $group->get('/biometrico/terminal', ParametricaAction::class.':genParamBiometrico');
    });
};
