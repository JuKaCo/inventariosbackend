<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Action\MenuAction;

return function (App $app) {
    $app->group('/rrhh', function (Group $group) {
        $group->get('/biometrico', MenuAction::class.':menu');
    });
};
