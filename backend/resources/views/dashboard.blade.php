<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZKTeco ADMS Server - Control de Asistencia Multidependencia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 0.4; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .live-dot {
            animation: pulse-ring 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
    <!-- Navbar -->
    <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-600 rounded-lg text-white shadow-lg">
                    <i class="fa-solid fa-fingerprint text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                        ZKTeco ADMS Server
                        <span class="text-xs font-semibold px-2 py-0.5 bg-emerald-950 text-emerald-400 border border-emerald-800 rounded-full flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 live-dot"></span> EN VIVO
                        </span>
                    </h1>
                    <p class="text-xs text-slate-400">Gestión Multidependencia para ZKTeco MB20-VL & SQLite</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button onclick="document.getElementById('modal-department').classList.remove('hidden')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold px-3 py-2 rounded-md shadow flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-building-columns text-indigo-400"></i> + Dependencia
                </button>
                <button onclick="document.getElementById('modal-employee').classList.remove('hidden')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold px-3 py-2 rounded-md shadow flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-user-plus text-purple-400"></i> + Agente
                </button>
                <button onclick="triggerTestSimulation()" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-3.5 py-2 rounded-md shadow flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-bolt"></i> Simular Fichada
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Notificaciones Flash -->
        @if(session('success'))
            <div class="bg-emerald-900/50 border border-emerald-500/50 text-emerald-200 px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-slate-400 font-medium">Lectores / Relojes</p>
                    <p class="text-xl font-bold text-white mt-0.5" id="stat-devices">{{ $stats['total_devices'] }}</p>
                </div>
                <div class="p-2.5 bg-blue-500/10 text-blue-400 rounded-lg">
                    <i class="fa-solid fa-microchip"></i>
                </div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-slate-400 font-medium">Relojes Online</p>
                    <p class="text-xl font-bold text-emerald-400 mt-0.5" id="stat-online">{{ $stats['online_devices'] }}</p>
                </div>
                <div class="p-2.5 bg-emerald-500/10 text-emerald-400 rounded-lg">
                    <i class="fa-solid fa-wifi"></i>
                </div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-slate-400 font-medium">Dependencias</p>
                    <p class="text-xl font-bold text-amber-400 mt-0.5">{{ $stats['total_departments'] }}</p>
                </div>
                <div class="p-2.5 bg-amber-500/10 text-amber-400 rounded-lg">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-slate-400 font-medium">Agentes / Legajos</p>
                    <p class="text-xl font-bold text-purple-400 mt-0.5" id="stat-employees">{{ $stats['total_employees'] }}</p>
                </div>
                <div class="p-2.5 bg-purple-500/10 text-purple-400 rounded-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-slate-400 font-medium">Fichadas de Hoy</p>
                    <p class="text-xl font-bold text-indigo-400 mt-0.5" id="stat-today">{{ $stats['total_punches_today'] }}</p>
                </div>
                <div class="p-2.5 bg-indigo-500/10 text-indigo-400 rounded-lg">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-slate-400 font-medium">Total Registros</p>
                    <p class="text-xl font-bold text-cyan-400 mt-0.5" id="stat-all">{{ $stats['total_punches_all'] }}</p>
                </div>
                <div class="p-2.5 bg-cyan-500/10 text-cyan-400 rounded-lg">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
        </div>

        <!-- Grilla Principal -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Columna Izquierda: Relojes y Dependencias (4 cols) -->
            <div class="lg:col-span-4 space-y-4">
                
                <!-- Tarjeta Relojes Conectados -->
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-bold text-sm text-white flex items-center gap-2">
                            <i class="fa-solid fa-clock text-indigo-400"></i> Lectores ZKTeco
                        </h2>
                        <span class="text-[11px] text-slate-400" id="devices-count-badge">{{ count($devices) }} conectados</span>
                    </div>

                    <div id="devices-list" class="space-y-3 max-h-[450px] overflow-y-auto pr-1">
                        @forelse($devices as $device)
                            <div class="bg-slate-900/90 border border-slate-700/70 rounded-lg p-3.5 hover:border-slate-600 transition space-y-2">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-white text-sm">{{ $device->alias ?? ('Lector ' . $device->sn) }}</h3>
                                            @if($device->isOnline())
                                                <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-emerald-950 text-emerald-400 border border-emerald-800 rounded">Online</span>
                                            @else
                                                <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-rose-950 text-rose-400 border border-rose-800 rounded">Offline</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] font-mono text-slate-400">SN: {{ $device->sn }} • IP: {{ $device->ip_address ?? 'N/A' }}</p>
                                    </div>
                                    <button onclick="openAssignDeviceModal('{{ $device->id }}', '{{ $device->alias ?? '' }}', '{{ $device->department_id ?? '' }}', '{{ $device->location_description ?? '' }}')" class="text-xs text-indigo-400 hover:text-indigo-300 bg-slate-800 px-2 py-1 rounded border border-slate-700 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot"></i> Asignar
                                    </button>
                                </div>

                                <!-- Lugar / Dependencia asignada -->
                                <div class="bg-slate-800/80 p-2 rounded text-[11px] border border-slate-700/60">
                                    <div class="flex items-center gap-1.5 text-amber-300 font-semibold">
                                        <i class="fa-solid fa-building"></i>
                                        <span>{{ $device->department ? $device->department->name : 'Sin Dependencia asignada' }}</span>
                                    </div>
                                    @if($device->location_description)
                                        <p class="text-[10px] text-slate-400 mt-0.5 pl-4">Ubicación física: <span class="text-slate-200">{{ $device->location_description }}</span></p>
                                    @endif
                                </div>

                                <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1">
                                    <span>Último contacto: <strong class="text-slate-200">{{ $device->last_activity ? $device->last_activity->diffForHumans() : 'Nunca' }}</strong></span>
                                    <span>Fichadas: <strong class="text-indigo-400">{{ $device->att_count }}</strong></span>
                                </div>

                                <!-- Comandos -->
                                <div class="pt-2 border-t border-slate-800/80 flex items-center gap-1">
                                    <form action="{{ route('commands.queue') }}" method="POST" class="flex-1 flex gap-1">
                                        @csrf
                                        <input type="hidden" name="device_sn" value="{{ $device->sn }}">
                                        <select name="command" class="bg-slate-800 border border-slate-700 text-[11px] text-slate-200 rounded px-2 py-1 flex-1 focus:outline-none">
                                            <option value="CHECK">Verificar Estado (CHECK)</option>
                                            <option value="INFO">Consultar Info (INFO)</option>
                                            <option value="DATA QUERY USERINFO">Descargar Usuarios (QUERY USER)</option>
                                            <option value="REBOOT">Reiniciar Reloj (REBOOT)</option>
                                        </select>
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-semibold px-2 py-1 rounded">
                                            Enviar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-500 text-xs">
                                <i class="fa-solid fa-satellite-dish text-3xl mb-2 text-slate-600"></i>
                                <p>No hay lectores conectados aún.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Dependencias / Establecimientos Registrados -->
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-bold text-sm text-white flex items-center gap-2">
                            <i class="fa-solid fa-building-columns text-amber-400"></i> Dependencias / Sedes
                        </h2>
                        <button onclick="document.getElementById('modal-department').classList.remove('hidden')" class="text-xs text-amber-400 hover:text-amber-300">
                            + Crear Sede
                        </button>
                    </div>

                    <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                        @forelse($departments as $dept)
                            <div class="bg-slate-900/70 border border-slate-700/50 rounded-lg p-2.5 text-xs">
                                <div class="flex items-center justify-between font-semibold text-white">
                                    <span>{{ $dept->name }}</span>
                                    <span class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded">{{ $dept->devices_count }} lector(es)</span>
                                </div>
                                @if($dept->legal_instrument)
                                    <p class="text-[10px] text-slate-400 mt-0.5"><i class="fa-solid fa-file-contract text-slate-500"></i> {{ $dept->legal_instrument }}</p>
                                @endif
                                @if($dept->address)
                                    <p class="text-[10px] text-slate-400"><i class="fa-solid fa-map-pin text-slate-500"></i> {{ $dept->address }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-500 text-xs text-center py-3">No hay dependencias creadas. Haz clic en "+ Dependencia" para crear una.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Fichadas en Tiempo Real (8 cols) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-sm text-white flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-emerald-400"></i> Fichadas en Tiempo Real
                            </h2>
                            <span class="text-[11px] bg-slate-700 text-slate-300 px-2 py-0.5 rounded-full" id="logs-count">{{ count($logs) }}</span>
                        </div>
                        <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate fa-spin text-slate-500"></i> Auto-refresco (3s)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="text-[11px] uppercase bg-slate-900/60 text-slate-400 border-b border-slate-700">
                                <tr>
                                    <th scope="col" class="py-2.5 px-3">Agente / Nombre</th>
                                    <th scope="col" class="py-2.5 px-3">PIN</th>
                                    <th scope="col" class="py-2.5 px-3">Lector / Dependencia</th>
                                    <th scope="col" class="py-2.5 px-3">Fecha y Hora</th>
                                    <th scope="col" class="py-2.5 px-3">Evento</th>
                                    <th scope="col" class="py-2.5 px-3">Método</th>
                                </tr>
                            </thead>
                            <tbody id="logs-table-body" class="divide-y divide-slate-700/60 font-sans">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-slate-700/30 transition">
                                        <td class="py-2.5 px-3 font-semibold text-white">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-[11px] font-bold text-indigo-300">
                                                    {{ strtoupper(substr($log->employee?->name ?? 'A', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="text-white">{{ $log->employee?->name ?? ('Agente #' . $log->user_pin) }}</p>
                                                    @if($log->employee?->department?->name)
                                                        <p class="text-[10px] text-slate-400">{{ $log->employee->department->name }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 font-mono text-indigo-300">
                                            <span class="bg-indigo-950 border border-indigo-800 px-2 py-0.5 rounded text-[11px]">
                                                #{{ $log->user_pin }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <p class="text-white font-medium">{{ $log->device?->alias ?? $log->device_sn }}</p>
                                            @if($log->device?->department?->name)
                                                <span class="text-[10px] text-amber-300 bg-amber-950/60 border border-amber-800/60 px-1.5 py-0.5 rounded">
                                                    {{ $log->device->department->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 font-mono text-slate-200">
                                            {{ $log->punch_time->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            @if($log->status == 0)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                                                    {{ $log->status_label }}
                                                </span>
                                            @elseif($log->status == 1)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-amber-950 text-amber-400 border border-amber-800">
                                                    {{ $log->status_label }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                                    {{ $log->status_label }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-400">
                                            <i class="fa-solid fa-id-badge mr-1 text-slate-500"></i> {{ $log->verify_type_label }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-logs-row">
                                        <td colspan="6" class="py-8 text-center text-slate-500">
                                            No se han recibido fichadas aún.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Asignar Lector a Dependencia -->
    <div id="modal-assign-device" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-indigo-400"></i> Asignar Lector a Dependencia
                </h3>
                <button onclick="document.getElementById('modal-assign-device').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="form-assign-device" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre Descriptivo / Alias del Lector</label>
                    <input type="text" id="assign_alias" name="alias" placeholder="Ej. Lector Entrada Guardia" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Dependencia / Sede Perteneciente</label>
                    <select id="assign_dept" name="department_id" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                        <option value="">-- Sin Asignar --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Ubicación Física Específica</label>
                    <input type="text" id="assign_location" name="location_description" placeholder="Ej. Edificio A - Planta Baja, Puerta Este" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>

                <div class="pt-3 border-t border-slate-700 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-assign-device').classList.add('hidden')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs px-4 py-2 rounded-lg font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-4 py-2 rounded-lg font-semibold shadow">
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Crear Dependencia -->
    <div id="modal-department" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-amber-400"></i> Nueva Dependencia / Establecimiento
                </h3>
                <button onclick="document.getElementById('modal-department').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('departments.save') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre de la Repartición / Sede</label>
                    <input type="text" name="name" required placeholder="Ej: Hospital Central Dr. Ramón Carrillo" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Código Identificador (Opcional)</label>
                    <input type="text" name="code" placeholder="Ej: HOSP-CENT-01" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Instrumento Legal de Creación (Opcional)</label>
                    <input type="text" name="legal_instrument" placeholder="Ej: Decreto Provincial N° 1420/2021" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Dirección / Radio Físico</label>
                    <input type="text" name="address" placeholder="Ej: Av. San Martín 1250, Capital" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>

                <div class="pt-3 border-t border-slate-700 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-department').classList.add('hidden')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs px-4 py-2 rounded-lg font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white text-xs px-4 py-2 rounded-lg font-semibold shadow">
                        Crear Dependencia
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Gestión de Empleado / Legajo -->
    <div id="modal-employee" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-indigo-400"></i> Registrar / Editar Agente
                </h3>
                <button onclick="document.getElementById('modal-employee').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('employees.save') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">PIN / ID en el Reloj (Ej. 1 o 23644466)</label>
                    <input type="text" id="emp_pin" name="pin" required placeholder="Número de PIN en el ZKTeco" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre Completo</label>
                    <input type="text" id="emp_name" name="name" required placeholder="Ej: Prueba" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">DNI / Documento (Opcional)</label>
                    <input type="text" id="emp_dni" name="dni" placeholder="Ej: 35123456" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Dependencia / Repartición Asignada</label>
                    <select id="emp_dept_id" name="department_id" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-indigo-500">
                        <option value="">-- Sin Dependencia Específica --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-700 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-employee').classList.add('hidden')" class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs px-4 py-2 rounded-lg font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-4 py-2 rounded-lg font-semibold shadow">
                        Guardar Agente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script de Actualización en Tiempo Real -->
    <script>
        function openAssignDeviceModal(deviceId, alias, deptId, location) {
            document.getElementById('form-assign-device').action = `/devices/${deviceId}/assign`;
            document.getElementById('assign_alias').value = alias || '';
            document.getElementById('assign_dept').value = deptId || '';
            document.getElementById('assign_location').value = location || '';
            document.getElementById('modal-assign-device').classList.remove('hidden');
        }

        async function fetchLiveData() {
            try {
                const res = await fetch('/api/live-data');
                if (!res.ok) return;
                const data = await res.json();

                // Actualizar contadores
                document.getElementById('stat-devices').innerText = data.stats.total_devices;
                document.getElementById('stat-online').innerText = data.stats.online_devices;
                document.getElementById('stat-today').innerText = data.stats.total_punches_today;
                document.getElementById('stat-all').innerText = data.stats.total_punches_all;
                if (document.getElementById('stat-employees')) {
                    document.getElementById('stat-employees').innerText = data.stats.total_employees;
                }

                // Actualizar tabla de fichadas
                const tbody = document.getElementById('logs-table-body');
                if (data.logs.length > 0) {
                    tbody.innerHTML = data.logs.map(log => `
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="py-2.5 px-3 font-semibold text-white">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-[11px] font-bold text-indigo-300">
                                        ${(log.employee_name || 'A').charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <p class="text-white">${log.employee_name}</p>
                                        ${log.employee_department ? `<p class="text-[10px] text-slate-400">${log.employee_department}</p>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-indigo-300">
                                <span class="bg-indigo-950 border border-indigo-800 px-2 py-0.5 rounded text-[11px]">
                                    #${log.user_pin}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <p class="text-white font-medium">${log.device_name}</p>
                                ${log.device_department ? `<span class="text-[10px] text-amber-300 bg-amber-950/60 border border-amber-800/60 px-1.5 py-0.5 rounded">${log.device_department}</span>` : ''}
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-200">${log.punch_time}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                                    ${log.status_label}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-400">
                                <i class="fa-solid fa-id-badge mr-1 text-slate-500"></i> ${log.verify_type_label}
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (err) {
                console.error('Error fetching live data:', err);
            }
        }

        async function triggerTestSimulation() {
            const randomPin = Math.floor(1000 + Math.random() * 9000);
            const now = new Date();
            const timeStr = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0');

            const payload = `${randomPin}\t${timeStr}\t0\t15\t0\t0\t0\n`;

            try {
                await fetch('/iclock/cdata?SN=CO8D230660045&pushver=2.4.1');
                await fetch('/iclock/cdata?SN=CO8D230660045&table=ATTLOG', {
                    method: 'POST',
                    headers: { 'Content-Type': 'text/plain' },
                    body: payload
                });
                fetchLiveData();
            } catch (err) {
                alert('Error: ' + err);
            }
        }

        setInterval(fetchLiveData, 3000);
    </script>
</body>
</html>
