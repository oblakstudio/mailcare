<?php

namespace App;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    use Uuids;
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['email', 'display_name'];

    protected $hidden = ['local_part', 'domain'];

    public function emails()
    {
        return $this->hasMany(\App\Email::class);
    }
}
