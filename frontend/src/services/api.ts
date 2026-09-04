import type { LiveDataResponse, Department, Employee } from '../types';

const API_BASE = import.meta.env.VITE_API_BASE_URL || '/api';

export const api = {
  async getLiveData(): Promise<LiveDataResponse> {
    const res = await fetch(`${API_BASE}/live-data`);
    if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    return res.json();
  },

  async getDepartments(): Promise<Department[]> {
    const res = await fetch(`${API_BASE}/departments`);
    if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    return res.json();
  },

  async createDepartment(data: { name: string; code?: string; legal_instrument?: string; address?: string }) {
    const res = await fetch(`${API_BASE}/departments`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({ message: 'Error al guardar dependencia' }));
      throw new Error(err.message || 'Error al guardar');
    }
    return res.json();
  },

  async getEmployees(): Promise<Employee[]> {
    const res = await fetch(`${API_BASE}/employees`);
    if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    return res.json();
  },

  async saveEmployee(data: { pin: string; name: string; dni?: string; department_id?: number | null }) {
    const res = await fetch(`${API_BASE}/employees`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({ message: 'Error al guardar agente' }));
      throw new Error(err.message || 'Error al guardar');
    }
    return res.json();
  },

  async assignDevice(deviceId: number, data: { alias?: string; department_id?: number | null; location_description?: string }) {
    const res = await fetch(`${API_BASE}/devices/${deviceId}/assign`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({ message: 'Error al asignar lector' }));
      throw new Error(err.message || 'Error al asignar');
    }
    return res.json();
  },

  async simulatePunch() {
    const res = await fetch(`${API_BASE}/simulate-punch`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    });
    if (!res.ok) throw new Error('Error al simular fichada');
    return res.json();
  }
};
