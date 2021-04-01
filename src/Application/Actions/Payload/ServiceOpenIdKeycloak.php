<?php
namespace App\Application\Actions\Payload;
class ServiceOpenIdKeycloak {
    public function verifDatosUserKeycloak($beared) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["KEYCLOAK_SERVER"] .$_ENV['KEYCLOAK_REALM']."/protocol/openid-connect/userinfo",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$beared
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info["http_code"] == 200) {
            $res=array('success'=>true, 'data'=>json_decode($response, true));
            return $res;
        } else {
            $respuesta = array(
                'success' => false
            );
            return $respuesta;
        }
    }
}