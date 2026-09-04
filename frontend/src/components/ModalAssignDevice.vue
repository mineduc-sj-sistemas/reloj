<script setup lang="ts">
import { ref, watch } from 'vue';
import type { Device, Department } from '../types';
import { Settings, X } from 'lucide-vue-next';

const props = defineProps<{
  device: Device | null;
  departments: Department[];
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved', data: { deviceId: number; alias?: string; department_id?: number | null; location_description?: string }): void;
}>();

const alias = ref('');
const departmentId = ref<number | null>(null);
const locationDescription = ref('');
const isSubmitting = ref(false);

watch(() => props.device, (newDev) => {
  if (newDev) {
    alias.value = newDev.alias || '';
    // Find department ID if matching name exists
    const matchingDept = props.departments.find(d => d.name === newDev.department_name);
    departmentId.value = matchingDept ? matchingDept.id : null;
    locationDescription.value = newDev.location_description || '';
  }
}, { immediate: true });

function handleSubmit() {
  if (!props.device) return;
  isSubmitting.value = true;
  emit('saved', {
    deviceId: props.device.id,
    alias: alias.value.trim() || undefined,
    department_id: departmentId.value,
    location_description: locationDescription.value.trim() || undefined,
  });
  isSubmitting.value = false;
}
</script>

<template>
  <div
    v-if="device"
    class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 flex items-center justify-center p-4"
  >
    <div class="bg-slate-800 border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl space-y-4">
      <div class="flex items-center justify-between border-b border-slate-700 pb-3">
        <div class="flex items-center gap-2">
          <Settings class="w-5 h-5 text-indigo-400" />
          <h3 class="font-bold text-white text-base">Asignar Lector a Dependencia</h3>
        </div>
        <button
          @click="emit('close')"
          class="text-slate-400 hover:text-white p-1 rounded-md transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="bg-slate-900/80 p-3 rounded-lg border border-slate-700/60 text-xs space-y-1 font-mono">
        <p class="text-slate-400">Número de Serie: <span class="text-indigo-300 font-bold">{{ device.sn }}</span></p>
        <p class="text-slate-400">Dirección IP: <span class="text-slate-200">{{ device.ip_address || 'Desconocida' }}</span></p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-3.5">
        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Nombre / Alias Identificador del Reloj
          </label>
          <input
            v-model="alias"
            type="text"
            placeholder="Ej. Reloj Entrada Principal"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Dependencia / Escuela Asignada
          </label>
          <select
            v-model="departmentId"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
          >
            <option :value="null">-- Seleccionar Dependencia --</option>
            <option
              v-for="dept in departments"
              :key="dept.id"
              :value="dept.id"
            >
              {{ dept.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Ubicación Física Específica
          </label>
          <input
            v-model="locationDescription"
            type="text"
            placeholder="Ej. Hall central junto a dirección"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div class="pt-3 border-t border-slate-700 flex justify-end gap-2">
          <button
            type="button"
            @click="emit('close')"
            class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs px-4 py-2 rounded-lg font-medium transition cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs px-4 py-2 rounded-lg font-semibold shadow transition cursor-pointer disabled:opacity-50"
          >
            Guardar Asignación
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
