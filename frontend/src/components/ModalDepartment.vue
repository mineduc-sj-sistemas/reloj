<script setup lang="ts">
import { shallowRef } from 'vue';
import { Building2, X } from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'created', data: { name: string; code?: string; legal_instrument?: string; address?: string }): void;
}>();

const name = shallowRef('');
const code = shallowRef('');
const legalInstrument = shallowRef('');
const address = shallowRef('');
const isSubmitting = shallowRef(false);

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
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white border border-slate-200 rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2">
            <Building2 class="w-5 h-5 text-brand-orange" />
            <h3 class="font-bold text-black text-base">Nueva Dependencia / Escuela</h3>
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
              Nombre de la Dependencia / Establecimiento *
            </label>
            <input
              v-model="name"
              type="text"
              required
              placeholder="Ej. Escuela Normal Superior Gral. San Martín"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange font-medium"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Código / CUE
            </label>
            <input
              v-model="code"
              type="text"
              placeholder="Ej. 7000123"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange font-medium"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Instrumento Legal / Resolución
            </label>
            <input
              v-model="legalInstrument"
              type="text"
              placeholder="Ej. Resolución N° 1245-ME-2024"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange font-medium"
            />
          </div>

          <div>
            <label class="block text-xs text-black font-bold mb-1">
              Domicilio / Ubicación
            </label>
            <input
              v-model="address"
              type="text"
              placeholder="Ej. Av. Libertador San Martín 450 Oeste"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-black placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:border-brand-orange font-medium"
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
              Guardar Dependencia
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>
