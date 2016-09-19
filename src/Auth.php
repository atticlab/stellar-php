<?php

namespace Smartmoney\Stellar;

class Auth
{

    private $request_body;
    private $signature;
    private $data;

    function __construct()
    {
        $this->signature    = !empty($_SERVER['HTTP_X_AUTH']) ? $_SERVER['HTTP_X_AUTH'] : null;
        $this->request_body = file_get_contents('php://input');
        $this->data         = json_decode($this->request_body, true);
    }

    public function getAuthData()
    {
        $acc_id = Account::encodeCheck('accountId', $this->data['publicKey']);

        return [
            'acc_id' => $acc_id,
            'auth_token' => self::generateCSRFToken(),
            'username' => $this->data['username']
        ];

    }

    public function checkSignature()
    {
        if (!empty($this->signature) && !empty($this->data) && !empty($this->request_body)) {

            if (!empty($this->data['publicKey']) && !empty($this->data['token'])) {

                // Check signature
                return ed25519_sign_open($this->request_body, base64_decode($this->data['publicKey']),
                    base64_decode($this->signature));

            }
        }

        return false;
    }

    public static function generateCSRFToken()
    {
        return uniqid('token_');
    }

    public static function getAuthPhrase()
    {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }

}