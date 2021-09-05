<?php

/* 
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

namespace NubankPHP;

class NubankPHP {

    const CLIENT_AGENT = "NubankPHP 0.0.1";
    const URL_TOKEN = "https://prod-global-auth.nubank.com.br/api/token";

    private $authenticator;
    private $refreshToken = false;
    private $certPath = false;
    private $sessionAccessToken = false;  // token da sessão
    private $queryURL;
    private $refreshTokenExpiration;

    /**
     * 
     * @param type $refreshToken Passar quando estiver disponível (após autenticação manual)
     * @param type $certPath Passar quando estiver disponível (após autenticação manual)
     */
    public function __construct($refreshToken = false, $certPath = false) {
        $this->refreshToken = $refreshToken;
        $this->certPath = $certPath;
    }

    /**
     * Função geralmente chamada em rotina manual apenas quando
     * o certificado/token expirar. O Nubank vai enviar um código 
     * para o e-mail associado a sua conta, simulando o 
     * procedimento de acesso via Web/App.
     * 
     * @return boolean
     */
    public function solicitarCodigoPorEmail($cpf, $senhaApp, $idDeviceIdAleatorio, $openssl_conf_location) {

        $this->authenticator = new \NubankPHP\Authenticator($cpf, $senhaApp, $idDeviceIdAleatorio, $openssl_conf_location);
        return $this->authenticator->requestEmailCode();
    }

    /**
     * Realiza a confirmação do código recebido por e-mail, e em caso de 
     * sucesso, conclui a troca de certificados, salvando o certificado 
     * de customer assinado pelo Nubank no caminho informado.
     */
    public function confirmarCodigoEmail($encryptedCode, $codigo, $destino_certificado) {

        if (!$this->authenticator) {
            throw \Exception("A solicitação de código não foi executada nesta sessão.");
        }

        return $this->authenticator->exchangeCerts($encryptedCode, $codigo, $destino_certificado);
    }

    /**
     * Faz um segundo acesso manual, desta vez com um certificado de customer
     * devidamente registrado (nos passos anteriores). Com isso, o Nubank vai
     * retornar Tokens de acesso que poderão ser reutilizados em rotinas periódicas
     * sem necessidade de fornercer cpf/senha novamente.     * 
     */
    public function obterTokenDeAcesso($cpf, $senha, $cert_path) {

        $payload = [
            'grant_type' => 'password',
            'client_id' => 'legacy_client_id',
            'client_secret' => 'legacy_client_secret',
            'login' => $cpf,
            'password' => $senha
        ];

        $response = Curl::postUsingCertAndToken(self::URL_TOKEN, $payload, $cert_path);

        return $response;
    }

    /**
     * Autenticar somente com refresh token + certificado.
     * 
     * Se o refresh token ainda for válido, ele será renovado
     * automaticamente (server side). Caso contrário, precisará
     * gerar um novo token manualmente.
     * 
     * @return boolean
     * @throws \Exception
     */
    public function autenticarComTokenECertificado() {

        if (!$this->refreshToken) {
            throw new \Exception("refreshToken não foi passado no construtor");
        }

        $payload = [
            'grant_type' => 'refresh_token',
            'client_id' => 'legacy_client_id',
            'client_secret' => 'legacy_client_secret',
            'refresh_token' => $this->refreshToken
        ];

        $result = Curl::postUsingCert(self::URL_TOKEN, $payload, $this->certPath, $this->refreshToken);

        if ($result) {
            $json = json_decode($result);

            if ($json) {
                if (isset($json->refresh_before)) {
                    $this->refreshTokenExpiration = strtotime($json->refresh_before);
                }
                if (isset($json->access_token)) {
                    $this->sessionAccessToken = $json->access_token;

                    //var_dump( $json->_links); die();

                    $this->queryURL = $json->_links->ghostflame->href;

                    return TRUE;
                }
            } else {
                throw new \Exception("Falhou: $result");
            }
        }
        return FALSE;
    }

    private function prepareQueryRequestBody($graphqlName, $variables = FALSE) {

        $query = $this->loadQueryFromFile($graphqlName);

        $r = [];
        $r["query"] = $query;

        if ($variables) {
            $r["variables"] = $variables;
        }

        ksort($r);

        return $r;
    }

    private function loadQueryFromFile($queryName) {

        $queryFile = __DIR__ . DIRECTORY_SEPARATOR . "queries" . DIRECTORY_SEPARATOR . $queryName . '.gql';
        return file_get_contents($queryFile);
    }

    public function getRefreshTokenExpiration() {
        return $this->refreshTokenExpiration;
    }

    private function _parseFeedItems($data) {

        foreach ($data as &$r) {
            if (!isset($r->amount)) {
                if ($r->__typename == "GenericFeedEvent") {
                    $posCifrao = strpos($r->detail, "R$");
                    $amount = trim(substr($r->detail, $posCifrao + 4));
                    $amount = str_replace(".", "", $amount);
                    $amount = str_replace(",", ".", $amount);
                    $amount = floatval($amount);
                } else {
                    $amount = 0.0; // não disponível
                }
                $r->amount = $amount;
            }
            $r->postDate = strtotime($r->postDate);

            $r->pix = ($r->__typename == "GenericFeedEvent");
        }

        return $data;
    }

    private function _keyName($title) {

        $title = mb_strtolower($title);

        // matriz de entrada
        $what = array('ä', 'ã', 'à', 'á', 'â', 'ê', 'ë', 'è', 'é', 'ï', 'ì', 'í', 'ö', 'õ', 'ò', 'ó', 'ô', 'ü', 'ù', 'ú', 'û', 'À', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ç', 'Ç', ' ', '-', '(', ')', ',', ';', ':', '|', '!', '"', '#', '$', '%', '&', '/', '=', '?', '~', '^', '>', '<', 'ª', 'º');

        // matriz de saída
        $by = array('a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'A', 'A', 'E', 'I', 'O', 'U', 'n', 'n', 'c', 'C', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_');

        // devolver a string
        return str_replace($what, $by, $title);
    }

    private function _parseIdTransacao($str) {
        $re = '/ID da transação: (\w+)/m';

        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

        // Print the entire match result
        if (is_array($matches) && count($matches) > 0) {
            if (is_array($matches[0])) {
                return $matches[0][1];
            }
        }
        return "";
    }

    private function _monthTranslate($date) {

        $what = array('JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ');
        $by = array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');

        return str_replace($what, $by, $date);
    }

    private function _parseDate($date_str) {

        $date = $this->_monthTranslate($date_str);

        $timestamp = \DateTime::createFromFormat('d M Y - H:i:s', $date)->getTimestamp();

        if (!$timestamp) {
            return $date_str;
        }

        return $timestamp;
    }

    private function _parseTransferDetail($transactionType, $data) {

        $r = [
            "_type" => $transactionType,
            "_screenType" => $data->screenType
        ];

        foreach ($data->screenPieces as $f) {

            // Existe mais de um ScreenPiece com esse __typename.
            // Será usado o titulo do header como chave dos itens.
            if ($f->__typename == "ReceiptTablePiece") {

                if (!isset($f->tableHeader) || (!isset($f->tableHeader->title))) {
                    $prefix = "transf";
                } else {
                    $prefix = $this->_keyName($f->tableHeader->title);
                    if ($prefix == "dados_gerais_do_pagamento") {
                        $prefix = "transf";
                    }
                }

                foreach ($f->tableItems as $t) {

                    $key = "{$prefix}_" . $this->_keyName($t->label);
                    $r[$key] = trim($t->value, " \t\n\r\0\x0B");
                }
            }

            if ($f->__typename == "ReceiptHeaderPiece") {
                $r["_title"] = trim($f->headerTitle);
                
                $r["data_str"] = trim($f->headerSubtitle);
                $r["data"] = $this->_parseDate(trim($f->headerSubtitle));
                $r["data_ptBR"] = date("d/m/Y H:i:s", $r["data"]);
            }

            if ($f->__typename == "ReceiptMessagePiece") {
                $r["mensagem"] = trim($f->messageContent);
            }
            if ($f->__typename == "ReceiptFooterPiece") {
                $r["transf_id_transacao"] = $this->_parseIdTransacao(trim($f->footerTitle));
            }
        }

        ksort($r);

        return $r;
    }

    // *******************************************************************
    // ROTINAS DE CONSULTA
    // *******************************************************************
    public function getAccountBalance() {

        $params = $this->prepareQueryRequestBody("account_balance");

        $result = Curl::postUsingCertAndToken($this->queryURL, $params, $this->certPath, $this->sessionAccessToken);

        if ($result) {
            $data = json_decode($result);
            if ($data) {
                return $data->data->viewer->savingsAccount->currentSavingsBalance->netAmount;
            }
        }
        return FALSE;
    }

    public function getAccountFeed() {

        $params = $this->prepareQueryRequestBody("account_feed");

        $result = Curl::postUsingCertAndToken($this->queryURL, $params, $this->certPath, $this->sessionAccessToken);

        if ($result) {
            $data = json_decode($result);
            if ($data) {
                $feed = $data->data->viewer->savingsAccount->feed;
                $feed = $this->_parseFeedItems($feed);
                return $feed;
            }
        }
        return FALSE;
    }

    /**
     * Obtém os detalhes de uma transferência a partir dos dados 
     * usados para montar a tela de recibo/detalhes
     */
    public function getTransferDetail($transactionId, $transactionType) {

        $payload = ["type" => $transactionType, "id" => $transactionId];
        $params = $this->prepareQueryRequestBody("generic_receipt_screen", $payload);

        $result = Curl::postUsingCertAndToken($this->queryURL, $params, $this->certPath, $this->sessionAccessToken);

        if ($result) {

            $data = json_decode($result);

            //print_r($data); die();
            //echo "<pre>$result</pre>"; die();

            if (isset($data->data->viewer->savingsAccount->getGenericReceiptScreen)) {
                return $this->_parseTransferDetail($transactionType, $data->data->viewer->savingsAccount->getGenericReceiptScreen);
            }
        }
        return ["_type" => $transactionType];
    }

    /**
     * Obtém informações do usuário autenticado
     */
    public function getViewer() {

        $payload = [];
        $params = $this->prepareQueryRequestBody("viewer", $payload);

        $result = Curl::postUsingCertAndToken($this->queryURL, $params, $this->certPath, $this->sessionAccessToken);

        if ($result) {
            $data = json_decode($result);
            return $data->data->viewer;
        }
        return FALSE;
    }

    public function getPixKeys() {

        $params = $this->prepareQueryRequestBody("get_pix_keys");

        $result = Curl::postUsingCertAndToken($this->queryURL, $params, $this->certPath, $this->sessionAccessToken);

        if ($result) {

            // echo "<pre>$result</pre>";

            $data = json_decode($result);

            $savings_acount = $data->data->viewer->savingsAccount;

            // var_dump($savings_acount);

            return [
                "keys" => $savings_acount->dict->keys,
                "account_id" => $savings_acount->id
            ];

            //if ($data) {
            //    return $data;
            //}
        }
        return FALSE;
    }

}
