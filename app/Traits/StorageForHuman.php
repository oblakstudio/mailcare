<?php

namespace App\Traits;

trait StorageForHuman
{

    public function humanFileSize($bytes, $dec = 2)
    {
        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f", $bytes / pow(1000, $factor)) . @$size[$factor];
    }
}
