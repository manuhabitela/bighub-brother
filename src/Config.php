<?php

namespace BigHubBrother;

class Config
{
    public function __construct($filepath)
    {
        $this->config = json_decode(file_get_contents($filepath), true);
        $this->_parse();
    }

    public function toJSON()
    {
        return $this->config;
    }

    public function isRepoWatchedByUser($repo, $user)
    {
        $repos = $this->config['users'][$user]['repos'];
        return !empty($repos[$repo]);
    }

    // yeah.
    public function isCommitterWatchedByUserAndRepo($committer, $user, $repo)
    {
        if ($committer === $user)
            return false;

        $repo = $this->config['users'][$user]['repos'][$repo] ?: null;
        if (!$repo)
            return false;

        if (!empty($repo['except_committers']) && in_array($committer, $repo['except_committers']))
            return false;

        if (!empty($repo['only_committers']) && !in_array($committer, $repo['only_committers']))
            return false;

        return true;
    }

    protected function _parse()
    {
        $conf = $this->config;
        foreach ($conf['users'] as $username => $info) {
            $conf['users'][$username]['repos'] = $this->_mergeUserWildcardRepos($info['repos']);
        }
        $this->config = $conf;
    }

    protected function _mergeUserWildcardRepos($repos)
    {
        $newRepos = [];

        $wildcard = !empty($repos["*"]) ? $repos["*"] : [];
        $noMergeRepos = !empty($wildcard['except_repos']) ? $wildcard['except_repos'] : [];
        unset($wildcard['except_repos']);

        foreach ($repos as $name => $info) {
            if ($name === "*")
                continue;
            $newRepos[$name] = (!in_array($name, $noMergeRepos)) ?
                array_merge_recursive($wildcard, $info) :
                $info;
        }

        return $newRepos;
    }
}