<?php

namespace BigHubBrother;

class GitHubWebhookRequest
{

    protected $rawData;
    protected $data;

    public function __construct($options)
    {
        $options = array_merge(['secret' => null, 'data' => null], $options);

        $this->setRawData( !empty($options['data']) ? $options['data'] : file_get_contents('php://input') );

        if (!empty($options['secret']) && !$this->_validateSignature($this->getData(), $secret)) {
            throw new \Exception("GitHub signature doesn't match secret key");
        }
    }

    public function setRawData($json)
    {
        $this->rawData = is_string($json) ? json_decode($json) : $json;
    }

    protected function parseData()
    {
        if (empty($this->rawData))
            return '';

        $branch = $this->_getBranch();
        $data = [
            'organization' => $this->_getOrg(),
            'repository'   => $this->_getRepo(),
            'compare_url'  => $this->_getDiffURL(),
            'branch'       => $branch,
            'changes'      => $this->_getChanges()
        ];

        return $data;
    }

    protected function _getChanges()
    {
        $commits = array_map(function($commit) {
            if (!$commit->distinct)
                return false;
            $data = [];
            $data['committer'] = $commit->committer->username;
            $data['files'] = array_merge($commit->added, $commit->removed, $commit->modified);
            return $data;
        }, $this->rawData->commits);
        $commits = array_filter($commits);

        $changes = [];
        foreach ($commits as $commit) {
            $changes[ $commit['committer'] ] = empty($changes[ $commit['committer'] ]) ?
                $commit['files'] :
                array_merge($changes[ $commit['committer'] ], $commit['files']);
        }

        return $changes;
    }

    protected function _getRepo()
    {
        return $this->rawData->repository->full_name;
    }

    protected function _getDiffURL()
    {
        return $this->rawData->compare;
    }

    protected function _getBranch()
    {
        return str_replace("refs/heads/", "", $this->rawData->ref);
    }

    protected function _getOrg()
    {
        return $this->rawData->organization->login;
    }

    public static function getUserURL($user)
    {
        return "https://github.com/$user";
    }

    public static function getOrgURL($org)
    {
        return self::getUserURL($org);
    }

    public static function getRepoURL($repo)
    {
        return "https://github.com/$repo";
    }

    public static function getBranchURL($branch, $repo)
    {
        return self::getRepoURL($repo) . "/tree/$branch";
    }

    public static function getFileURL($file, $branch, $repo)
    {
        $branchURL = self::getBranchURL($branch, $repo);
        return str_replace("/tree/", "/blob/", $branchURL) . "/" . $file;
    }

    public function getRawData()
    {
        return $this->rawData;
    }

    public function getData()
    {
        return $this->parseData();
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
