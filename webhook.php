<?php
require 'vendor/autoload.php';

if (file_exists(__DIR__ . '/.env'))
  \Dotenv::load(__DIR__);
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') !== false ? getenv('WEBHOOK_SECRET') : null);

header('Content-Type: text/plain');

try {
    $push = new BigHubBrother\GitHubWebhookRequest(
        APPLICATION_ENV == "production" ?
            [ 'secret' => WEBHOOK_SECRET ] :
            [ 'data' => file_get_contents(__DIR__ . '/examples/push.json') ]);
} catch (Exception $e) {
    echo "Webhook error: " . $e->getMessage();
}

try {
    $config = new BigHubBrother\Config(__DIR__ . '/config/config.json');
} catch (Exception $e) {
    echo "Config error: " . $e->getMessage();
}

try {
    $notifier = new BigHubBrother\Notifier([
        'config' => $config,
        'data' => $push->getData()
    ]);
} catch (Exception $e) {
    echo "Notifier error: " . $e->getMessage();
}

$log = $notifier->notifyPeople();
echo $log;
