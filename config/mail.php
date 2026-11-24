<?php

$host = urlencode($_ENV['SMTP_HOST']);
$port = $_ENV['SMTP_PORT'];

$user = urlencode($_ENV['SMTP_USER']);
$pass = urlencode($_ENV['SMTP_PASS']);

return [
    "dsn" => "smtp://$user:$pass@$host:$port",
    "from_email" => $_ENV['SMTP_FROM'],
    "from_name" => "PrjF1 Ana",
];
