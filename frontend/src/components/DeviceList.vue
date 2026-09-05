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
  <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
    <div class="px-4 py-3.5 border-b border-slate-200 bg-white flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Cpu class="w-5 h-5 text-brand-orange" />
        <h2 class="font-bold text-black text-sm">Relojes / Lectores Biométricos</h2>
      </div>
      <span class="text-xs font-mono font-bold text-black bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
        Total: {{ devices.length }}
      </span>
    </div>

    <div v-if="devices.length === 0" class="p-8 text-center text-black/70 text-sm">
      No se detectan lectores conectados. En cuanto un reloj ZKTeco realice un ping a /iclock, aparecerá aquí automáticamente.
    </div>

    <div v-else class="divide-y divide-slate-100">
      <div
        v-for="device in devices"
        :key="device.id"
        class="p-4 hover:bg-slate-50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3"
      >
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span
              :class="[
                'w-2.5 h-2.5 rounded-full',
                device.is_online ? 'bg-brand-orange live-dot' : 'bg-brand-red'
              ]"
            ></span>
            <span class="font-bold text-black text-sm">
              {{ device.alias || device.sn }}
            </span>
            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-black">
              SN: {{ device.sn }}
            </span>
            <span
              :class="[
                'text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1 border',
                device.is_online
                  ? 'bg-brand-orange/10 text-brand-orange border-brand-orange'
                  : 'bg-brand-red/10 text-brand-red border-brand-red'
              ]"
            >
              <component :is="device.is_online ? Wifi : WifiOff" class="w-3 h-3" />
              {{ device.is_online ? 'ONLINE' : 'OFFLINE' }}
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-3 text-xs text-black/80 pt-0.5">
            <span class="flex items-center gap-1">
              <Building class="w-3.5 h-3.5 text-brand-orange" />
              <strong class="text-black font-semibold">{{ device.department_name }}</strong>
            </span>
            <span v-if="device.location_description" class="flex items-center gap-1 text-black/70">
              <MapPin class="w-3.5 h-3.5 text-slate-400" />
              {{ device.location_description }}
            </span>
            <span v-if="device.ip_address" class="font-mono text-[11px] text-black/70">
              IP: {{ device.ip_address }}
            </span>
            <span class="text-black/60">
              Última actividad: {{ device.last_activity }}
            </span>
          </div>
        </div>

        <button
          @click="emit('assignDevice', device)"
          class="self-start sm:self-center bg-white border border-brand-orange text-black hover:bg-brand-orange/5 px-3 py-1.5 rounded-lg transition-colors text-xs font-bold flex items-center gap-1.5 shadow-xs cursor-pointer"
        >
          <Settings class="w-3.5 h-3.5 text-brand-orange" />
          <span>Configurar / Asignar</span>
        </button>
      </div>
    </div>
  </div>
</template>
