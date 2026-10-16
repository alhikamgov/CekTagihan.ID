<?php
return [
    'commission_percent' => (float) ($_ENV['PLATFORM_COMMISSION_PERCENT'] ?? 2),
    'split_rule_id'      => $_ENV['XENDIT_SPLIT_RULE_ID']      ?? '',
    'master_account_id'  => $_ENV['XENDIT_MASTER_ACCOUNT_ID']  ?? '',
];