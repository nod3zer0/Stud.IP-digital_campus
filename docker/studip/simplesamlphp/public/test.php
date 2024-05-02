<?
require_once('/var/simplesamlphp/src/_autoload.php');
session_start();
header('Content-Type: text/plain');
echo "Some text\n";
flush();
$as = new \SimpleSAML\Auth\Simple('default-sp');
$as->requireAuth(['ReturnTo' => 'https://studip.ceskar.xyz/simplesaml/test.php']);
$attributes = $as->getAttributes();
print_r($attributes);
?>


hello
