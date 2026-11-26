<?php

return [
    'dsn' => $_ENV['MAILER_DSN'],
    'from_email' => $_ENV['MAILER_EMAIL'],
    'from_name' => $_ENV['MAILER_NAME'],
];
