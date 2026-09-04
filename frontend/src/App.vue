<script setup lang="ts">
import { useRelojDashboard } from './composables/useRelojDashboard';

import Navbar from './components/Navbar.vue';
import StatsCards from './components/StatsCards.vue';
import DeviceList from './components/DeviceList.vue';
import LiveAttendanceTable from './components/LiveAttendanceTable.vue';
import ModalDepartment from './components/ModalDepartment.vue';
import ModalEmployee from './components/ModalEmployee.vue';
import ModalAssignDevice from './components/ModalAssignDevice.vue';
import { CheckCircle2, AlertCircle } from 'lucide-vue-next';

const {
  stats,
  devices,
  logs,
  departments,
  isSimulating,
  notification,
  clearNotification,
  isModalDeptOpen,
  isModalEmpOpen,
  selectedDeviceForAssign,
  handleSimulatePunch,
  handleCreateDepartment,
  handleCreateEmployee,
  handleAssignDevice,
} = useRelojDashboard();
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
      <Transition
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
            @click="clearNotification"
            class="text-xs opacity-75 hover:opacity-100 cursor-pointer"
          >
            ✕
          </button>
        </div>
      </Transition>

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
