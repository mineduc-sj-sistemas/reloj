<script setup lang="ts">
import type { AttendanceLog } from '../types';
import {
  History,
  Fingerprint,
  ScanFace,
  KeyRound,
  CreditCard,
  Hand,
} from 'lucide-vue-next';

defineProps<{
  logs: AttendanceLog[];
}>();

function getVerifyIcon(log: AttendanceLog) {
  const label = (log.verify_type_label || '').toLowerCase();
  const type = log.verify_type;

  // Reconocimiento Facial
  if (type === 15 || label.includes('facial') || label.includes('rostro')) {
    return ScanFace;
  }
  // Huella Dactilar
  if (type === 1 || type === 4 || label.includes('huella')) {
    return Fingerprint;
  }
  // Contraseña / PIN
  if (type === 0 || type === 3 || label.includes('contraseña') || label.includes('pin') || label.includes('clave')) {
    return KeyRound;
  }
  // Tarjeta / RFID
  if (type === 2 || label.includes('tarjeta') || label.includes('rfid')) {
    return CreditCard;
  }
  // Palma
  if (type === 25 || label.includes('palma')) {
    return Hand;
  }
  return Fingerprint;
}
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
    <div class="px-4 py-3.5 border-b border-slate-200 bg-white flex items-center justify-between">
      <div class="flex items-center gap-2">
        <History class="w-5 h-5 text-brand-orange" />
        <h2 class="font-bold text-black text-sm">Fichadas en Vivo (Últimas Registradas)</h2>
      </div>
      <span class="text-xs font-semibold text-black/60">
        Actualización cada 3 segundos
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs text-black">
        <thead class="bg-brand-orange text-white uppercase font-black text-xs tracking-wider">
          <tr>
            <th class="py-3 px-4">Agente / Empleado</th>
            <th class="py-3 px-4">PIN</th>
            <th class="py-3 px-4">Reloj / Dependencia</th>
            <th class="py-3 px-4">Fecha y Hora</th>
            <th class="py-3 px-4">Estado</th>
            <th class="py-3 px-4">Método</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="logs.length === 0">
            <td colspan="6" class="py-8 text-center text-black/60 font-medium">
              No hay fichadas registradas aún.
            </td>
          </tr>
          <tr
            v-for="log in logs"
            :key="log.id"
            class="hover:bg-slate-50 transition"
          >
            <!-- Agente -->
            <td class="py-2.5 px-4 font-bold text-black">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-full bg-brand-orange/15 border border-brand-orange/40 flex items-center justify-center text-[11px] font-black text-brand-orange">
                  {{ (log.employee_name || 'A').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="text-black font-bold">{{ log.employee_name }}</p>
                  <p v-if="log.employee_department" class="text-[10px] text-black/70 font-semibold">
                    {{ log.employee_department }}
                  </p>
                </div>
              </div>
            </td>

            <!-- PIN -->
            <td class="py-2.5 px-4 font-mono font-bold text-black">
              <span class="bg-slate-100 border border-slate-300 px-2 py-0.5 rounded text-[11px] text-black">
                #{{ log.user_pin }}
              </span>
            </td>

            <!-- Dispositivo / Dependencia -->
            <td class="py-2.5 px-4">
              <p class="text-black font-semibold">{{ log.device_name }}</p>
              <span v-if="log.device_department" class="text-[10px] text-brand-orange font-bold bg-brand-orange/10 border border-brand-orange/30 px-1.5 py-0.5 rounded">
                {{ log.device_department }}
              </span>
            </td>

            <!-- Fecha y Hora -->
            <td class="py-2.5 px-4 font-mono text-black font-semibold">
              {{ log.punch_time }}
            </td>

            <!-- Estado -->
            <td class="py-2.5 px-4">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-orange/10 text-brand-orange border border-brand-orange/40">
                {{ log.status_label }}
              </span>
            </td>

            <!-- Método de Verificación -->
            <td class="py-2.5 px-4 text-black font-semibold">
              <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-2 py-1 rounded-md text-xs">
                <component :is="getVerifyIcon(log)" class="w-4 h-4 text-brand-orange shrink-0" />
                <span class="text-black">{{ log.verify_type_label }}</span>
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
