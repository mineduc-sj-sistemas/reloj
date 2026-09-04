<script setup lang="ts">
import { ref } from 'vue';
import type { Department } from '../types';
import { UserPlus, X } from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
  departments: Department[];
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'created', data: { pin: string; name: string; dni?: string; department_id?: number | null }): void;
}>();

const pin = ref('');
const name = ref('');
const dni = ref('');
const departmentId = ref<number | null>(null);
const isSubmitting = ref(false);

function handleSubmit() {
  if (!pin.value.trim() || !name.value.trim()) return;
  isSubmitting.value = true;
  emit('created', {
    pin: pin.value.trim(),
    name: name.value.trim(),
    dni: dni.value.trim() || undefined,
    department_id: departmentId.value || null,
  });
  pin.value = '';
  name.value = '';
  dni.value = '';
  departmentId.value = null;
  isSubmitting.value = false;
}
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 flex items-center justify-center p-4"
  >
    <div class="bg-slate-800 border border-slate-700 rounded-xl max-w-md w-full p-6 shadow-2xl space-y-4">
      <div class="flex items-center justify-between border-b border-slate-700 pb-3">
        <div class="flex items-center gap-2">
          <UserPlus class="w-5 h-5 text-purple-400" />
          <h3 class="font-bold text-white text-base">Registrar / Asociar Agente</h3>
        </div>
        <button
          @click="emit('close')"
          class="text-slate-400 hover:text-white p-1 rounded-md transition cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-3.5">
        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            PIN / Legajo (Número en el Reloj) *
          </label>
          <input
            v-model="pin"
            type="text"
            required
            placeholder="Ej. 1001"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 font-mono focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Nombre y Apellido *
          </label>
          <input
            v-model="name"
            type="text"
            required
            placeholder="Ej. Juan Pérez"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            DNI / Documento
          </label>
          <input
            v-model="dni"
            type="text"
            placeholder="Ej. 30123456"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Dependencia Asignada
          </label>
          <select
            v-model="departmentId"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"
          >
            <option :value="null">-- Sin Dependencia Asignada --</option>
            <option
              v-for="dept in departments"
              :key="dept.id"
              :value="dept.id"
            >
              {{ dept.name }}
            </option>
          </select>
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
            Guardar Agente
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
