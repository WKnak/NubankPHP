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
    die("can't login");
}

$transactionId = $_GET["id"];
$transactionType = $_GET["type"];

$transferDetails = $nubank->getTransferDetail($transactionId, $transactionType);

?>

<html>
    <body>
        <h1>NubankPHP</h1>

        <h2>Detalhes da Transferência</h2>

        <pre>
            <?php var_dump($transferDetails); ?>
        </pre>

    </body>
</html>