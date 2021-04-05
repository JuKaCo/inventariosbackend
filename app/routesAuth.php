<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->options('{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {

        $decoded= getenv("TOKEN_DATOS");
        $response->getBody()->write($decoded);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
