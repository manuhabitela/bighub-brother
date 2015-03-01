<?php

namespace BigHubBrother;

use BigHubBrother\GitHubWebhookRequest;
use BigHubBrother\Config;
use BigHubBrother\Notifier;
use Exception;

class BigHubBrother
{

    public function __construct($options = array())
    {
        $options = array_merge([
            'config' => __DIR__ . '/../config/config.json',
            'devExample' => __DIR__ . '/../examples/push.json',
            'data' => null,
            'secret_env' => 'WEBHOOK_SECRET'
        ], $options);

        header('Content-Type: text/plain');
        try {
            $push = new GitHubWebhookRequest(
                getenv('APPLICATION_ENV') == "production" ?
                    [ 'secret' => getenv($options['secret_env']) ] :
                    [ 'data' => file_get_contents($options['devExample']) ]);
        } catch (Exception $e) {
            echo "Webhook error: " . $e->getMessage();
        }

        try {
            $config = new Config($options['config']);
        } catch (Exception $e) {
            echo "Config error: " . $e->getMessage();
        }

        try {
            $notifier = new Notifier([
                'config' => $config,
                'data' => $push->getData()
            ]);
        } catch (Exception $e) {
            echo "Notifier error: " . $e->getMessage();
        }

        $log = $notifier->notifyPeople();
        echo $log;
    }
}