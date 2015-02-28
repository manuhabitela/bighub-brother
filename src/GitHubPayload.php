<?php

namespace BigHubBrother;

class GitHubPayload
{

    protected $data;

    public function __construct($options)
    {
        $options = array_merge(['secret' => null, 'data' => null], $options);

        $this->setData( !empty($options['data']) ? $options['data'] : file_get_contents('php://input') );

        if (!empty($options['secret']) && !$this->_validateSignature($this->getData(), $secret)) {
            throw new \Exception("GitHub signature doesn't match secret key");
        }
    }

    public function setData($json)
    {
        $this->data = is_string($json) ? json_decode($json) : $json;
    }

    public function getData()
    {
        return $this->data;
    }

    protected function _validateSignature($payload, $secret)
    {
        if (empty($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
            throw new \Exception('Missing X-Hub-Signature header.');
        }

        $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        return 'sha1=' . hash_hmac('sha1', $payload, $secret, false) === $signature;
    }
}
