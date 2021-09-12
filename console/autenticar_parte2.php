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

   2. Obtenção de um token de acesso por 7 dias
--------------------------------------------------------
EOF;

echo "\n\n";
$cpf = readline("Informe o CPF (somente numeros):");
echo "\n";
$senhaApp = readline("Informe a senha:");

$nubank = new \NubankPHP\NubankPHP();
$result = json_decode($nubank->obterTokenDeAcesso($cpf, $senhaApp, CERTIFICATE_FILENAME));

if (!$result || !isset($result->access_token)) {
    echo <<<EOF
  --------------------------------------------------------
  [ Falha ] Houve uma falha na autenticação !
  Revise as suas informações, e os requisitos/dependências 
  para rodar este código, em especial a integração com 
  OpenSSL/PHP/Apache, e recomece o processo.
  --------------------------------------------------------
    
EOF;

    die();
}

$accessToken = $result->access_token;
$refreshToken = $result->refresh_token;
$refreshBefore = date("d/m/Y H:i", strtotime($result->refresh_before));

file_put_contents(REFRESH_TOKEN_FILE, $refreshToken);

$arquivoExiste = file_exists(REFRESH_TOKEN_FILE);

if ($arquivoExiste) {
    echo <<<EOF
  ---------------------------------------------------------
  [ Sucesso! ]
  Um Refresh-Token foi obtido e salvo com sucesso no disco.
    
  Com o Refresh-Token será possível obter um Access-Token
  que é válido por 7 dias.
            
  Agora você já pode realizar acesso sem precisar informar
  cpf/senha em cada acesso, útil para scripts automatizados
  e mais seguro.
  ---------------------------------------------------------
            
EOF;
} else {
    echo <<<EOF
  ---------------------------------------------------------
  [Falha ao gravar tokens]
  Os tokens foram gerados, MAS não foi possível salvar
  no disco. Verifique as configurações e permissões dos
  de gravação.
  ---------------------------------------------------------
    
EOF;
}


    