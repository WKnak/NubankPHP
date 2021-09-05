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

class Certificate {

    private static function dump($Var) {
        echo "<pre>";
        var_dump($Var);
        echo "</pre>";
    }

    private static function check_errors() {
        echo "<pre>";
        $Count = 0;
        while (($e = openssl_error_string()) !== false) {
            echo $e . "<br>";
            $Count++;
        }
        if ($Count == 0)
            echo "No error";
        echo "</pre>";
    }

    public static function createSelfSigned() {

        $import_export_password = "";
        $destination_file = "D:\Projetos\TiNX\NubankAPI-php\openssl-1.1.1k\cert\nubank-php-client.p12";
        $openssl_cnf_location = "D:\Projetos\TiNX\NubankAPI-php\openssl-1.1.1k\openssl.cnf";

        $configs = array(
            "config" => $openssl_cnf_location,
            "digest_alg" => "sha1",
            "x509_extensions" => "v3_ca",
            "req_extensions" => "v3_req",
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "encrypt_key" => true,
            "encrypt_key_cipher" => OPENSSL_CIPHER_3DES
        );

        $info = array(
            "countryName" => "BR",
            "stateOrProvinceName" => "Rio Grande do Sul",
            "localityName" => "Santa Cruz do Sul",
            "organizationName" => "TiNX Tecnologia",
            "organizationalUnitName" => "github.com/wknak/NubankPHP",
            "commonName" => "NubankPHP",
            "emailAddress" => "contato@tinx.com.br"
        );

        echo "<hr />openssl_csr_new()<br />";
        $private_key = null;
        $unsigned_cert = openssl_csr_new($info, $private_key, $configs);
        self::check_errors();
        self::dump($private_key);
        self::dump($unsigned_cert);

        echo "<hr />openssl_csr_sign()<br />";
        $signed_cert = openssl_csr_sign($unsigned_cert, null, $private_key, 365, $configs);
        self::check_errors();
        self::dump($signed_cert);

        echo "<hr />openssl_pkcs12_export_to_file()<br />";
        openssl_pkcs12_export_to_file($signed_cert, 'D:\Projetos\TiNX\NubankAPI-php\openssl-1.1.1k\cert\nubank-php-client.p12', $private_key, $import_export_password);
        self::check_errors();
    }

}
