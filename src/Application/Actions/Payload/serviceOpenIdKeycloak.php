<?php

namespace App\Application\Actions\Payload;

class ServiceOpenIdKeycloak {

    public function verifDatosUserKeycloak($beared) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $_ENV["KEYCLOAK_SERVER"] . $_ENV['KEYCLOAK_REALM'] . "/protocol/openid-connect/userinfo",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: " . $beared
                ),
            ));
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            if ($info["http_code"] == 200) {
                $res = array('success' => true, 'data' => json_decode($response, true));
                return $res;
            } else {
                $respuesta = array(
                    'success' => false
                );
                return $respuesta;
            }
        } catch (Exception $e) {
             $respuesta = array(
                    'success' => false
                );
                return $respuesta;
        }
    }

    public function verifDatosUserKeycloakTokenRefresh($token) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $_ENV["KEYCLOAK_SERVER"] . $_ENV['KEYCLOAK_REALM'] . "/protocol/openid-connect/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=refresh_token&scope=openid%20profile%20email%20offline_access&refresh_token=" . $token . "&client_id=" . $_ENV['KEYCLOAK_CLIENT_ID'],
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "cache-control: no-cache",
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info["http_code"] == 200) {
            $res = array('success' => true, 'data' => json_decode($response, true));
            return $res;
        } else {
            $respuesta = array(
                'success' => false
            );
            return $respuesta;
        }
    }

}
