<?php

namespace App;

use App\Traits\StorageForHuman;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use Uuids;
    use StorageForHuman;
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $appends = ['size_for_human'];

    public function hashHeaders($headers)
    {
        return md5(json_encode($headers));
    }

    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    public function getSizeForHumanAttribute()
    {
        return $this->humanFileSize($this->size_in_bytes);
    }
}
