<?php

namespace BigHubBrother;

class Notifier
{
    public function __construct($options)
    {
        $options = array_merge(['config' => null, 'data' => null], $options);

        if (empty($options['config']))
            throw new \Exception("Missing configuration.");
        if (empty($options['data']))
            throw new \Exception("Missing data.");

        $this->config = $options['config'];
        $this->data = $options['data'];
    }

    public function sendMails()
    {
        foreach ($this->config['users'] as $username => $info) {
            if (!empty($info['e-mail']))
                $mustNotify = $this->_checkDataForUser($username);
            else
                echo "Missing e-mail field for $username";
        }
    }

    protected function _checkDataForUser($username)
    {
        if (!$this->config->isRepoWatchedByUser($this->data['repository'], $username))
            return false;

        foreach ($this->data['changes'] as $committer => $files) {
            if (!$this->config->isCommitterWatchedByUserAndRepo($committer, $username, $this->data['repository']))
                continue;

            //check for files
        }
    }
}