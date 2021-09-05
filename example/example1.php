<?php

/* 
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

include ('../src/autoload.php');
include ('../config/config.php');

$refreshToken = file_get_contents(REFRESH_TOKEN_FILE);

$nubank = new NubankPHP\NubankPHP($refreshToken, CERTIFICATE_FILENAME);

$loginSuccess = $nubank->autenticarComTokenECertificado();

if (!$loginSuccess) {
    die("login falhou.");
}

$balance = $nubank->getAccountBalance();

$viewer = $nubank->getViewer();

?>

<html>
    <body>
        <h1>NubankPHP</h1>
        <h2>Informação do usuário autenticado</h2>
        <p>ID: <b><?=$viewer->id?></b><br />
        Nome: <b><?=$viewer->name?></b><br />
        CPF: <b><?=$viewer->cpf?></b><br />
        E-mail: <b><?=$viewer->email?></b></p>        
        <h2>Conta Corrente</h2>
        <p>Saldo da conta: <b>R$ <?=number_format($balance, 2, ",", ".");?></b></p>
    </body>
</html>