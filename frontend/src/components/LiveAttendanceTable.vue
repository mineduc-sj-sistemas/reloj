<script setup lang="ts">
import type { AttendanceLog } from '../types';
import { History, Fingerprint } from 'lucide-vue-next';

defineProps<{
  logs: AttendanceLog[];
}>();
</script>

<template>
  <div class="bg-slate-800/90 border border-slate-700/80 rounded-xl shadow-lg overflow-hidden">
    <div class="px-4 py-3.5 border-b border-slate-700/80 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <History class="w-5 h-5 text-indigo-400" />
        <h2 class="font-semibold text-white text-sm">Fichadas en Vivo (Últimas Registradas)</h2>
      </div>
      <span class="text-xs text-slate-400">
        Actualización cada 3 segundos
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-slate-300">
        <thead class="bg-slate-900/60 text-[11px] text-slate-400 uppercase tracking-wider border-b border-slate-700/60">
          <tr>
            <th class="py-2.5 px-3">Agente / Empleado</th>
            <th class="py-2.5 px-3">PIN</th>
            <th class="py-2.5 px-3">Reloj / Dependencia</th>
            <th class="py-2.5 px-3">Fecha y Hora</th>
            <th class="py-2.5 px-3">Estado</th>
            <th class="py-2.5 px-3">Método</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-700/40">
          <tr v-if="logs.length === 0">
            <td colspan="6" class="py-8 text-center text-slate-500">
              No hay fichadas registradas aún.
            </td>
          </tr>
          <tr
            v-for="log in logs"
            :key="log.id"
            class="hover:bg-slate-700/30 transition"
          >
            <!-- Agente -->
            <td class="py-2.5 px-3 font-semibold text-white">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-600/30 border border-indigo-500/50 flex items-center justify-center text-[11px] font-bold text-indigo-300">
                  {{ (log.employee_name || 'A').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="text-white">{{ log.employee_name }}</p>
                  <p v-if="log.employee_department" class="text-[10px] text-slate-400">
                    {{ log.employee_department }}
                  </p>
                </div>
              </div>
            </td>

            <!-- PIN -->
            <td class="py-2.5 px-3 font-mono text-indigo-300">
              <span class="bg-indigo-950 border border-indigo-800 px-2 py-0.5 rounded text-[11px]">
                #{{ log.user_pin }}
              </span>
            </td>

            <!-- Dispositivo / Dependencia -->
            <td class="py-2.5 px-3">
              <p class="text-white font-medium">{{ log.device_name }}</p>
              <span v-if="log.device_department" class="text-[10px] text-amber-300 bg-amber-950/60 border border-amber-800/60 px-1.5 py-0.5 rounded">
                {{ log.device_department }}
              </span>
            </td>

            <!-- Fecha y Hora -->
            <td class="py-2.5 px-3 font-mono text-slate-200">
              {{ log.punch_time }}
            </td>

            <!-- Estado -->
            <td class="py-2.5 px-3">
              <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                {{ log.status_label }}
              </span>
            </td>

            <!-- Método de Verificación -->
            <td class="py-2.5 px-3 text-slate-400">
              <span class="flex items-center gap-1">
                <Fingerprint class="w-3.5 h-3.5 text-slate-500" />
                {{ log.verify_type_label }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
