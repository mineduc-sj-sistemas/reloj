<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_sn',
        'command_id',
        'command',
        'status',
        'response',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_sn', 'sn');
    }
}
