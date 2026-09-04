<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_sn',
        'user_pin',
        'punch_time',
        'status',
        'verify_type',
        'work_code',
        'reserved_1',
        'reserved_2',
        'raw_data',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'status' => 'integer',
        'verify_type' => 'integer',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_sn', 'sn');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_pin', 'pin');
    }

    /**
     * Descripción amigable del tipo de fichaje
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => 'Entrada (Check-In)',
            1 => 'Salida (Check-Out)',
            2 => 'Salida a Descanso (Break-Out)',
            3 => 'Regreso de Descanso (Break-In)',
            4 => 'Entrada Horas Extra (OT-In)',
            5 => 'Salida Horas Extra (OT-Out)',
            default => "Estado ({$this->status})"
        };
    }

    /**
     * Descripción amigable del método de verificación
     */
    public function getVerifyTypeLabelAttribute(): string
    {
        return match ($this->verify_type) {
            0 => 'Contraseña',
            1 => 'Huella Dactilar',
            2 => 'Tarjeta / RFID',
            3 => 'Contraseña / PIN',
            4 => 'Huella',
            15 => 'Reconocimiento Facial',
            25 => 'Palma',
            default => "Tipo ({$this->verify_type})"
        };
    }
}
