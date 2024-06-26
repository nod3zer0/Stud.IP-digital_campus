<?php

/**
 * @deprecated  This script exists for legacy purposes only and will be removed in a future release.
 */

declare(strict_types=1);

namespace SimpleSAML;

require_once('../../_include.php');

use SimpleSAML\Configuration;
use SimpleSAML\Module\saml\Controller;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

$config = Configuration::getInstance();
$controller = new Controller\WebBrowserSingleSignOn($config);
$request = HttpRequest::createFromGlobals();

$headers = $config->getOptionalArray('headers.security', Configuration::DEFAULT_SECURITY_HEADERS);

$response = $controller->singleSignOnService($request);
foreach ($headers as $header => $value) {
    // Some pages may have specific requirements that we must follow. Don't touch them.
    if (!$response->headers->has($header)) {
        $response->headers->set($header, $value);
    }
}
$response->send();
