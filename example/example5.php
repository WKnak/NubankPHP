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
    <style>
        th { text-align: left; }
        input, select { width: 100%; }
    </style>
    <body>
        <h1>NubankPHP</h1>

        <?php if (isset($_GET['msg'])) { ?>
            <p style="color: red"><?= $_GET['msg'] ?></p>
        <?php } ?>

        <h2>Criar uma solicitação de pagamento PIX</h2>

        <form action="example5_create.php" method="post">
            <input type="hidden" name="savingsAccountId" value="<?= $pixKeys['account_id'] ?>" />
            <table>
                <tr><th>Account ID:</th><td><small><?= $pixKeys['account_id'] ?></small></td></tr>
                <tr><th>Valor R$:</th><td><input name="amount" type="number" step='0.01' value='0.00' placeholder='0.00' /></td></tr>
                <tr><th>Chave Pix Destino:</th><td><select name="chavePix">
                            <?php foreach ($pixKeys['keys'] as $f) { ?>
                                <option value="<?= $f->value ?>"><?= $f->formattedValue ?></option>
                            <?php } ?></select></td></tr>
                <tr>
                    <th>Mensagem:</th><td><input name="message" type="text" placeholder="Obrigado por comprar conosco!" /></td>
                    <td><small style="color: #444">Caracteres livres (inclusive Emoji ❤). Consulte manual PIX sobre tamanho.</small></td>
                </tr>
                <tr>
                    <th>ID Transação:</th><td><input name="tx_id" type="text" maxlength="35" placeholder="TX0000000001" /></td>
                    <td><small style="color: #444">Máx 35 caracteres, a~z A~Z 0~9</small></td>
                </tr>
                <tr><td></td><td><button type="submit">Criar solicitação</button></td></tr>

            </table>

        </form>

    </body>
</html>