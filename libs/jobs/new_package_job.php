<?php
class NewPackageJob extends CakeJob {

    var $username;

    var $name;

    function __construct($username, $name) {
        $this->username = $username;
        $this->name = $name;
    }

    function perform() {
        $this->out(sprintf('Verifying package uniqueness %s/%s', $this->username, $this->name));
        $this->loadModel('Maintainer');
        $maintainer = $this->Maintainer->find('view', $this->username);

        $existing = $this->Maintainer->Package->find('list', array('conditions' => array(
                'Package.maintainer_id' => $maintainer['Maintainer']['id'],
                'Package.name' => $this->name
        )));
        if ($existing) return false;

        $this->loadModel('Github');
        $this->out('Retrieving repository');
        $repo = $this->Github->find('reposShowSingle', array(
            'username' => $this->username,
            'repo' => $this->name
        ));

        $this->out('Verifying that package is not a fork');
        if ($repo['Repository']['fork']) return false;

        $this->out('Detecting homepage');
        $homepage = $this->getHomepage($repo);

        $this->out('Detecting number of issues');
        $issues = $this->getIssues($repo);

        $this->out('Detecting total number of contributors');
        $contributors = $this->getContributors($repo);

        $this->out('Detecting number of collaborators');
        $collaborators = $this->getCollaborators($repo);

        $this->out('Saving package');
        $this->Maintainer->Package->save(array('Package' => array(
            'maintainer_id' => $maintainer['Maintainer']['id'],
            'name' => $this->name,
            'repository_url' => "git://github.com/{$repo['Repository']['owner']}/{$repo['Repository']['name']}.git",
            'homepage' => $homepage,
            'description' => $repo['Repository']['description'],
            'contributors' => $contributors,
            'collaborators' => $collaborators,
            'forks' => $repo['Repository']['forks'],
            'watchers' => $repo['Repository']['watchers'],
            'open_issues' => $issues,
            'created_at' => substr(str_replace('T', ' ', $repo['Repository']['created_at']), 0, 19),
            'last_pushed_at' => substr(str_replace('T', ' ', $repo['Repository']['pushed_at']), 0, 19),
        )));
        $this->out('Package saved');
    }

    function getHomepage($repo) {
        $homepage = (string) $repo['Repository']['url'];
        if (!empty($repo['Repository']['homepage'])) {
            $homepage = $repo['Repository']['homepage'];
        }
        return $homepage;
    }

    function getIssues($repo) {
        $issues = 0;
        if ($repo['Repository']['has_issues']) {
            $issues = $repo['Repository']['open_issues'];
        }
        return $issues;
    }

    function getContributors($repo) {
        $contribs = 1;
        $contributors = $this->Github->find('reposShowContributors', array(
            'username' => $this->username,
            'repo' => $this->name
        ));

        if (!empty($contributors)) {
            $contribs = count($contributors);
        }
        return $contribs;
    }

    function getCollaborators($repo) {
        $collabs = 1;
        $collaborators = $this->Github->find('reposShowCollaborators', array(
            'username' => $this->username,
            'repo' => $this->name
        ));

        if (!empty($collaborators)) {
            $collabs = count($collaborators);
        }
        return $collabs;
    }

}