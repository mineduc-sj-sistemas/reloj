<script setup lang="ts">
import type { DashboardStats } from '../types';
import { Cpu, Wifi, Building2, Users, Calendar, BarChart3 } from 'lucide-vue-next';

defineProps<{
  stats: DashboardStats;
}>();
</script>

<template>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <!-- Lectores / Relojes -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
      <div>
        <p class="text-[11px] text-slate-400 font-medium">Lectores / Relojes</p>
        <p class="text-xl font-bold text-white mt-0.5">{{ stats.total_devices }}</p>
      </div>
      <div class="p-2.5 bg-blue-500/10 text-blue-400 rounded-lg">
        <Cpu class="w-5 h-5" />
      </div>
    </div>

    <!-- Relojes Online -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
      <div>
        <p class="text-[11px] text-slate-400 font-medium">Relojes Online</p>
        <p class="text-xl font-bold text-emerald-400 mt-0.5">{{ stats.online_devices }}</p>
      </div>
      <div class="p-2.5 bg-emerald-500/10 text-emerald-400 rounded-lg">
        <Wifi class="w-5 h-5" />
      </div>
    </div>

    <!-- Dependencias -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
      <div>
        <p class="text-[11px] text-slate-400 font-medium">Dependencias</p>
        <p class="text-xl font-bold text-amber-400 mt-0.5">{{ stats.total_departments }}</p>
      </div>
      <div class="p-2.5 bg-amber-500/10 text-amber-400 rounded-lg">
        <Building2 class="w-5 h-5" />
      </div>
    </div>

    <!-- Agentes / Legajos -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
      <div>
        <p class="text-[11px] text-slate-400 font-medium">Agentes / Legajos</p>
        <p class="text-xl font-bold text-purple-400 mt-0.5">{{ stats.total_employees }}</p>
      </div>
      <div class="p-2.5 bg-purple-500/10 text-purple-400 rounded-lg">
        <Users class="w-5 h-5" />
      </div>
    </div>

    <!-- Fichadas Hoy -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
      <div>
        <p class="text-[11px] text-slate-400 font-medium">Fichadas Hoy</p>
        <p class="text-xl font-bold text-cyan-400 mt-0.5">{{ stats.total_punches_today }}</p>
      </div>
      <div class="p-2.5 bg-cyan-500/10 text-cyan-400 rounded-lg">
        <Calendar class="w-5 h-5" />
      </div>
    </div>

    <!-- Total Histórico -->
    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center justify-between">
      <div>
        <p class="text-[11px] text-slate-400 font-medium">Total Histórico</p>
        <p class="text-xl font-bold text-indigo-400 mt-0.5">{{ stats.total_punches_all }}</p>
      </div>
      <div class="p-2.5 bg-indigo-500/10 text-indigo-400 rounded-lg">
        <BarChart3 class="w-5 h-5" />
      </div>
    </div>
  </div>
</template>
