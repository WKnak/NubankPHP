<?php

/*
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */
//
// ESTE SCRIPT DEVE SER RODADO EM UM TERMINAL POR LINHA DE COMANDO
//
// EXEMPLO:
//
//    php autenticar_parte2.php -a    
//

include ('../src/autoload.php');
include ('../config/config.php');

echo <<<EOF
--------------------------------------------------------    
   NubankPHP - github.com/wknak/NubankPHP
   
EOF;

$refreshToken = file_get_contents(REFRESH_TOKEN_FILE);

$nubank = new NubankPHP\NubankPHP($refreshToken, CERTIFICATE_FILENAME);

$loginSuccess = $nubank->autenticarComTokenECertificado();

if (!$loginSuccess) {
    echo "   Login falhou";
    echo "--------------------------------------------------------";
    die();
}

$viewer = $nubank->getViewer();
$balance = $nubank->getAccountBalance();

echo <<<EOF
   ID: $viewer->id
   Nome: $viewer->name
   CPF: $viewer->cpf
   E-mail: $viewer->email 
   Saldo: R$ $balance
        
   Sucesso!
--------------------------------------------------------

EOF;
