#!/usr/bin/env php
<?php
require_once __DIR__ . '/studip_cli_env.inc.php';

$folder = $GLOBALS['STUDIP_BASE_PATH'] . '/public/assets/images/icons';
$iterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS);
$iterator = new RecursiveIteratorIterator($iterator);
$regexp_iterator = new RegexIterator($iterator, '/\.svg$/', RecursiveRegexIterator::MATCH);

foreach ($regexp_iterator as $file) {
    $contents = file_get_contents($file);

    $xml = simplexml_load_string($contents);
    $attr = $xml->attributes();
    if ($attr->width && $attr->height) {
        continue;
    }
    $contents = str_replace('<svg ', '<svg width="16" height="16" ', $contents);
    file_put_contents($file, $contents);

    echo "Adjusted $file\n";
}
