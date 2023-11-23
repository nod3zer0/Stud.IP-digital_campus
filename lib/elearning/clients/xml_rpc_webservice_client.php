<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

require_once __DIR__ . '/webservice_client.php';

class XML_RPC_WebserviceClient extends WebserviceClient
{
    private $client;

    public function __construct($webservice_url)
    {
        $this->client = new xmlrpc_client($webservice_url);
        $this->client->debug = false;
        $this->client->return_type = 'phpvals';

    }

    public function &call($method_name, &$args)
    {
        $xmlrpc_args = [];
        foreach ($args as $arg)
        {
                $xmlrpc_args[] = php_xmlrpc_encode($arg);
        }

        $xmlrpc_return = $this->client->send(new xmlrpcmsg($method_name, $xmlrpc_args), 300);
        $xmlrpc_result = $xmlrpc_return->value();
        return $xmlrpc_result;
    }
}
