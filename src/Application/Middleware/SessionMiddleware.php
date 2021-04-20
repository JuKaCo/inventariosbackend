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
    /*
     * ----header----------------------------------------------------------
      {
      "alg": "RS256",
      "typ": "JWT",
      "kid": "4F4bwcM1OE_xwa8Rb5Oc9W5m8TdJTlGI-7Ztpcj0Y1I"
      }
     * ----data-------------------------------------------------------------
      {
        "exp": 1617981575,
        "iat": 1617981275,
        "jti": "7b354291-cc22-41c9-b03c-043db1ec34a9",
        "iss": "http://192.168.1.232:8080/auth/realms/web",
        "aud": "account",
     * ----ID_USUARIO-------------------------------------------------------
     * $token=getenv('TOKEN_DATOS');
     * $token=json_decode($token,true);
     * $id_usuario=$token['sub'];
     * 
        "sub": "5e80d6ca-0455-4e2a-a1bf-eaaa543e901b",
        "typ": "Bearer",
        "azp": "angular-web",
        "session_state": "7e580021-d580-44b0-9c5a-8b120f413c9c",
        "acr": "1",
        "allowed-origins": [
            "*"
        ],
        "realm_access": {
     * ----ROLES_ACCESO-----------------------------------------------------
     * $token=getenv('TOKEN_DATOS');
     * $token=json_decode($token,true);
     * $roles=($token['realm_access'])['roles'];
     * 
            "roles": [
                "offline_access",
                "rol_uaf_ingreso",
                "uma_authorization",
                "rol_almacen"
            ]
        },
        "resource_access": {
            "account": {
            "roles": [
                "manage-account",
                "manage-account-links",
                "view-profile"
            ]
            }
        },
        "scope": "openid offline_access email profile",
        "email_verified": false,
     * ----DATOS_GENERALES-------------------------------------------------
      * $token=getenv('TOKEN_DATOS');
     * $token=json_decode($token,true);
     * $name=$token['name'];
     * $cargo_usuario=$token['cargo_usuario'];
     * $preferred_username=$token['preferred_username'];
     * 
        "name": "Roger Nav",
        "cargo_usuario": "Encargado de almacen",
        "preferred_username": "rnavia",
        "given_name": "Roger",
        "family_name": "Nav",
        "email": "ranvia@123.com"
      }
     */
    public function process(Request $request, RequestHandler $handler): Response {
        $serviceOpenIdKeycloak = new ServiceOpenIdKeycloak();


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
                    $payload = json_encode($payload);
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
                //@putenv TOKEN_DATOS
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
        }
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
