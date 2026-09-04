<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'pin',
        'name',
        'dni',
        'department_id',
        'department',
        'card_number',
        'privilege',
        'password',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'user_pin', 'pin');
    }
}
