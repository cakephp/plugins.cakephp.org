<?php
if (!class_exists('Folder')) App::import('Core', 'Folder');
class PackageFolder extends Folder {

    var $folderExceptions = array();

/**
 * Private helper function for findRecursive.
 *
 * @param string $pattern Pattern to match against
 * @param boolean $sort Whether results should be sorted.
 * @return array Files matching pattern
 * @access private
 */
    function _findRecursive($pattern, $sort = false) {
        list($dirs, $files) = $this->read($sort);
        $found = array();

        foreach ($files as $file) {
            if (preg_match('/^' . $pattern . '$/i', $file)) {
                $found[] = Folder::addPathElement($this->path, $file);
            }
        }
        $start = $this->path;

        foreach ($dirs as $dir) {
            if (in_array($dir, $this->folderExceptions)) continue;

            $this->cd(Folder::addPathElement($start, $dir));
            $found = array_merge($found, $this->findRecursive($pattern, $sort));
        }
        return $found;
    }

}