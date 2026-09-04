<script setup lang="ts">
import type { Device } from '../types';
import { Cpu, Wifi, WifiOff, MapPin, Building, Settings } from 'lucide-vue-next';

defineProps<{
  devices: Device[];
}>();

const emit = defineEmits<{
  (e: 'assignDevice', device: Device): void;
}>();
</script>

<template>
  <div class="bg-slate-800/90 border border-slate-700/80 rounded-xl shadow-lg overflow-hidden">
    <div class="px-4 py-3.5 border-b border-slate-700/80 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Cpu class="w-5 h-5 text-indigo-400" />
        <h2 class="font-semibold text-white text-sm">Relojes / Lectores Biomédicos</h2>
      </div>
      <span class="text-xs font-mono text-slate-400 bg-slate-700/60 px-2 py-0.5 rounded">
        Total: {{ devices.length }}
      </span>
    </div>

    <div v-if="devices.length === 0" class="p-8 text-center text-slate-400 text-sm">
      No se detectan lectores conectados. En cuanto un reloj ZKTeco realice un ping a /iclock, aparecerá aquí automáticamente.
    </div>

    <div v-else class="divide-y divide-slate-700/50">
      <div
        v-for="device in devices"
        :key="device.id"
        class="p-4 hover:bg-slate-750 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3"
      >
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span
              :class="[
                'w-2.5 h-2.5 rounded-full',
                device.is_online ? 'bg-emerald-400 live-dot' : 'bg-rose-500'
              ]"
            ></span>
            <span class="font-bold text-white text-sm">
              {{ device.alias || device.sn }}
            </span>
            <span class="text-xs font-mono px-2 py-0.5 rounded bg-slate-700 text-slate-300">
              SN: {{ device.sn }}
            </span>
            <span
              :class="[
                'text-[10px] font-semibold px-2 py-0.5 rounded flex items-center gap-1',
                device.is_online
                  ? 'bg-emerald-950 text-emerald-300 border border-emerald-800'
                  : 'bg-rose-950 text-rose-300 border border-rose-800'
              ]"
            >
              <component :is="device.is_online ? Wifi : WifiOff" class="w-3 h-3" />
              {{ device.is_online ? 'ONLINE' : 'OFFLINE' }}
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 pt-0.5">
            <span class="flex items-center gap-1">
              <Building class="w-3.5 h-3.5 text-amber-400" />
              <strong class="text-slate-300">{{ device.department_name }}</strong>
            </span>
            <span v-if="device.location_description" class="flex items-center gap-1">
              <MapPin class="w-3.5 h-3.5 text-slate-500" />
              {{ device.location_description }}
            </span>
            <span v-if="device.ip_address" class="font-mono text-[11px] text-slate-500">
              IP: {{ device.ip_address }}
            </span>
            <span class="text-slate-500">
              Última actividad: {{ device.last_activity }}
            </span>
          </div>
        </div>

        <button
          @click="emit('assignDevice', device)"
          class="self-start sm:self-center bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium px-3 py-1.5 rounded-md border border-slate-600/80 shadow-sm flex items-center gap-1.5 transition cursor-pointer"
        >
          <Settings class="w-3.5 h-3.5 text-indigo-400" />
          <span>Configurar / Asignar</span>
        </button>
      </div>
    </div>
  </div>
</template>
