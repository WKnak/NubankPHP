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

// echo ($refreshToken); die().

$nubank = new NubankPHP\NubankPHP($refreshToken, CERTIFICATE_FILENAME);

$loginSuccess = $nubank->autenticarComTokenECertificado();

if (!$loginSuccess) {
    die("can't login");
}

$dashboardFeed = $nubank->getAccountFeed();
$max = isset($_GET['max']) ? $_GET['max'] : 10;
$transferInOnly = isset($_GET['TransferIn']);
$cnt = 0;
?>

<html>
    <body>
        <h1>NubankPHP</h1>

        <h2>Últimas <?= $max ?> registros do Feed da Conta 
            <small>
                [<a href="example3.php?max=20">20</a>] 
                [<a href="example3.php?max=50">50</a>]
                [<a href="example3.php?TransferIn=true">Transfer In</a>]
            </small></h2>
        <table cellpadding="4" cellspacing="0" border="1">
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>PIX</th>
                <th>Detalhe</th>
            </tr>
            <?php
            foreach ($dashboardFeed as $f) {
                
                if($transferInOnly && $f->__typename != "TransferInEvent") continue;

                $linkDetails = false;
                if ($f->pix) {
                    $pixType = strpos($f->title, "enviada") ? "TRANSFER_OUT" : "TRANSFER_IN";
                    $linkDetails = '<a href="example3_detail.php?type=' . $pixType . '&id=' . $f->id . '">PIX</a>';
                } elseif ($f->__typename == "TransferInEvent") {
                    $linkDetails = '<a href="example3_detail.php?type=TRANSFER_IN&id=' . $f->id . '">Detalhes</a>';
                } elseif ($f->__typename == "TransferOutEvent") {
                    $linkDetails = '<a href="example3_detail.php?type=TRANSFER_OUT&id=' . $f->id . '">Detalhes</a>';
                }
                ?>
                <tr><td><?= $f->id ?></td>
                    <td><?= date("d/m/Y", $f->postDate) ?></td>
                    <td><?= $f->title ?></td>
                    <td><?= $f->__typename ?></td>
                    <td align="right"><?= number_format($f->amount, 2, ",", ".") ?></td>
                    <td><?= $linkDetails ? $linkDetails : "" ?></td>
                    <td><?= $f->detail ?></td>                    
                </tr>
                <?php
                $cnt++;
                if ($cnt == $max)
                    break;
            }
            ?>
        </table>
        <p><b>Importante:</b> Sempre são trazidos todos os registros.</p>
        <p><b>Pix:</b> o tipo de evento GenericFeedEvent é considerado Pix.</p>
        <p><b>TransferInEvent e TransferOutEvent:</b> Podem ser Pix, TED e transferência entre contas Nubank.</p>
    </body>
</html>