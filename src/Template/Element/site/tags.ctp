<?php
use Cake\Collection\Collection;

if (empty($package->tags)) {
    return;
}

echo $this->Resource->tagCloud($package->tags);
