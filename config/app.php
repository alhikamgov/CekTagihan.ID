<?php
return [
    'env'     => $_ENV['APP_ENV']     ?? 'local',
    'name'    => $_ENV['APP_NAME']    ?? 'CekTagihan',
    'tagline' => $_ENV['APP_TAGLINE'] ?? 'Bayar tagihan dari mana saja!',
    'url'     => $_ENV['APP_URL']     ?? 'http://cektagihan.test',
    'debug'   => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
];