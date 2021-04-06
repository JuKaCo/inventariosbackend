<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponsePsr7;
use App\Application\Actions\Payload\serviceOpenIdKeycloak;
use Firebase\JWT\JWT;

class SessionMiddleware implements Middleware {

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response {
        /*$serviceOpenIdKeycloak = new ServiceOpenIdKeycloak();


        $Autorization = $request->getHeaders();
        if (isset($Autorization['Authorization'])) {
            try {
                $Autorization = ($Autorization['Authorization'])[0];
                $resOauth = $serviceOpenIdKeycloak->verifDatosUserKeycloak($Autorization);
                if (!$resOauth['success']) {
                    $response = new ResponsePsr7();

                    $existingContent = (string) $response->getBody();
                    $payload = json_encode(array(
                        'statusCode' => 403,
                        'success' => false,
                        'data' => null,
                        'message' => 'No tiene permiso para acceder'));
                    $payload = json_encode($resOauth);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
                }

                $Autorization = explode(" ", $Autorization);
                $Autorization = $Autorization[1];
                $key = $_ENV['KEYCLOAK_PK_PUBLIC'];
                $publicKey = <<<EOD
                            -----BEGIN PUBLIC KEY-----
                            {$key}
                            -----END PUBLIC KEY-----
                            EOD;

                $jwt = new \Firebase\JWT\JWT;
                $jwt::$leeway = 60;
                $decoded = JWT::decode($Autorization, $publicKey, array('RS256'));
                //*Setea datos del token en formato String JSON
                //@setenv TOKEN_DATOS
                putenv("TOKEN_DATOS=" . json_encode((array) $decoded));
            } catch (Exception $e) {
                $existingContent = (string) $response->getBody();

                $response = new ResponsePsr7();
                $payload = json_encode(array(
                    'statusCode' => 403,
                    'success' => false,
                    'data' => null,
                    'message' => 'No tiene permiso para acceder2'));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            }
        }*/
        
        return $handler->handle($request);

        /* $response = $handler->handle($request);
          $response->getBody()->write('AFTER');
          return $response; */

        /* $response = $handler->handle($request);
          $existingContent = (string) $response->getBody();

          $response = new ResponsePsr7();
          $response->getBody()->write('BEFORE');

          return $response; */
    }

}
