<?php

/*
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

/**
 * Necessita criar a pasta private fora do seu repositório (ou outro local).
 * Também pode ser necessário permitir que o PHP acesse arquivos pelo "open_basedir" nesta pasta
 */
define("AUTH_BASE", __DIR__ . "\\..\\..\\private\\cert\\");

define("CERTIFICATE_FILENAME", AUTH_BASE . 'NubankPHP-customer.p12');
define("ACCESS_TOKEN_FILE", AUTH_BASE . 'NubankPHP-client.access_token');
define("REFRESH_TOKEN_FILE", AUTH_BASE . 'NubankPHP-client.refresh_token');

/**
 * Ajustar o caminho para seu openssl.cnf conforme ambiente (Linux/Windows)
 */
define("OPENSSL_CNF_LOCATION", __DIR__ . "\\..\\..\\openssl-1.1.1k\\openssl.cnf");

if (!is_dir(AUTH_BASE)) {

    throw new \Exception("Falha: o caminho dos certificados não existe. Verifique sua configuração: \"" . AUTH_BASE . "\".");
}

if (!file_exists(OPENSSL_CNF_LOCATION)) {
    throw new \Exception("Falha: o caminho do openssl.cnf é inválido. Verifique sua configuração.");
}

