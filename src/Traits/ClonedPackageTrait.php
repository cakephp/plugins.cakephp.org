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

    public function cloneDir()
    {
        return sprintf(
            '%srepos/%s/%s',
            TMP,
            $this->maintainer->username,
            $this->name
        );
    }

    public function zipballUrl()
    {
        return sprintf(
            'https://api.github.com/repos/%s/%s/zipball/master',
            $this->maintainer->username,
            $this->name
        );
    }

    public function zipballPath()
    {
        return sprintf(
            '/tmp/%s-%s.zip',
            $this->maintainer->username,
            $this->name
        );
    }
}
