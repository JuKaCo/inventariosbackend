<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Payload\serviceOpenIdKeycloak;
use Firebase\JWT\JWT;

return function (App $app) {

    $app->options('{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {


        $serviceOpenIdKeycloak = new ServiceOpenIdKeycloak();


        $resban = array('mensaje' => 'Hola desde backend');

        $Autorization = $request->getHeaders();
        if (isset($Autorization['Authorization'])) {
            $Autorization = ($Autorization['Authorization'])[0];
            $resOauth = $serviceOpenIdKeycloak->verifDatosUserKeycloak($Autorization);
            try {
                $Autorization = explode(" ", $Autorization);
                $Autorization = $Autorization[1];

                $key = $_ENV['KEYCLOAK_PK_PUBLIC'];
                $publicKey = <<<EOD
                            -----BEGIN PUBLIC KEY-----
                            {$key}
                            -----END PUBLIC KEY-----
                            EOD;
                $decoded = JWT::decode($Autorization, $publicKey, array('RS256'));
                $decoded_array = (array) $decoded;

                $payload = json_encode($decoded_array);
                
                $response->getBody()->write($payload);
            } catch (Exception $e) {
                echo 'Excepción capturada: ', $e->getMessage(), "\n";
                exit;
            }
        } else {
            $response->getBody()->write(array());
        }


        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
