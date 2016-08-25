<?php

namespace Smartmoney\Stellar;

class Helpers
{
    /**
     * Fetch account details from horizon API
     * @param string $account_id
     * @param string $horizon_host
     * @param string $horizon_port
     * @throws \Exception
     * @return \stdClass
     */
    public static function horizonAccountInfo($account_id, $horizon_host, $horizon_port)
    {
        if (empty($account_id)) {
            throw new \Exception('Empty Master account public key');
        }

        if (empty($horizon_host)) {
            throw new \Exception('Empty Horizon host');
        }

        if (empty($horizon_port)) {
            throw new \Exception('Empty Horizon port');
        }

        $account_id = trim(strtoupper($account_id));

        if (!\Smartmoney\Stellar\Account::isValidAccountId($account_id)){
            return null;
        }

        $url = 'http://' . $horizon_host . ':' . $horizon_port . '/accounts/' . $account_id;
        $json = @file_get_contents($url);
        if ($json === false) {
            return false;
        }

        $info = json_decode($json);
        if (empty($info)) {
            return false;
        }

        return $info;
    }

    /**
     * Fetch master account details from horizon server
     * @throws Exception
     */
    public static function masterAccountInfo($masterPubKey, $horizon_host, $horizon_port)
    {
        if (empty($masterPubKey)) {
            throw new \Exception('Empty Master account public key');
        }

        $master = self::horizonAccountInfo($masterPubKey, $horizon_host, $horizon_port);
        if (empty($master->id)) {
            throw new \Exception('Failed to get master account info');
        }

        return $master;
    }

    /**
     * Fetch transaction details from horizon server
     * @param string $tx_hash
     * @throws \Exception
     * @return \stdClass
     */
    public static function horizonTransactionInfo($tx_hash, $horizon_host, $horizon_port)
    {
        if (empty($horizon_host)) {
            throw new \Exception('Empty Horizon host in config');
        }

        if (empty($horizon_port)) {
            throw new \Exception('Empty Horizon port in config');
        }

        $tx_hash = trim(strtolower($tx_hash));
        if (!preg_match('/[0-9a-f]{64}/', $tx_hash)) {

            return null;
        }

        $url = 'http://' . $horizon_host . ':' . $horizon_port . '/transactions/' . $tx_hash;
        $json = @file_get_contents($url);
        if ($json === false) {

            return false;
        }

        $info = json_decode($json);
        if (empty($info)) {

            return false;
        }

        return $info;
    }

    /**
     * Fetch admins list from master account info
     */
    public static function getAdminsList($master, $admins_weight)
    {
        $admins = [];

        if (!empty($master->signers)) {
            foreach ($master->signers as $signer) {
                if ($signer->weight == $admins_weight) {
                    $admins[] = $signer->public_key;
                }
            }
        }

        return $admins;
    }
}