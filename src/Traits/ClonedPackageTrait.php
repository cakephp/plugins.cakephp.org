<?php
namespace App\Traits;

use Cake\Filesystem\Folder;

trait ClonedPackageTrait
{
    public function isCloned()
    {
        $path = $this->cloneDir();
        $createPath = false;
        $folder = new Folder($path, $createPath);

        return $folder->cd($path);
    }

    public function cloneBasePath()
    {
        return sprintf(
            '%srepos',
            TMP
        );
    }

    public function cloneMaintainerPath()
    {
        return sprintf(
            '%srepos/%s',
            TMP,
            $this->maintainer->username
        );
    }

    public function cloneDir()
    {
        return sprintf(
            '%srepos/%s/%s',
            TMP,
            $this->maintainer->username,
            $this->name
        );
    }

    public function cloneTreesUrl()
    {
        return sprintf(
            'https://api.github.com/repos/%s/%s/git/trees/master?recursive=1',
            $this->maintainer->username,
            $this->name
        );
    }

    public function cloneZipballUrl()
    {
        return sprintf(
            'https://api.github.com/repos/%s/%s/zipball/master',
            $this->maintainer->username,
            $this->name
        );
    }

    public function cloneZipballPath()
    {
        return sprintf(
            '/tmp/%s-%s.zip',
            $this->maintainer->username,
            $this->name
        );
    }
}
