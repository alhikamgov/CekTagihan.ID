<?php
return [
    'provider' => $_ENV['WA_PROVIDER'] ?? 'fonnte',
    'token'    => $_ENV['WA_TOKEN']    ?? '',
    'sender'   => $_ENV['WA_SENDER']   ?? '',
];