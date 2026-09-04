<script setup lang="ts">
import { ref } from 'vue';
import { Building2, X } from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'created', data: { name: string; code?: string; legal_instrument?: string; address?: string }): void;
}>();

const name = ref('');
const code = ref('');
const legalInstrument = ref('');
const address = ref('');
const isSubmitting = ref(false);

function handleSubmit() {
  if (!name.value.trim()) return;
  isSubmitting.value = true;
  emit('created', {
    name: name.value.trim(),
    code: code.value.trim() || undefined,
    legal_instrument: legalInstrument.value.trim() || undefined,
    address: address.value.trim() || undefined,
  });
  name.value = '';
  code.value = '';
  legalInstrument.value = '';
  address.value = '';
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
          <Building2 class="w-5 h-5 text-indigo-400" />
          <h3 class="font-bold text-white text-base">Nueva Dependencia / Escuela</h3>
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
            Nombre de la Dependencia / Establecimiento *
          </label>
          <input
            v-model="name"
            type="text"
            required
            placeholder="Ej. Escuela Normal Superior Gral. San Martín"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Código / CUE
          </label>
          <input
            v-model="code"
            type="text"
            placeholder="Ej. 7000123"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Instrumento Legal / Resolución
          </label>
          <input
            v-model="legalInstrument"
            type="text"
            placeholder="Ej. Resolución N° 1245-ME-2024"
            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-xs text-slate-300 font-medium mb-1">
            Domicilio / Ubicación
          </label>
          <input
            v-model="address"
            type="text"
            placeholder="Ej. Av. Libertador San Martín 450 Oeste"
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
            Guardar Dependencia
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
