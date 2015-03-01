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
    $push = new BigHubBrother\GitHubWebhookRequest(
        APPLICATION_ENV == "production" ?
            [ 'secret' => WEBHOOK_SECRET ] :
            [ 'data' => file_get_contents(__DIR__ . '/examples/push.json') ]);
} catch (Exception $e) {
    echo $e->getMessage();
}

$data = $push->getData();

var_dump($data);