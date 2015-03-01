<?php

namespace BigHubBrother;

use Exception;
use BigHubBrother\Utility;

class Notifier
{
    public function __construct($options)
    {
        $options = array_merge(['config' => null, 'data' => null], $options);

        if (empty($options['config']))
            throw new Exception("Missing configuration.");
        if (empty($options['data']))
            throw new Exception("Missing data.");

        $this->config = $options['config'];
        $this->push = $options['data'];
    }

    public function notifyPeople()
    {
        foreach ($this->config->getUsers() as $username => $info) {
            $repo = $this->config->getRepo($this->push['repository'], $username);
            if (!$repo)
                continue;

            $data = $this->_getNotifyData($username, $info);
            $email = $repo['e-mail'] ?: null;
            if (!empty($data) && !empty($email)) {
                $push = Utility::without($this->push, ['changes']);
                $this->notifyUser($email, Utility::merge($push, ['changes' => $data]));
            }
        }
    }

    public function notifyUser($email, $data)
    {
        var_dump($email, $data);
    }

    protected function _getNotifyData($username, $info)
    {

        $changesToNotify = [];

        foreach ($this->push['changes'] as $committer => $files) {
            if (!$this->config->isCommitterWatchedByUserInRepo($committer, $username, $this->push['repository'])) {
                continue;
            }

            if (!$this->config->areFilesWatchedByUserInRepo($files, $username, $this->push['repository'])) {
                continue;
            }

            $changesToNotify[$committer] = $files;
        }

        return $changesToNotify;
    }
}