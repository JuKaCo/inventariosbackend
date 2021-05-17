<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Firebase\JWT\JWT;

class JWTdata {
    
    private $request;

    public function __construct($request) {
        $this->request=$request;
    }
    public  function getToken(){
        $Autorization = $this->request->getHeaders();
        if (isset($Autorization['Authorization'])) {
            $Autorization = $Autorization['Authorization'];
            $Autorization = $Autorization[0];
            $Autorization = explode(" ", $Autorization);
            $Autorization = $Autorization[1];
            $key = $_ENV['KEYCLOAK_PK_PUBLIC'];
            $publicKey = <<<EOD
                            -----BEGIN PUBLIC KEY-----
                            {$key}
                            -----END PUBLIC KEY-----
                            EOD;
            $jwt = new JWT;
            $jwt::$leeway = 256;
            try {
                $decoded = JWT::decode($Autorization, $publicKey, array('RS256'));
            } catch (\Exception $exc) {
                return array('success' => false);
            }
            return array('success' => true, 'data' => $decoded);
        }else{
            return array('success' => false);
        }
        
    }

}
