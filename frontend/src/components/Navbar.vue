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
  <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-indigo-600 rounded-lg text-white shadow-lg">
          <Fingerprint class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
            ZKTeco ADMS Server
            <span class="text-xs font-semibold px-2 py-0.5 bg-emerald-950 text-emerald-400 border border-emerald-800 rounded-full flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-400 live-dot"></span> EN VIVO
            </span>
          </h1>
          <p class="text-xs text-slate-400">Gestión Multidependencia para ZKTeco MB20-VL & SQLite (Vue 3 + TS)</p>
        </div>
      </div>

      <div class="flex items-center gap-2.5">
        <button
          @click="emit('openDepartment')"
          class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold px-3 py-2 rounded-md shadow flex items-center gap-1.5 transition cursor-pointer"
        >
          <Building2 class="w-4 h-4 text-indigo-400" />
          <span>+ Dependencia</span>
        </button>

        <button
          @click="emit('openEmployee')"
          class="bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold px-3 py-2 rounded-md shadow flex items-center gap-1.5 transition cursor-pointer"
        >
          <UserPlus class="w-4 h-4 text-purple-400" />
          <span>+ Agente</span>
        </button>

        <button
          @click="emit('simulatePunch')"
          :disabled="isSimulating"
          class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold px-3.5 py-2 rounded-md shadow flex items-center gap-1.5 transition cursor-pointer"
        >
          <Zap class="w-4 h-4" />
          <span>{{ isSimulating ? 'Simulando...' : 'Simular Fichada' }}</span>
        </button>
      </div>
    </div>
  </header>
</template>
