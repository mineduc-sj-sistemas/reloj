export interface DashboardStats {
  total_devices: number;
  online_devices: number;
  total_punches_today: number;
  total_punches_all: number;
  total_employees: number;
  total_departments: number;
}

export interface Device {
  id: number;
  sn: string;
  alias: string | null;
  department_name: string;
  location_description: string | null;
  ip_address: string | null;
  is_online: boolean;
  last_activity: string;
  att_count: number;
}

export interface AttendanceLog {
  id: number;
  device_sn: string;
  device_name: string;
  device_department: string | null;
  user_pin: string;
  employee_name: string;
  employee_department: string | null;
  punch_time: string;
  status_label: string;
  verify_type_label: string;
}

export interface Department {
  id: number;
  name: string;
  code: string | null;
  legal_instrument: string | null;
  address: string | null;
  devices_count?: number;
  employees_count?: number;
}

export interface Employee {
  id: number;
  pin: string;
  name: string;
  dni: string | null;
  department_id: number | null;
  department?: Department | null;
}

export interface LiveDataResponse {
  stats: DashboardStats;
  devices: Device[];
  logs: AttendanceLog[];
}
