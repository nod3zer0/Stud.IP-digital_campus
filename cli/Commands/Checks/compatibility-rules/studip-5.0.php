<?php
// "Rules"/definitions for critical changes in 5.0
return [
    // https://develop.studip.de/trac/ticket/11250
    'userMayAccessRange' => '<fg=yellow>Changed</> - Use <fg=yellow>isAccessibleToUser</> instead',
    'userMayEditRange' => '<fg=yellow>Changed</> - Use <fg=yellow>isEditableByUser</> instead',
    'userMayAdministerRange' => '<fg=red>Removed</>',

    // UTF8-Encode/Decode legacy functions
    'studip_utf8encode' => '<fg=red>Removed</> - Use utf8_encode().',
    'studip_utf8decode' => '<fg=red>Removed</> - Use utf8_decode().',

    // JSON encode/decode legacy functions
    'studip_json_decode' => '<fg=red>Deprecated</> - Use json_decode() and pay attention to the second parameter.',
    'studip_json_encode' => '<fg=red>Deprecated</> - Use json_encode().',

    // https://develop.studip.de/trac/ticket/10806
    'SemesterData' => '<fg=red>Removed</> - Use <fg=yellow>Semester model</> instead',

    // https://develop.studip.de/trac/ticket/10786
    'StatusgroupsModel' => '<fg=red>Removed</> - Use <fg=yellow>Statusgruppen model</> instead',

    // https://develop.studip.de/trac/ticket/10796
    'StudipNullCache' => '<fg=red>Removed</> - Use <fg=yellow>StudipMemoryCache</> instead',

    // https://develop.studip.de/trac/ticket/10838
    'getDeputies' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::findDeputies()</> instead',
    'getDeputyBosses' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::findDeputyBosses()</> instead',
    '/(?<!Deputy::)addDeputy/' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::addDeputy()</> instead',
    '/deleteDeputy(?=\()/' => '<fg=red>Removed</> - Use <fg=yellow>Deputy model</> instead',
    'deleteAllDeputies' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::deleteByRange_id</> instead',
    '/(?<!Deputy::)isDeputy/' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::isDeputy()</> instead',
    'setDeputyHomepageRights' => '<fg=red>Removed</> - Use <fg=yellow>Deputy model</> instead',
    'getValidDeputyPerms' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::getValidPerms()</> instead',
    'isDefaultDeputyActivated' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::isActivated()</> instead',
    'getMyDeputySeminarsQuery' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::getMySeminarsQuery()</> instead',
    'isDeputyEditAboutActivated' => '<fg=red>Removed</> - Use <fg=yellow>Deputy::isEditActivated()</> instead',

    // https://develop.studip.de/trac/ticket/10870
    'get_config' => '<fg=red>Deprecated</> - Use <fg=yellow>Config::get()</> instead.',

    // https://develop.studip.de/trac/ticket/10919
    'RESTAPI\\RouteMap' => '<fg=red>Deprecated</> - Use the <fg=yellow>JSONAPI</> instead.',

    // https://develop.studip.de/trac/ticket/10878
    'Leafo\\ScssPhp' => 'Library was replaced by <fg=yellow>scssphp/scssphp</>',
    'sfYamlParser'   => 'Library was replaced by <fg=yellow>symfony/yaml</>',
    'DocBlock::of'   => 'Library was replaced by <fg=yellow>gossi/docblock</>',

    'vendor/idna_convert' => 'Remove include/require. Will be autoloaded.',
    'vendor/php-htmldiff' => 'Remove include/require. Will be autoloaded.',
    'vendor/HTMLPurifier' => 'Remove include/require. Will be autoloaded.',
    'vendor/phplot'       => 'Remove include/require. Will be autoloaded.',
    'vendor/phpCAS'       => 'Remove include/require. Will be autoloaded.',
    'vendor/phpxmlrpc'    => 'Remove include/require. Will be autoloaded.',

    // https://develop.studip.de/trac/ticket/10964
    'periodicalPushData'                           => '<fg=red>Removed</> - Use <fg=yellow>STUDIP.JSUpdater.register()</> instead',
    '/UpdateInformtion::setInformation\(.+\..+\)/' => '<fg=red>Removed</> - Use <fg=yellow>STUDIP.JSUpdater.register()</> instead',
];
