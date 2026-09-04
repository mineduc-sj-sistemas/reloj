<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import type { DashboardStats, Device, AttendanceLog, Department } from './types';
import { api } from './services/api';

import Navbar from './components/Navbar.vue';
import StatsCards from './components/StatsCards.vue';
import DeviceList from './components/DeviceList.vue';
import LiveAttendanceTable from './components/LiveAttendanceTable.vue';
import ModalDepartment from './components/ModalDepartment.vue';
import ModalEmployee from './components/ModalEmployee.vue';
import ModalAssignDevice from './components/ModalAssignDevice.vue';
import { CheckCircle2, AlertCircle } from 'lucide-vue-next';

const stats = ref<DashboardStats>({
  total_devices: 0,
  online_devices: 0,
  total_punches_today: 0,
  total_punches_all: 0,
  total_employees: 0,
  total_departments: 0,
});

const devices = ref<Device[]>([]);
const logs = ref<AttendanceLog[]>([]);
const departments = ref<Department[]>([]);

// Modals state
const isModalDeptOpen = ref(false);
const isModalEmpOpen = ref(false);
const selectedDeviceForAssign = ref<Device | null>(null);

// Notifications & action states
const notification = ref<{ message: string; type: 'success' | 'error' } | null>(null);
const isSimulating = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

function notify(message: string, type: 'success' | 'error' = 'success') {
  notification.value = { message, type };
  setTimeout(() => {
    if (notification.value?.message === message) {
      notification.value = null;
    }
  }, 4000);
}

async function loadLiveData() {
  try {
    const data = await api.getLiveData();
    stats.value = data.stats;
    devices.value = data.devices;
    logs.value = data.logs;
  } catch (err) {
    console.error('Error fetching live data:', err);
  }
}

async function loadDepartments() {
  try {
    departments.value = await api.getDepartments();
  } catch (err) {
    console.error('Error loading departments:', err);
  }
}

async function handleSimulatePunch() {
  isSimulating.value = true;
  try {
    const res = await api.simulatePunch();
    notify(res.message || 'Fichada simulada con éxito');
    await loadLiveData();
  } catch (err) {
    notify('Error al simular fichada: ' + err, 'error');
  } finally {
    isSimulating.value = false;
  }
}

async function handleCreateDepartment(data: { name: string; code?: string; legal_instrument?: string; address?: string }) {
  try {
    const res = await api.createDepartment(data);
    notify(res.message || 'Dependencia creada correctamente');
    isModalDeptOpen.value = false;
    await Promise.all([loadDepartments(), loadLiveData()]);
  } catch (err: any) {
    notify(err.message || 'Error al guardar dependencia', 'error');
  }
}

async function handleCreateEmployee(data: { pin: string; name: string; dni?: string; department_id?: number | null }) {
  try {
    const res = await api.saveEmployee(data);
    notify(res.message || 'Agente guardado correctamente');
    isModalEmpOpen.value = false;
    await loadLiveData();
  } catch (err: any) {
    notify(err.message || 'Error al guardar agente', 'error');
  }
}

async function handleAssignDevice(data: { deviceId: number; alias?: string; department_id?: number | null; location_description?: string }) {
  try {
    const res = await api.assignDevice(data.deviceId, {
      alias: data.alias,
      department_id: data.department_id,
      location_description: data.location_description,
    });
    notify(res.message || 'Lector asignado correctamente');
    selectedDeviceForAssign.value = null;
    await loadLiveData();
  } catch (err: any) {
    notify(err.message || 'Error al asignar lector', 'error');
  }
}

onMounted(() => {
  loadLiveData();
  loadDepartments();
  pollInterval = setInterval(loadLiveData, 3000);
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
  <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col">
    <!-- Navbar -->
    <Navbar
      :is-simulating="isSimulating"
      @open-department="isModalDeptOpen = true"
      @open-employee="isModalEmpOpen = true"
      @simulate-punch="handleSimulatePunch"
    />

    <!-- Main Content -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 flex-1">
      <!-- Flash Notification -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div
          v-if="notification"
          :class="[
            'px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow border',
            notification.type === 'success'
              ? 'bg-emerald-900/50 border-emerald-500/50 text-emerald-200'
              : 'bg-rose-900/50 border-rose-500/50 text-rose-200'
          ]"
        >
          <div class="flex items-center gap-2">
            <component
              :is="notification.type === 'success' ? CheckCircle2 : AlertCircle"
              class="w-5 h-5"
            />
            <span>{{ notification.message }}</span>
          </div>
          <button
            @click="notification = null"
            class="text-xs opacity-75 hover:opacity-100 cursor-pointer"
          >
            ✕
          </button>
        </div>
      </transition>

      <!-- Stats Cards -->
      <StatsCards :stats="stats" />

      <!-- Device List -->
      <DeviceList
        :devices="devices"
        @assign-device="(dev) => selectedDeviceForAssign = dev"
      />

      <!-- Live Attendance Table -->
      <LiveAttendanceTable :logs="logs" />
    </main>

    <!-- Modals -->
    <ModalDepartment
      :is-open="isModalDeptOpen"
      @close="isModalDeptOpen = false"
      @created="handleCreateDepartment"
    />

    <ModalEmployee
      :is-open="isModalEmpOpen"
      :departments="departments"
      @close="isModalEmpOpen = false"
      @created="handleCreateEmployee"
    />

    <ModalAssignDevice
      :device="selectedDeviceForAssign"
      :departments="departments"
      @close="selectedDeviceForAssign = null"
      @saved="handleAssignDevice"
    />
  </div>
</template>
