<?php

namespace Smartmoney\Stellar;

class Auth
{

    private $session;
    private $allowed_type;
    private $token;
    private $config;

    private $request_body;
    private $signature;
    private $data;

    function __construct($session_key)
    {
        $this->config = \Phalcon\Di::getDefault()->get('config');
        $this->session = new \Phalcon\Session\Bag($session_key);
        $request = new \Phalcon\Http\Request();

        $this->signature = $request->getHeader('X-Auth');
        $this->request_body = file_get_contents('php://input');
        $this->data = json_decode($this->request_body, true);

    }

    public function getAuthSession()
    {
        return $this->session;
    }

    public function getSessionAuthToken()
    {

        if (!empty($this->session->auth_token)) {
            return $this->session->auth_token;
        }

    }

    public function setSessionAuthToken($auth_token)
    {
        $this->session->auth_token = $auth_token;
    }

    public function setAllowedType($type)
    {
        $this->allowed_type = $type;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getAuthData()
    {

        if ($this->checkSignature()) {

            $acc_id = \Smartmoney\Stellar\Account::encodeCheck('accountId', $this->data['publicKey']);

            if ($this->checkAccountType($acc_id)) {

                $this->session->auth_token = self::generateCSRFToken();

                $data = [
                    'acc_id' => $acc_id,
                    'auth_token' => $this->session->auth_token,
                ];

                return $data;

            }

        }

        return false;
    }

    private function checkSignature()
    {

        if (!empty($this->signature) && !empty($this->data) && !empty($this->request_body)) {

            if (!empty($this->data['publicKey']) && !empty($this->data['token'])) {

                // Check signature
                $is_signed = ed25519_sign_open($this->request_body, base64_decode($this->data['publicKey']), base64_decode($this->signature));

                return $is_signed;

            }
        }

        return false;
    }

    private function checkAccountType($acc_id)
    {
        if (\Smartmoney\Stellar\Account::isValidAccountId($acc_id) &&
            \Smartmoney\Stellar\Account::isAccountExist($acc_id, $this->config->horizon->host,
                $this->config->horizon->port)
        ) {
            //if set allowed type - check for match account type
            if (!is_null($this->allowed_type)) {
                if (\Smartmoney\Stellar\Account::getAccountType($acc_id, $this->config->horizon->host,
                        $this->config->horizon->port) == $this->allowed_type
                ) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public static function generateCSRFToken()
    {
        return uniqid('token_');
    }
}