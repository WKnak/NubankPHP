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

class Authenticator {

    private $login;
    private $password;
    private $device_id;
    private $encrypted_code;
    private $key1;
    private $key2;
    private $url_gen_certificate;
    private $discoveryUrls;
    private $openssl_conf_location;

    public function __construct(
            $login, $password, $device_id, $openssl_conf_location) {

        $this->login = $login;
        $this->password = $password;
        $this->device_id = $device_id;
        $this->encrypted_code = false;

        $this->openssl_conf_location = $openssl_conf_location;

        $this->discoveryUrls = new Discovery();
        $this->url_gen_certificate = $this->discoveryUrls->getAppUrl("gen_certificate");

        $this::generateLocalKeys();
    }

    private function generateLocalKeys() {

        $this->key1 = $this->_generateKey();
        $this->key2 = $this->_generateKey();
        if (!$this->key1 || !$this->key2) {
            throw \Exception("Key1 e Key2 não podem ser NULL. Verifique a configuração do OpenSSL.");
        }
    }

    public function requestEmailCode() {

        $payload = $this->_getPayload();

        $response = Curl::postForRequestEmailCode($this->url_gen_certificate, $payload);

        if (!$response || !isset($response['sent-to'])) {
            return false;
        }

        return $response;
    }

    public function exchangeCerts($encryptedCode, $receivedCode, $certPath) {

        $payload = $this->_getPayload();

        $payload['encrypted-code'] = $encryptedCode;
        $payload['code'] = $receivedCode;

        $response = Curl::postForExchangeCerts($this->url_gen_certificate, $payload);

        $cert1 = $response->certificate;
        $cert2 = $response->certificate_crypto;

        // $cert1_p12 = 
        $this->_genCert($this->key1, $cert1, $certPath);

        // não precisa salvar cert2 (vai sobrescrever)
        // $cert2_p12 = $this->_gen_cert($this->key2, $cert2);

        return $response;
    }

    private function _genCert($private_key, $cert, $filename) {

        if (openssl_pkcs12_export_to_file($cert, $filename, $private_key, "")) {
            return $filename;
        } else {
            return false;
        }
    }

    private function _getPayload() {

        return [
            'login' => $this->login,
            'password' => $this->password,
            'public_key' => $this->_getPublicKey($this->key1),
            'public_key_crypto' => $this->_getPublicKey($this->key2),
            'model' => "NubankPHP Client (" . $this->device_id . ")",
            'device_id' => $this->device_id
        ];
    }

    private function _generateKey() {

        $configs = array(
            "config" => $this->openssl_conf_location,
            "digest_alg" => "sha1",
            "x509_extensions" => "v3_ca",
            "req_extensions" => "v3_req",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "encrypt_key" => true,
            "encrypt_key_cipher" => OPENSSL_CIPHER_3DES
        );

        $private_key = openssl_pkey_new($configs);

        return $private_key;
    }

    private function _getPublicKey($private_key) {
        return openssl_pkey_get_details($private_key)['key'];
    }

}
