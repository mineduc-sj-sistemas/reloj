<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'sn',
        'alias',
        'department_id',
        'location_description',
        'ip_address',
        'firmware_version',
        'push_version',
        'user_count',
        'finger_count',
        'face_count',
        'att_count',
        'status',
        'last_activity',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'device_sn', 'sn');
    }

    public function commands()
    {
        return $this->hasMany(DeviceCommand::class, 'device_sn', 'sn');
    }

    public function isOnline(): bool
    {
        if (!$this->last_activity) {
            return false;
        }
        // Si tuvo actividad en los últimos 5 minutos se considera online
        return $this->last_activity->diffInMinutes(now()) <= 5;
    }
}
