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
use App\Infrastructure\Persistence\DataRutaRepository;

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
     * $JWT = new \App\Application\Middleware\JWTdata($request);
     * $token = $JWT->getToken();
     * if (!$token['success']) {
     * return $this->respondWithData(array(), 'Datos de token invalidos', 403);
     * }
     * $token = $token['data'];
     * $id_usuario=$token->sub;
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
     * $JWT = new \App\Application\Middleware\JWTdata($request);
     * $token = $JWT->getToken();
     * if (!$token['success']) {
     * return $this->respondWithData(array(), 'Datos de token invalidos', 403);
     * }
     * $token = $token['data'];
     * $roles = $token->realm_access->roles;
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
     * $JWT = new \App\Application\Middleware\JWTdata($request);
     * $token = $JWT->getToken();
     * if (!$token['success']) {
     * return $this->respondWithData(array(), 'Datos de token invalidos', 403);
     * }
     * $token = $token['data'];
     * $name=$token->name;
     * $cargo_usuario=$token->cargo_usuario;
     * $preferred_username=$token->preferred_username;
     * 
        "scope": "openid offline_access email profile",
        "email_verified": false,
        "regional": "c23c5276-ac12-11eb-8dab-000c29c20449",
        "name": "Roger Nav",
        "privilegio": "total",
        "cargo_usuario": "Patron",
        "preferred_username": "rnavia",
        "given_name": "Roger",
        "family_name": "Nav",
        "email": "ranvia@123.com"
      }
     */
    public function process(Request $request, RequestHandler $handler): Response {
        $serviceOpenIdKeycloak = new ServiceOpenIdKeycloak();

        $method = $request->getMethod();
        if ($method != 'OPTIONS') {


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
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
                    }
                    $JWT = new \App\Application\Middleware\JWTdata($request);
                    $token = $JWT->getToken();
                    if (!$token['success']) {
                        return $this->respondWithData(array(), 'Datos de token invalidos', 403);
                    }
                    $token = $token['data'];
                    /********Validando Rutas******* */

                    // obteniendo los roles
                    $roles = $token->realm_access->roles;

                    //Importando el repositorio
                    $rutas = new DataRutaRepository();
                    //obteniendo el path del request
                    $uri = $request->getUri()->getPath();
                    // Eliminando la base del path
                    // Combirtiendo a array el path
                    if ($_ENV['PRODUCTION'] == 1) {
                        //$uri = "/api/v1/param/cliente";
                        $pathArray = explode('/', $uri);

                        $pathArray = array_slice($pathArray, 1);

                        $path = join('/', $pathArray);
                    } else {
                        $path = str_replace($_ENV['BASE_URI'], '', $uri);
                        //$path = 'api/v1/param/gen/5?filtro=6&aasd=asd';
                        $pathArray = explode('/', $path);
                    }
                    
                    // Obteniendo el metodo de la solicitud Ejem.: GET, POST, PUT, etc
                    $method = $request->getMethod();
                    // Funcion que busca en BD todos los accesos admitidos por el rol y el metodo
                    $listadoRutas = $rutas->getRuta($roles, $method);
                    // Comparando los paths
                    for ($i = 0; $i < count($listadoRutas); $i++) {
                        $pathDB = explode('/', $listadoRutas[$i]['label']);
                        $contador = 0;
                        if ($path == $listadoRutas[$i]['label']) {
                            //echo "POSITIVO -> 1 \n";
                            break;
                        } elseif (count($pathArray) == count($pathDB)) {
                            for ($j = 0; $j < count($pathArray); $j++) {
                                $buscaQuery = strpos($pathDB[$j], '?');
                                if ($buscaQuery) {
                                    $pathDB[$j] = substr($pathDB[$j], 0, intval($buscaQuery));
                                }
                                if ($pathArray[$j] == $pathDB[$j] || $pathDB[$j] == '%') {
                                    $contador++;
                                }
                            }
                        }
                        if ($contador == count($pathArray)) {
                            //echo "POSITIVO -> 2 \n";
                            break;
                        }
                        if (count($listadoRutas) == $i + 1) {
                            $response = new ResponsePsr7();
                            $payload = json_encode(array(
                                'statusCode' => 401,
                                'success' => false,
                                'data' => null,
                                'message' => 'No tiene permiso para acceder'
                            ));
                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
                        }
                    }
                    /*********Fin Validando Rutas**********/
                } catch (Exception $e) {
                    $existingContent = (string) $response->getBody();

                    $response = new ResponsePsr7();
                    $payload = json_encode(array(
                        'statusCode' => 403,
                        'success' => false,
                        'data' => null,
                        'message' => 'No tiene permiso para acceder'
                    ));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
                }
            } else {
                $response = new ResponsePsr7();
                $payload = json_encode(array(
                    'statusCode' => 403,
                    'success' => false,
                    'data' => null,
                    'message' => 'No tiene permiso para acceder'
                ));
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
