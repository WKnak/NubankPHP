<?php
/*
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

if (sizeof($_POST) == 0) {
    // precisa enviar dados do POST
    header("location: example5.php?msg=precisa enviar dados do POST");
    die();
}

$amount = floatval($_POST['amount']);
if (!$amount | $amount <= 0.0) {
    header("location: example5.php?msg=valor inválido");
    die();
}

$tx_id = $_POST['tx_id'];

if ($tx_id) {
    if (strlen($tx_id) > 35) {
        header("location: example5.php?msg=tx_id inválido, max 35 chars.");
        die();
    }
    $tx_id_verified = preg_replace("/[^a-zA-Z0-9]/", "", $tx_id);

    if ($tx_id_verified !== $tx_id) {
        header("location: example5.php?msg=tx_id inválido, char não permitido.");
        die();
    }
}

$valor = $amount;
$savingsAccountId = $_POST["savingsAccountId"];
$codigoPedido = $tx_id_verified;
$chavePix = $_POST["chavePix"];
$message = $_POST["message"];

include ('../src/autoload.php');
include ('../config/config.php');

$refreshToken = file_get_contents(REFRESH_TOKEN_FILE);

$nubank = new NubankPHP\NubankPHP($refreshToken, CERTIFICATE_FILENAME);

$loginSuccess = $nubank->autenticarComTokenECertificado();

if (!$loginSuccess) {
    die("can't login");
}

$pix = $nubank->createPixMoneyRequest($savingsAccountId, $valor, $chavePix, $codigoPedido, $message);

if (!$pix) {
    die("erro ao criar solicitação PIX");
}
?>

<html>
    <body>
        <h1>NubankPHP</h1>

        <h2>Requisição Pix Criada com Sucesso</h2>

        <table cellpadding="4" cellspacing="0" border="1">
            <tr><th>id</th><td><?= $pix->id ?></td></tr>
            <tr><th>amount</th><td>R$ <?= number_format($pix->amount, 2, ",", ".") ?></td></tr>
            <tr><th>message</th><td><?= isset($pix->message) ? $pix->message : "" ?></td></tr>
            <tr><th>pixAlias</th><td><?= $pix->pixAlias ?></td></tr>
            <tr><th>transactionId</th><td><?= $pix->transactionId ?></td></tr>
            <tr><th>url</th><td><a href="<?= $pix->url ?>" target="_blank"><?= $pix->url ?></a></td></tr>
            <tr><th>brcode</th><td><?= $pix->brcode ?></td></tr>        
        </table>
    </body>
</html>