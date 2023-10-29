<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'created_at',
        'emails_received',
        'inboxes_created',
        'storage_used',
        'cumulative_storage_used',
        'emails_deleted',
    ];

    protected $casts = [
        'emails_received' => 'int',
        'inboxes_created' => 'int',
        'storage_used' => 'int',
        'cumulative_storage_used' => 'int',
        'emails_deleted' => 'int',
    ];

    protected $hidden = ['id'];

    public static function metaEmailsReceived(): int
    {
        return self::sum('emails_received');
    }

    public static function metaInboxesCreated(): int
    {
        return self::sum('inboxes_created');
    }

    public static function metaStorageUsed(): int
    {
        return disk_total_space(storage_path()) - disk_free_space(storage_path());
    }

    public static function metaEmailsDeleted(): int
    {
        return self::sum('emails_deleted');
    }

    public static function storageUsedBetween($date1, $date2): int
    {
        return self::whereBetween('created_at', [$date1, $date2])->sum('storage_used');
    }
}
