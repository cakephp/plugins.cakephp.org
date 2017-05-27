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
}
