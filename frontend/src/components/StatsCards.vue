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
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
      <div>
        <p class="text-[11px] text-black font-semibold uppercase tracking-wider">Lectores</p>
        <p class="text-2xl font-black text-black mt-0.5">{{ stats.total_devices }}</p>
      </div>
      <div class="p-2.5 bg-brand-orange/10 text-brand-orange rounded-lg">
        <Cpu class="w-5 h-5" />
      </div>
    </div>

    <!-- Relojes Online -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
      <div>
        <p class="text-[11px] text-black font-semibold uppercase tracking-wider">En Línea</p>
        <p class="text-2xl font-black text-black mt-0.5">{{ stats.online_devices }}</p>
      </div>
      <div class="p-2.5 bg-brand-orange/10 text-brand-orange rounded-lg">
        <Wifi class="w-5 h-5" />
      </div>
    </div>

    <!-- Dependencias -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
      <div>
        <p class="text-[11px] text-black font-semibold uppercase tracking-wider">Dependencias</p>
        <p class="text-2xl font-black text-black mt-0.5">{{ stats.total_departments }}</p>
      </div>
      <div class="p-2.5 bg-brand-orange/10 text-brand-orange rounded-lg">
        <Building2 class="w-5 h-5" />
      </div>
    </div>

    <!-- Agentes / Legajos -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
      <div>
        <p class="text-[11px] text-black font-semibold uppercase tracking-wider">Agentes</p>
        <p class="text-2xl font-black text-black mt-0.5">{{ stats.total_employees }}</p>
      </div>
      <div class="p-2.5 bg-brand-orange/10 text-brand-orange rounded-lg">
        <Users class="w-5 h-5" />
      </div>
    </div>

    <!-- Fichadas Hoy -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
      <div>
        <p class="text-[11px] text-black font-semibold uppercase tracking-wider">Fichadas Hoy</p>
        <p class="text-2xl font-black text-brand-orange mt-0.5">{{ stats.total_punches_today }}</p>
      </div>
      <div class="p-2.5 bg-brand-orange/10 text-brand-orange rounded-lg">
        <Calendar class="w-5 h-5" />
      </div>
    </div>

    <!-- Total Histórico -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 flex items-center justify-between shadow-xs">
      <div>
        <p class="text-[11px] text-black font-semibold uppercase tracking-wider">Total Registros</p>
        <p class="text-2xl font-black text-black mt-0.5">{{ stats.total_punches_all }}</p>
      </div>
      <div class="p-2.5 bg-black/5 text-black rounded-lg">
        <BarChart3 class="w-5 h-5" />
      </div>
    </div>
  </div>
</template>
