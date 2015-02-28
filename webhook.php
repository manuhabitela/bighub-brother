<?php
require 'vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
  \Dotenv::load(__DIR__);
}
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') !== false ? getenv('WEBHOOK_SECRET') : null);

try {
    $config = new Noodlehaus\Config('config/config.json');
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $payload = new BigHubBrother\GitHubPayload(
        APPLICATION_ENV == "production" ?
            [ 'secret' => WEBHOOK_SECRET ] :
            [ 'data' => file_get_contents(__DIR__ . '/examples/push.json') ]);
    $data = $payload->getData();
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($data);