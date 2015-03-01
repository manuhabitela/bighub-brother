<?php
require 'vendor/autoload.php';

if (file_exists(__DIR__ . '/.env'))
    \Dotenv::load(__DIR__);

$BigHubBrother = new BigHubBrother\BigHubBrother();
