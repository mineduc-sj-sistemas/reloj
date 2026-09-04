import { ref, shallowRef, onMounted, onUnmounted } from 'vue';
import type { DashboardStats, Device, AttendanceLog, Department } from '../types';
import { api } from '../services/api';

export interface NotificationState {
  message: string;
  type: 'success' | 'error';
}

export function useRelojDashboard() {
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

  // Primitives using shallowRef as recommended by vue-best-practices
  const isSimulating = shallowRef(false);
  const isLoading = shallowRef(true);
  const notification = shallowRef<NotificationState | null>(null);

  // Modals visibility state (primitives)
  const isModalDeptOpen = shallowRef(false);
  const isModalEmpOpen = shallowRef(false);
  const selectedDeviceForAssign = shallowRef<Device | null>(null);

  let pollInterval: ReturnType<typeof setInterval> | null = null;
  let notificationTimer: ReturnType<typeof setTimeout> | null = null;

  function notify(message: string, type: 'success' | 'error' = 'success') {
    notification.value = { message, type };
    if (notificationTimer) clearTimeout(notificationTimer);
    notificationTimer = setTimeout(() => {
      notification.value = null;
    }, 4000);
  }

  function clearNotification() {
    notification.value = null;
    if (notificationTimer) clearTimeout(notificationTimer);
  }

  async function loadLiveData() {
    try {
      const data = await api.getLiveData();
      stats.value = data.stats;
      devices.value = data.devices;
      logs.value = data.logs;
    } catch (err) {
      console.error('Error fetching live data:', err);
    } finally {
      isLoading.value = false;
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
      notify(res.message || 'Fichada biométrica simulada con éxito');
      await loadLiveData();
    } catch (err) {
      notify('Error al simular fichada: ' + err, 'error');
    } finally {
      isSimulating.value = false;
    }
  }

  async function handleCreateDepartment(data: {
    name: string;
    code?: string;
    legal_instrument?: string;
    address?: string;
  }) {
    try {
      const res = await api.createDepartment(data);
      notify(res.message || 'Dependencia creada correctamente');
      isModalDeptOpen.value = false;
      await Promise.all([loadDepartments(), loadLiveData()]);
    } catch (err: any) {
      notify(err.message || 'Error al guardar dependencia', 'error');
    }
  }

  async function handleCreateEmployee(data: {
    pin: string;
    name: string;
    dni?: string;
    department_id?: number | null;
  }) {
    try {
      const res = await api.saveEmployee(data);
      notify(res.message || 'Agente guardado correctamente');
      isModalEmpOpen.value = false;
      await loadLiveData();
    } catch (err: any) {
      notify(err.message || 'Error al guardar agente', 'error');
    }
  }

  async function handleAssignDevice(data: {
    deviceId: number;
    alias?: string;
    department_id?: number | null;
    location_description?: string;
  }) {
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
    if (notificationTimer) clearTimeout(notificationTimer);
  });

  return {
    // State
    stats,
    devices,
    logs,
    departments,
    isSimulating,
    isLoading,
    notification,
    isModalDeptOpen,
    isModalEmpOpen,
    selectedDeviceForAssign,

    // Actions
    notify,
    clearNotification,
    loadLiveData,
    loadDepartments,
    handleSimulatePunch,
    handleCreateDepartment,
    handleCreateEmployee,
    handleAssignDevice,
  };
}
