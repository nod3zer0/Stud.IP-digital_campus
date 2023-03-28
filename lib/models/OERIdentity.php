<?php

use phpseclib3\Crypt\RSA;

abstract class OERIdentity extends SimpleORMap
{
    /**
     * configures this class
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['registered_callbacks']['before_store'][] = "cbCreateKeysIfNecessary";
        parent::configure($config);
    }

    public function createSignature($text)
    {
        return RSA::loadPrivateKey($this['private_key'])->sign($text);
    }

    public function verifySignature($text, $signature)
    {
        return RSA::loadPublicKey($this['public_key'])->verify($text, $signature);
    }

    public function cbCreateKeysIfNecessary()
    {
        if (!$this['public_key']) {
            $this->createKeys();
        }
    }

    protected function createKeys()
    {
        $keypair = RSA::createKey(4096);
        $this['private_key'] = preg_replace("/\r/", "", $keypair['privatekey']);
        $this['public_key'] = preg_replace("/\r/", "", $keypair['publickey']);
    }
}
