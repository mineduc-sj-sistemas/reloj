<script setup lang="ts">
import { shallowRef } from 'vue';
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

const pin = shallowRef('');
const name = shallowRef('');
const dni = shallowRef('');
const departmentId = shallowRef<number | null>(null);
const isSubmitting = shallowRef(false);

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
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white border border-slate-200 rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2">
            <UserPlus class="w-5 h-5 text-brand-orange" />
            <h3 class="font-bold text-black text-base">Registrar / Asociar Agente</h3>
          </div>
          <button
            @click="emit('close')"
            class="text-black/60 hover:text-black p-1 rounded-md transition cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3.5">
          <div>
            <label class="block text-xs text-black font-bold mb-1">
              PIN / Legajo (Número en el Reloj) *
            </label>
            <input
              v-model="pin"
              type="text"
              required
              placeholder="Ej. 1001"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Nombre y Apellido *
            </label>
            <input
              v-model="name"
              type="text"
              required
              placeholder="Ej. Juan Pérez"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              DNI / Documento
            </label>
            <input
              v-model="dni"
              type="text"
              placeholder="Ej. 30123456"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Dependencia Asignada
            </label>
            <select
              v-model="departmentId"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange cursor-pointer"
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
              Guardar Agente
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>
