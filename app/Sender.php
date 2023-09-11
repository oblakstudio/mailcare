<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sender extends Model
{
    use Uuids;
    use HasFactory;
    
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['email', 'display_name'];
    protected $hidden = ['local_part', 'domain'];
}
