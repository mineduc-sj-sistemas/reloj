<script setup lang="ts">
import { Fingerprint, Building2, UserPlus, Zap } from 'lucide-vue-next';

defineProps<{
  isSimulating: boolean;
}>();

const emit = defineEmits<{
  (e: 'openDepartment'): void;
  (e: 'openEmployee'): void;
  (e: 'simulatePunch'): void;
}>();
</script>

<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <img
          src="/logoMinisterio.png"
          alt="Ministerio de Educación de San Juan"
          class="h-11 w-auto object-contain border border-slate-200 p-1 rounded-md bg-white shadow-xs hidden sm:block"
        />
        <div class="p-2 bg-brand-orange rounded-lg text-white shadow-sm">
          <Fingerprint class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-lg font-black tracking-tight text-black flex items-center gap-2">
            ZKTeco ADMS Server
            <span class="text-xs font-bold px-2 py-0.5 bg-white text-brand-orange border border-brand-orange rounded-full flex items-center gap-1.5 shadow-xs">
              <span class="w-2 h-2 rounded-full bg-brand-orange live-dot"></span> EN VIVO
            </span>
          </h1>
          <p class="text-xs text-black/70 font-medium">Ministerio de Educación · Gestión Multidependencia ZKTeco MB20-VL</p>
        </div>
      </div>

      <div class="flex items-center gap-2.5">
        <button
          @click="emit('openDepartment')"
          class="bg-white border border-brand-orange text-black hover:bg-brand-orange/5 px-3 py-2 rounded-lg transition-colors text-xs font-bold flex items-center gap-1.5 shadow-xs cursor-pointer"
        >
          <Building2 class="w-4 h-4 text-brand-orange" />
          <span>+ Dependencia</span>
        </button>

        <button
          @click="emit('openEmployee')"
          class="bg-white border border-brand-orange text-black hover:bg-brand-orange/5 px-3 py-2 rounded-lg transition-colors text-xs font-bold flex items-center gap-1.5 shadow-xs cursor-pointer"
        >
          <UserPlus class="w-4 h-4 text-brand-orange" />
          <span>+ Agente</span>
        </button>

        <button
          @click="emit('simulatePunch')"
          :disabled="isSimulating"
          class="bg-brand-orange hover:bg-brand-orange/95 disabled:opacity-50 text-white font-bold px-3.5 py-2 rounded-lg transition-colors border border-transparent shadow-sm text-xs flex items-center gap-1.5 cursor-pointer"
        >
          <Zap class="w-4 h-4" />
          <span>{{ isSimulating ? 'Simulando...' : 'Simular Fichada' }}</span>
        </button>
      </div>
    </div>
  </header>
</template>
