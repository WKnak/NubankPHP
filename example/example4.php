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

$pixKeys = $nubank->getPixKeys();
?>

<html>
    <body>
        <h1>NubankPHP</h1>

        <h2>Suas Chaves Pix</h2>

        <table cellpadding="4" cellspacing="0" border="1">
            <tr>
                <th>Tipo Chave</th>                
                <th>Chave</th>
                <th>Chave Formatada</th>
                <th>ID</th>
            </tr>
            <?php foreach ($pixKeys['keys'] as $f) { ?>
                <tr>
                    <td><?= $f->kind ?></td>
                    <td><?= $f->value ?></td>
                    <td><?= $f->formattedValue ?></td>
                    <td><?= $f->id ?></td>
                </tr>
            <?php } ?>
        </table>
        <p>Account ID: <?= $pixKeys['account_id'] ?></p>

    </body>
</html>