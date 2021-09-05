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
//    php autenticar_parte1.php -a    
//

include ('../src/autoload.php');
include ('../config/config.php');

echo <<<EOF
--------------------------------------------------------    
   NubankPHP - github.com/wknak/NubankPHP

   1. Autenticação inicial - gerar certificado de customer
--------------------------------------------------------
EOF;

if (!is_dir(AUTH_BASE)) {    
    $caminho = AUTH_BASE;
    echo <<<EOF
  --------------------------------------------------------
  [ Falha ] O caminho dos certificados não existe:
  
  "$caminho"
            
  Crie manualmente e repita o procedimento.
  --------------------------------------------------------
EOF;
    die();
}

echo "\n\n";
$cpf = readline("Informe o CPF (somente numeros):");
echo "\n";
$senhaApp = readline("Informe a senha:");

$idDeviceIdAleatorio = strtolower(substr(md5(date("d/m/Y H:i:s")), 1, 12));

$nubank = new \NubankPHP\NubankPHP();
$result = $nubank->solicitarCodigoPorEmail($cpf, $senhaApp, $idDeviceIdAleatorio, OPENSSL_CNF_LOCATION);

if (is_array($result)) {
    $email = $result['sent-to'];
    $encryptedCode = $result['encrypted-code'];
}

$falhaSolicitarCodigo = !$result || !$email || !$encryptedCode;

if ($falhaSolicitarCodigo) {
    echo <<<EOF
  --------------------------------------------------------
  [ Falha ] Houve uma falha na autenticação. !!
  Revise as suas informações, e os requisitos/dependências
  para rodar este código, em especial a integração com
  OpenSSL/PHP/Apache, e recomece o processo.
  --------------------------------------------------------
EOF;
    die();
}
echo <<<EOF
  --------------------------------------------------------
  [ Excelente!! ]\n\n
  O Nubank enviou um código para o e-mail

                  $email
            
  digite-o abaixo para concluir a autenticação e gerar
  um certificado que será utilizado para gerar um token
  que poderá ser reutilizado nos scripts e  requisições
  futuras:
  --------------------------------------------------------
EOF;

echo "\n\n";
$codigo = readline("Informe codigo recebido no e-mail:");

$result = $nubank->confirmarCodigoEmail($encryptedCode, $codigo, CERTIFICATE_FILENAME);

$falhaConfirmarCodigo = !$result;

if ($falhaConfirmarCodigo) {

    echo <<<EOF
  --------------------------------------------------------
  [ Falha ] Houve uma falha ao salvar certificado!
  Revise as suas informações, o código recebido e os 
  requisitos para rodar este código, em especial a
  configuração de OpenSSL/PHP/Apache.
  --------------------------------------------------------
EOF;
    die();
}

echo <<<EOF
  --------------------------------------------------------
  [ Show! ] O certificado foi salvo com sucesso, ele será
  usado a seguir para gerar um token de acesso. Com esse 
  token será possível realizar diversas requisições e 
  consultas sem precisar autenticar todas as vezes.

  Faça agora a parte 2 da autenticação:

      php autenticar_parte2.php -a   
  --------------------------------------------------------
EOF;

