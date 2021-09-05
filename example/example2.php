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
?>

<html>
    <body>
        <h1>NubankPHP</h1>
        <p>Refresh Token expira em: <?= date("d/m/Y H:i:s", $nubank->getRefreshTokenExpiration()) ?></p>
        <small>Obs: com o refresh Token, você obtém um Access Token. O refresh token é renovado por +7 dias a cada acesso (server side)</small>
    </body>
</html>