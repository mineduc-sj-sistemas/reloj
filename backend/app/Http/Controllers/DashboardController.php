<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\AttendanceLog;
use App\Models\DeviceCommand;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $devices = Device::with('department')->orderBy('last_activity', 'desc')->get();
        $logs = AttendanceLog::with(['device.department', 'employee'])->orderBy('punch_time', 'desc')->take(100)->get();
        $commands = DeviceCommand::orderBy('id', 'desc')->take(20)->get();
        $employees = Employee::with('department')->orderBy('pin', 'asc')->get();
        $departments = Department::withCount(['devices', 'employees'])->orderBy('name', 'asc')->get();

        $stats = [
            'total_devices' => Device::count(),
            'online_devices' => Device::all()->filter(fn($d) => $d->isOnline())->count(),
            'total_punches_today' => AttendanceLog::whereDate('punch_time', today())->count(),
            'total_punches_all' => AttendanceLog::count(),
            'total_employees' => Employee::count(),
            'total_departments' => Department::count(),
        ];

        return view('dashboard', compact('devices', 'logs', 'commands', 'employees', 'departments', 'stats'));
    }

    public function liveData()
    {
        $devices = Device::with('department')->orderBy('last_activity', 'desc')->get()->map(function ($d) {
            return [
                'id' => $d->id,
                'sn' => $d->sn,
                'alias' => $d->alias ?? $d->sn,
                'department_name' => $d->department->name ?? 'Sin Asignar',
                'location_description' => $d->location_description,
                'ip_address' => $d->ip_address,
                'is_online' => $d->isOnline(),
                'last_activity' => $d->last_activity ? $d->last_activity->diffForHumans() : 'Nunca',
                'att_count' => $d->att_count,
            ];
        });

        $logs = AttendanceLog::with(['device.department', 'employee'])
            ->orderBy('punch_time', 'desc')
            ->take(50)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'device_sn' => $l->device_sn,
                    'device_name' => $l->device?->alias ?? $l->device_sn,
                    'device_department' => $l->device?->department?->name ?? null,
                    'user_pin' => $l->user_pin,
                    'employee_name' => $l->employee?->name ?? ("Agente #" . $l->user_pin),
                    'employee_department' => $l->employee?->department_text ?? $l->employee?->department?->name,
                    'punch_time' => $l->punch_time->format('Y-m-d H:i:s'),
                    'status_label' => $l->status_label,
                    'verify_type_label' => $l->verify_type_label,
                ];
            });

        $stats = [
            'total_devices' => Device::count(),
            'online_devices' => Device::all()->filter(fn($d) => $d->isOnline())->count(),
            'total_punches_today' => AttendanceLog::whereDate('punch_time', today())->count(),
            'total_punches_all' => AttendanceLog::count(),
            'total_employees' => Employee::count(),
            'total_departments' => Department::count(),
        ];

        return response()->json([
            'stats' => $stats,
            'devices' => $devices,
            'logs' => $logs,
        ]);
    }

    public function departments()
    {
        $departments = Department::withCount(['devices', 'employees'])->orderBy('name', 'asc')->get();
        return response()->json($departments);
    }

    public function employees()
    {
        $employees = Employee::with('department')->orderBy('pin', 'asc')->get();
        return response()->json($employees);
    }

    public function commands()
    {
        $commands = DeviceCommand::orderBy('id', 'desc')->take(30)->get();
        return response()->json($commands);
    }

    public function saveDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'legal_instrument' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:200',
        ]);

        $department = Department::create($validated);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Dependencia '{$department->name}' creada correctamente.",
                'data' => $department,
            ], 201);
        }

        return back()->with('success', "Dependencia/Establecimiento '{$request->name}' creado correctamente.");
    }

    public function assignDevice(Request $request, $id)
    {
        $validated = $request->validate([
            'alias' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'location_description' => 'nullable|string|max:150',
        ]);

        $device = Device::findOrFail($id);
        $device->update([
            'alias' => $validated['alias'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'location_description' => $validated['location_description'] ?? null,
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Lector ZKTeco asignado correctamente.",
                'data' => $device->fresh('department'),
            ]);
        }

        return back()->with('success', "Lector ZKTeco asignado a la dependencia correctamente.");
    }

    public function saveEmployee(Request $request)
    {
        $validated = $request->validate([
            'pin' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'dni' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $employee = Employee::updateOrCreate(
            ['pin' => $validated['pin']],
            [
                'name' => $validated['name'],
                'dni' => $validated['dni'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
            ]
        );

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Datos del agente PIN #{$employee->pin} guardados correctamente.",
                'data' => $employee->fresh('department'),
            ], 200);
        }

        return back()->with('success', "Datos del agente PIN #{$request->pin} guardados correctamente.");
    }

    public function queueCommand(Request $request)
    {
        $validated = $request->validate([
            'device_sn' => 'required|string',
            'command' => 'required|string',
        ]);

        $command = DeviceCommand::create([
            'device_sn' => $validated['device_sn'],
            'command' => $validated['command'],
            'status' => 'pending',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Comando puesto en cola para {$command->device_sn}.",
                'data' => $command,
            ], 201);
        }

        return back()->with('success', "Comando '{$request->command}' puesto en cola para {$request->device_sn}.");
    }

    public function simulatePunch(Request $request)
    {
        // Encontrar un dispositivo existente o usar default
        $device = Device::first();
        $sn = $device ? $device->sn : 'CO8D230660045';

        // Encontrar un empleado existente o generar uno
        $employees = Employee::all();
        $pin = $employees->isNotEmpty() ? $employees->random()->pin : strval(rand(1000, 9999));

        $log = AttendanceLog::create([
            'device_sn' => $sn,
            'user_pin' => $pin,
            'punch_time' => now(),
            'status' => 0, // Entrada
            'verify_type' => 15, // Facial
            'work_code' => 0,
        ]);

        if ($device) {
            $device->update(['last_activity' => now(), 'status' => 'online']);
        }

        return response()->json([
            'success' => true,
            'message' => "Fichada simulada exitosamente para PIN #{$pin}.",
            'data' => [
                'id' => $log->id,
                'user_pin' => $log->user_pin,
                'employee_name' => optional($log->employee)->name ?? "Agente #{$pin}",
                'device_sn' => $sn,
                'punch_time' => $log->punch_time->format('Y-m-d H:i:s'),
                'status_label' => $log->status_label,
                'verify_type_label' => $log->verify_type_label,
            ]
        ]);
    }
}
