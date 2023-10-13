<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * xmlrpc.php - XML-RPC Backend for Stud.IP web services
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

use PhpXmlRpc\Extras\XmlrpcSmartyTemplate;
use PhpXmlRpc\PhpXmlRpc;

require '../lib/bootstrap.php';
require '../lib/webservices/webservices_bootstrap.php';

// Bootstrap documenting server
class StudipDocumentingXmlRpcServer extends \PhpXmlRpc\Extras\SelfDocumentingServer
{
    public function checkAuth()
    {
        $rules = WebserviceAccessRule::findByApiKey($_SERVER['PHP_AUTH_PW']);
        if (count($rules) === 0) {
            header('WWW-Authenticate: Basic realm="Please enter valid api key as password"');
            header('HTTP/1.0 401 Unauthorized');
            die('Please enter valid api key as password');
        }
    }

    public function service($data = null, $return_payload = false, $doctype = '')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->checkAuth();
        } elseif(
            isset($_SERVER['CONTENT_TYPE'])
            && $_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded'
            && isset($_POST['methodCall'])
        ) {
            $this->checkAuth();
        }
        return parent::service($data, $return_payload, $doctype);
    }

    public function generateDocs($doctype='html', $lang='en', $editorpath='')
    {
        if ($doctype === 'html' && isset($_GET['methodName'])) {
            $_GET['methodName'] = preg_replace('/[^a-zA-Z0-9_.:\/]/', '', $_GET['methodName']);
        }

        $documentationGenerator = new StudipServerDocumentor(new XmlrpcSmartyTemplate(null));
        return $documentationGenerator->generateDocs($this, $doctype, $lang, $editorpath);
    }
}

class StudipServerDocumentor extends \PhpXmlRpc\Extras\ServerDocumentor
{
    public static function templates()
    {
        return array_merge(parent::templates(), [
            'docheader' => '<!DOCTYPE html>
<html lang="{$lang}">
<head>
<meta name="generator" content="' . PhpXmlRpc::$xmlrpcName . '" />
<link href="assets/stylesheets/webservices.css" type="text/css" rel="stylesheet" />
{$extras}
<title>{$title}</title>
</head>
<body>',
        ]);
    }
}


# create server
$dispatcher = new Studip_Ws_XmlrpcDispatcher($AVAILABLE_SERVICES);

$server = new StudipDocumentingXmlRpcServer($dispatcher->get_dispatch_map(), false);
$server->debug = false;

# start server
$server->service();
