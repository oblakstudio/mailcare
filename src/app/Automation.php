<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'sender',
        'inbox',
        'subject',
        'has_attachments',
        'action_url',
        'action_email',
        'action_secret_token',
        'action_delete_email',
        'post_raw',
        'emails_received',
    ];

    protected $casts = [
        'emails_received' => 'int',
        'has_attachments' => 'boolean',
        'action_delete_email' => 'boolean',
        'post_raw' => 'boolean',
        'in_error' => 'boolean',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}
