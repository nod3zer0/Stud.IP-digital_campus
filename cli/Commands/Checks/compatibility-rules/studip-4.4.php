<?php
// "Rules"/definitions for critical changes in 4.4
return [
    'Token::is_valid' => 'Use <fg=yellow>Token::isValid($token, $user_id)</> instead.',
    'Token::generate' => 'Use <fg=yellow>Token::create($duration = 30, $user_id = null)</> instead.',
];
