<?php

namespace BigHubBrother;

use Exception;

class Config
{
    public function __construct($filepath)
    {
        $this->config = json_decode(file_get_contents($filepath), true);
        $this->_prepare();
        $this->_parse();
    }

    public function toJSON()
    {
        return $this->config;
    }

    public function getUsers()
    {
        return $this->config['users'];
    }

    public function getUser($user)
    {
        return $this->config['users'][$user];
    }

    public function getRepo($repo, $user)
    {
        $userConf = $this->getUser($user);
        $default = $userConf['default'];
        $blacklist = $userConf['exclude'];
        $whitelist = $userConf['only'];
        $specifics = $userConf['specifics'];

        if (!empty($specifics[$repo]))
            return $specifics[$repo];

        if (!empty($default) && (
            (!empty($blacklist) && !in_array($repo, $blacklist)) ||
            (!empty($whitelist) && in_array($repo, $whitelist))
        ))
            return $default;

        return null;
    }

    public function isRepoWatchedByUser($repo, $user)
    {
        return !!$this->getRepo($repo, $user);
    }

    public function isCommitterWatchedByUserInRepo($committer, $user, $repo)
    {
        if ($committer === $user)
            return false;

        $repo = $this->getRepo($repo, $user);
        if (!$repo)
            return false;

        if (!empty($repo['except_committers']) && in_array($committer, $repo['except_committers']))
            return false;

        if (!empty($repo['only_committers']) && !in_array($committer, $repo['only_committers']))
            return false;

        return true;
    }

    public function areFilesWatchedByUserInRepo($files, $user, $repo)
    {
        $repo = $this->getRepo($repo, $user);
        if (!$repo)
            return false;

        $watched = false;
        $watchedFiles = $repo['file_patterns'];
        foreach ($watchedFiles as $pattern) {
            foreach ($files as $file) {
                if (fnmatch($pattern, $file)) {
                    $watched = true;
                }
            }
        }
        return $watched;
    }

    protected function _prepare()
    {
        if (empty($this->config['users']))
            $this->config['users'] = [];

        foreach ($this->config['users'] as $name => &$info) {

            if (!empty($info['exclude']) && !empty($info['only'])) {
                throw new Exception("$name: can't have exclude and only at same time", 1);
            }

            foreach (['default', 'specifics', 'exclude', 'only'] as $key) {
                if (empty($info[$key]))
                    $info[$key] = [];
            }
        }
    }

    protected function _parse()
    {
        $conf = $this->config;
        foreach ($conf['users'] as &$info) {
            $info['specifics'] = $this->_mergeUserDefaultConfs($info);
        }

        $this->config = $conf;
    }

    protected function _mergeUserDefaultConfs($userConf)
    {
        if (empty($userConf['default']))
            return $userConf['specifics'];

        $default = $userConf['default'];
        $specifics = $userConf['specifics'];
        $except = $userConf['exclude'];
        $only = $userConf['only'];

        foreach ($specifics as $name => $info) {
            if ( !empty($except) && in_array($name, $except) )
                continue;

            elseif ( !empty($only) && !in_array($name, $only) )
                continue;

            else
                $specifics[$name] = Utility::merge($default, $info);
        }

        return $specifics;
    }
}