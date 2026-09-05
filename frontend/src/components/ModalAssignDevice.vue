<script setup lang="ts">
import { shallowRef, watch } from 'vue';
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

const alias = shallowRef('');
const departmentId = shallowRef<number | null>(null);
const locationDescription = shallowRef('');
const isSubmitting = shallowRef(false);

watch(() => props.device, (newDev) => {
  if (newDev) {
    alias.value = newDev.alias || '';
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
  <Teleport to="body">
    <div
      v-if="device"
      class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white border border-slate-200 rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2">
            <Settings class="w-5 h-5 text-brand-orange" />
            <h3 class="font-bold text-black text-base">Asignar Lector a Dependencia</h3>
          </div>
          <button
            @click="emit('close')"
            class="text-black/60 hover:text-black p-1 rounded-md transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs space-y-1 font-mono text-black">
          <p class="text-black/80 font-semibold">Número de Serie: <span class="text-brand-orange font-bold">{{ device.sn }}</span></p>
          <p class="text-black/80">Dirección IP: <span class="font-bold text-black">{{ device.ip_address || 'Desconocida' }}</span></p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3.5">
          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Nombre / Alias Identificador del Reloj
            </label>
            <input
              v-model="alias"
              type="text"
              placeholder="Ej. Reloj Entrada Principal"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Dependencia / Escuela Asignada
            </label>
            <select
              v-model="departmentId"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange cursor-pointer"
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
            <label class="block text-xs text-black font-bold mb-1">
              Ubicación Física Específica
            </label>
            <input
              v-model="locationDescription"
              type="text"
              placeholder="Ej. Hall central junto a dirección"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange"
            />
          </div>

          <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
            <button
              type="button"
              @click="emit('close')"
              class="bg-white border border-slate-300 text-black hover:bg-slate-50 text-xs px-4 py-2 rounded-lg font-bold transition cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="bg-brand-orange hover:bg-brand-orange/95 text-white text-xs px-4 py-2 rounded-lg font-bold shadow-sm transition cursor-pointer disabled:opacity-50"
            >
              Guardar Asignación
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>
