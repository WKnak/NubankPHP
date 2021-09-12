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
        <p>Access Token expira em: <?= date("d/m/Y H:i:s", $nubank->getRefreshTokenExpiration()) ?></p>
        <p>Access Token:<br />
            <textarea style="width: 40em; height: 16em;" ><?= $nubank->getAccessToken() ?></textarea>
        </p>

        <p><small>O <b>Access Token</b> é obtido através do <b>Refresh Token</b>.<br />O access token tem validade de 7 dias. A API renova o access-token quando este tiver validade menor que 6 dias.</small></p>
        <p><small>Você pode utilizar o <b>Access Token</b> com o software Insonmia para realizar testes de requisição ao GraphQL (API Pública) do Nubank.</small></p>
    </body>
</html>