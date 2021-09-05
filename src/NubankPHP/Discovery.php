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

class Discovery {

    const DISCOVERY_URL = "https://prod-s0-webapp-proxy.nubank.com.br/api/discovery";
    const DISCOVERY_APP_URL = "https://prod-s0-webapp-proxy.nubank.com.br/api/app/discovery";

    // TODO: transformar em singleton/static ?
    
    private $appURLs;
    private $generalURLs;
    
    public function __construct() {
        $this->getUrl("ok");        
    }

    public function getAppUrl($name) {

        if ($this->appURLs == []) {
            $raw = $this->curlGet(self::DISCOVERY_APP_URL);
            $this->appURLs = json_decode($raw);            
        }

        if (isset($this->appURLs->$name)) {
            return $this->appURLs->$name;
        }
        
        throw \Exception("Falhou ao obter URL das APIs do Nubank");
    }

    public function getUrl($name) {

        if ($this->generalURLs == []) {
            $raw = $this->curlGet(self::DISCOVERY_URL);
            $this->generalURLs = json_decode($raw);
        }

        if (isset($this->generalURLs->$name)) {
            return $this->generalURLs->$name;
        }
        return "http://127.0.0.1/discovery-failed";
    }

    private function curlGet($url) {

        $ch = curl_init($url);

        $headers = [
            'User-Agent: ' . \NubankPHP\NubankPHP::CLIENT_AGENT
        ];

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, false);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

}
