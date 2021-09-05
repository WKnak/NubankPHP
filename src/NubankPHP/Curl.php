<?php

/* 
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */


namespace NubankPHP;

class Curl {

    public static function postForRequestEmailCode($url, $params) {

        $ch = curl_init($url);

        $request_headers = ['Content-Type: application/json'];

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        $jsonParams = json_encode($params); //, JSON_HEX_QUOT);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonParams);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        $response_headers = [];

        // callback para processar cada um dos headers de resposta
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
                function($curl, $header) use (&$response_headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        });

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        /*
         * O retorno desta função neste momento deverá ser
         * 
         * {"error":"Unauthorized"}
         * 
         * Só interessa por enquanto os reponse_headers
         */
        $response = curl_exec($ch);

        curl_close($ch);

        $result = self::_parseEmailCodeResponse($response_headers);

        return $result;
    }

    public static function postForExchangeCerts($url, $params) {

        $ch = curl_init($url);

        $request_headers = ['Content-Type: application/json'];

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        $jsonParams = json_encode($params); //, JSON_HEX_QUOT);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonParams);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        // echo "\n---------------------------\n\n$response\n\n--------------------------------";

        $response = json_decode($response);

        curl_close($ch);

        return $response;
    }

    private static function _parseEmailCodeResponse($headers) {

        if (!isset($headers['www-authenticate']))
            return false;

        $v = $headers['www-authenticate'][0];

        $v = trim(str_replace("device-authorization", "", $v));
        $key_values = explode(",", $v);

        $a = [];
        foreach ($key_values as $kv) {
            $kv = explode("=", $kv);
            if (count($kv) == 2) {
                $a[trim($kv[0])] = str_replace('"', "", $kv[1]);
            };
        }

        if (isset($a['sent-to']) && isset($a['encrypted-code'])) {
            return [
                "sent-to" => $a['sent-to'],
                'encrypted-code' => $a['encrypted-code']
            ];
        } else {
            return false;
        }
    }

    public static function postUsingCert($url, $payload, $certPath) {
        return self::postUsingCertAndToken($url, $payload, $certPath, false);
    }

    public static function postUsingCertAndToken($url, $payload, $certPath, $sessionAccessToken = false) {

        $CERT_PASSWORD = "";

        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'X-Correlation-Id: WEB-APP.pewW9',
            'User-Agent: ' . \NubankPHP\NubankPHP::CLIENT_AGENT
        ];

        if ($sessionAccessToken) {
            array_push($headers, 'Authorization: Bearer ' . $sessionAccessToken);
        }

        curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
        curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $CERT_PASSWORD);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

//        echo "<h1>Response</h1><hr /><pre>";
//        var_dump($response);
//        echo "</pre>";

        return $response;
    }

}
