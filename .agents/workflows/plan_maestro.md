# Plan Maestro — Sistema de Control de Asistencia
## Ministerio de Educación de San Juan

> **Versión:** 1.0  
> **Fecha:** 2026-09-04  
> **Repositorio:** https://github.com/mineduc-sj-sistemas/reloj  
> **Stack:** Laravel 13 (backend) + Vue 3 + TypeScript + Tailwind CSS (frontend) + SQLite  
> **Design System:** Skill `design` — Paleta institucional naranja `#FE8204`, rojo `#E43C2F`, amarillo `#FADC3C`

---

## 🎯 Objetivo

Sistema web de control de asistencia para empleados no docentes del Ministerio de Educación de San Juan, integrado con relojes ZKTeco MB20-VL mediante protocolo ADMS. Permite registrar entradas/salidas, gestionar turnos, calcular jornadas, manejar licencias y generar reportes.

---

## 👥 Roles del Sistema

| Rol | Descripción | Permisos clave |
|-----|-------------|---------------|
| `super_admin` | Administrador técnico del proyecto | Todo, incluyendo config de dispositivos y deploy |
| `jefe` | Jefatura de la oficina de personal | Aprobar modificaciones de turno, licencias, ver reportes |
| `administrativo` | Operador del sistema | Gestión de empleados, asignaciones, reportes básicos |

---

## 🏗️ Estructura Organizacional

```
Edificio (ej: "Depósito Central", "Centro Cívico", "Establecimiento X")
  └── Área (ej: "Sistemas", "Depósito", "Administración")
        └── Sector (ej: "Gabinete Técnicos", "Choferes", "Oficina Despacho")
```

Un empleado tiene una **asignación laboral activa** que lo vincula a un Sector + Turno + Días de la semana.  
El reloj físico marca el **edificio**, pero el sistema sabe a qué sector pertenece el empleado ese día.

---

## 📅 Fases de Desarrollo

---

### FASE 1 — Infraestructura y Base de Datos (MVP Core)

**Objetivo:** Migraciones, modelos, seeders de referencia, autenticación.

#### Migraciones (orden de dependencias)

1. `edificios` — id, nombre, direccion, lat, lng
2. `areas` — id, edificio_id, nombre
3. `sectores` — id, area_id, nombre
4. `empleados` — id, nombre, apellido, dni, legajo, tipo_contrato (planta|contratado|maestranza), activo, foto
5. `turnos` — id, nombre, hora_inicio, hora_fin, horas_diarias (6|7|8), horas_semanales (20|30)
6. `asignaciones_laborales` — id, empleado_id, sector_id, turno_id, dias_semana (JSON), fecha_desde, fecha_hasta (nullable), activa
7. `excepciones_turno` — id, empleado_id, fecha, hora_inicio, hora_fin, justificacion, aprobado_por (user_id)
8. `licencias` — id, empleado_id, tipo (vacaciones|enfermedad|cambio_tarea|contingencia|otra), fecha_desde, fecha_hasta, justificacion, estado (pendiente|aprobada|rechazada), aprobado_por
9. `dispositivos` — id, serie, ip, alias, sector_id (nullable), modelo, fabricante, protocolo (adms|sdk), activo
10. `marcaciones_brutas` — id, empleado_id, dispositivo_id, marcado_en (timestamp), direccion (entrada|salida|desconocida), sincronizado
11. `jornadas_calculadas` — id, empleado_id, fecha, entrada_en, salida_en, horas_trabajadas, estado (presente|ausente|tardanza_permitida|tardanza_intolerable|licencia|feriado|justificado)
12. `configuraciones` — id, clave, valor, descripcion (ej: tolerancia_tardanza_minutos, tolerancia_tardanza_intolerable_minutos)
13. `feriados` — id, fecha, nombre, tipo (nacional|provincial)
14. `notificaciones` — id, user_id, tipo, mensaje, leida, datos (JSON), created_at

#### Modelos Eloquent
- `Edificio`, `Area`, `Sector`
- `Empleado` (con relaciones: asignaciones, marcaciones, jornadas, licencias)
- `Turno`, `AsignacionLaboral`, `ExcepcionTurno`
- `Licencia`
- `Dispositivo`
- `MarcacionBruta`, `JornadaCalculada`
- `Configuracion` (helper estático `Configuracion::get('clave')`)
- `Feriado`
- `Notificacion`

#### Autenticación
- Laravel Breeze (API mode) + Sanctum
- Spatie Laravel-Permission para roles (`super_admin`, `jefe`, `administrativo`)
- Middleware de rol en rutas API

---

### FASE 2 — Vista de Relojes (Dispositivos)

**Ruta Vue:** `/relojes`

#### 2.1 Dashboard de Relojes
- Tarjetas por dispositivo: alias, sector asignado, estado (online/offline), última comunicación, IP, modelo
- Indicador de tasa de marcaciones del día
- Botón "Ver detalles" → panel lateral o modal

#### 2.2 Alta / Edición de Reloj
- Campos: Número de Serie, IP, Alias, Edificio → Área → Sector (cascada), Modelo, Protocolo
- Validación: serie única, IP válida
- MVP: soporte ZKTeco ADMS (MB20-VL); estructura para agregar drivers de otros modelos

#### 2.3 Vista Mapa (Leaflet.js)
- Marcador por edificio (lat/lng desde `edificios`)
- Color del marcador: verde (todos online), naranja (alguno offline), rojo (todos offline)
- Popup: nombre del edificio, lista de relojes con estado, tasa de presencia del día

#### 2.4 Sistema de Alertas Offline
- Backend: `CheckDevicesStatusJob` (schedule cada 5 min)
  - Si un reloj no hace heartbeat en >10 min → crea notificación tipo `dispositivo_offline`
- Frontend: campanita con badge de no leídas, polling cada 30s a `/api/notificaciones`
- Toast al recibir nueva notificación offline

#### 2.5 Revisión del Heartbeat ADMS
- Intervalo actual: ~30s (configurable desde el reloj físico)
- Recomendación MVP con pocos relojes: mantener 30s
- Si escalan a >10 relojes: subir a 60s desde el panel del dispositivo o via comando ADMS `SETPARA`

---

### FASE 3 — Vista de Usuarios (Empleados)

**Ruta Vue:** `/usuarios`

#### 3.1 Lista de Empleados
- Tabla con: foto, nombre, DNI, legajo, tipo contrato, sector actual, turno actual, estado (activo/inactivo)
- Filtros: edificio, área, sector, tipo contrato, activo
- Búsqueda por nombre/DNI/legajo
- Badge naranja si el empleado tiene datos incompletos (sin turno, sin sector)
- Exportar Excel / PDF

#### 3.2 Detección de Empleados Nuevos
- Al recibir una marcación de un PIN desconocido vía ADMS → crear empleado con `activo=false` y datos mínimos
- Notificación interna: "Nuevo empleado sin datos — PIN: XXXX"
- La vista de lista muestra un filtro/badge "Pendientes de completar"

#### 3.3 Perfil / Alta / Edición de Empleado
- Datos personales: nombre, apellido, DNI, legajo, foto, tipo contrato, fecha ingreso
- **Asignaciones laborales** (historial): sector, turno, días activos, fecha desde/hasta
  - Puede tener varias asignaciones históricas; solo una activa a la vez
  - Puede cambiar de sector/turno con justificación
- **Excepciones de turno**: días puntuales con horario diferente
- **Historial de marcaciones** (últimos 30 días): tabla con entrada/salida/horas/estado

#### 3.4 Gestión de Turnos
**Ruta:** `/turnos`
- ABM de turnos: nombre, hora inicio, hora fin, horas diarias, horas semanales
- Turno estándar A: 07:00–15:00 / 8h / 30h  
- Turno estándar B: 13:00–21:00 / 8h / 30h
- Turnos modificados: se crean con aprobación del jefe

---

### FASE 4 — Asistencia y Jornadas

**Ruta Vue:** `/asistencia`

#### 4.1 Dashboard Diario (Live)
- Listado de todos los empleados del día actual con estado en tiempo real
- Estados: `Presente`, `Ausente`, `Tardanza`, `En Licencia`, `Feriado`
- Hora de entrada registrada, hora estimada de salida
- Filtro por edificio/área/sector
- Actualización cada 3s (polling existente en `/api/live-data`)

#### 4.2 Cálculo de Jornada
- Lógica en `JornadaService`:
  - Tomar **primera marcación** del día como entrada
  - Tomar **última marcación** del día como salida (a confirmar con usuarios)
  - Calcular `horas_trabajadas = salida - entrada`
  - Comparar con turno del empleado ese día (con excepciones si aplica)
  - Determinar estado: presente, tardanza permitida, tardanza intolerable
  - Considerar licencias activas antes de marcar ausente
  - Considerar feriados del calendario

#### 4.3 Gestión de Tardanza
- Configuración global (tabla `configuraciones`):
  - `tolerancia_tardanza_minutos`: ej. 10 min → tardanza permitida
  - `tolerancia_tardanza_intolerable_minutos`: ej. 30 min → tardanza intolerable
- Mostrar en dashboard con colores: amarillo = permitida, rojo = intolerable

#### 4.4 Licencias
**Ruta:** `/licencias`
- ABM de licencias por empleado
- Tipos: vacaciones, enfermedad, cambio de tarea, contingencia climática, otra
- Estado: pendiente → aprobada / rechazada (por `jefe`)
- Al aprobar: afecta el cálculo de jornada para esas fechas
- Contingencia climática: redondea la jornada al 100% (horas_trabajadas = horas_turno)

#### 4.5 Feriados
**Ruta:** `/configuracion/feriados`
- ABM de feriados: fecha, nombre, tipo (nacional/provincial)
- Importación anual (CSV o manual)

---

### FASE 5 — Reportes y Exportación

**Ruta Vue:** `/reportes`

#### 5.1 Reporte de Asistencia Mensual
- Por empleado o por sector
- Columnas: día, entrada, salida, horas trabajadas, estado, observaciones
- Filtros: mes, empleado, edificio/área/sector, tipo contrato
- Exportar a **Excel** (Laravel Excel / PhpSpreadsheet)
- Exportar a **PDF** (DomPDF / Laravel Snappy)

#### 5.2 Reporte de Tardanzas
- Listado de empleados con tardanzas en un período
- Clasificado por: permitidas vs intolerables

#### 5.3 Reporte de Ausentismo
- Empleados con ausencias injustificadas en un período

#### 5.4 Reporte de Dispositivos
- Historial de conectividad por reloj
- Tasa de disponibilidad

---

### FASE 6 — Infraestructura de Soporte

#### 6.1 Sistema Multi-Driver de Dispositivos
- Interfaz/contrato `DeviceDriver`:
  - `sendCommand(Dispositivo $device, string $command, array $params): bool`
  - `syncEmployees(Dispositivo $device, Collection $empleados): bool`
  - `getStatus(Dispositivo $device): DeviceStatus`
- Implementaciones:
  - `AdmsDriver` (ZKTeco MB20-VL — ya implementado)
  - `SdkDriver` (placeholder para futuros modelos)
- Registro en `DeviceDriverFactory` por modelo/protocolo

#### 6.2 Jobs y Schedule
| Job | Frecuencia | Propósito |
|-----|-----------|-----------|
| `CheckDevicesStatusJob` | Cada 5 min | Detecta relojes offline → notificación |
| `CalculateJornadasJob` | Diario 23:59 | Cierra y calcula jornadas del día |
| `SyncFeriadosJob` | Anual (1 ene) | Importar feriados del año siguiente |

#### 6.3 Notificaciones In-App
- Modelo `Notificacion` con tipos: `dispositivo_offline`, `empleado_nuevo`, `licencia_pendiente`, `turno_modificado`
- Endpoint: `GET /api/notificaciones?no_leidas=true`
- Frontend: campanita con badge, dropdown de notificaciones, marcar como leída
- Toast automático para nuevas notificaciones críticas

---

## 🗺️ Rutas del Sistema (Vue Router)

```
/                         → Redirect a /asistencia
/login                    → Autenticación
/asistencia               → Dashboard diario (live)
/asistencia/reportes      → Reportes históricos
/relojes                  → Dashboard de dispositivos
/relojes/nuevo            → Alta de reloj
/relojes/:id              → Detalle / edición de reloj
/relojes/mapa             → Mapa de ubicaciones (Leaflet)
/usuarios                 → Lista de empleados
/usuarios/nuevo           → Alta de empleado
/usuarios/:id             → Perfil + asignaciones
/turnos                   → ABM de turnos
/licencias                → Gestión de licencias
/configuracion            → Configuración general
/configuracion/feriados   → ABM de feriados
/configuracion/estructura → Edificios → Áreas → Sectores
```

---

## 🎨 Design System

- **Skill activo:** `.agents/skills/design/SKILL.md`
- Paleta estricta: naranja `#FE8204`, rojo `#E43C2F`, amarillo `#FADC3C`, fondo blanco, texto negro
- Thead tablas: `bg-brand-orange text-white uppercase font-black text-xs`
- Botón primario: `bg-brand-orange text-white`
- Botón peligro: `bg-brand-red text-white`
- Sin colores genéricos grises como primarios; WCAG AA obligatorio

---

## ✅ Criterios de Aceptación MVP

- [ ] Login funcional con 3 roles
- [ ] Alta de edificios, áreas, sectores
- [ ] Alta de empleados planta permanente con asignación a sector y turno
- [ ] El reloj ZKTeco envía marcaciones y se almacenan correctamente
- [ ] Dashboard live muestra estado de empleados en tiempo real
- [ ] Cálculo de jornada diaria (entrada/salida/horas)
- [ ] Estados de asistencia: presente, ausente, tardanza, licencia, feriado
- [ ] Vista de relojes con estado online/offline
- [ ] Alta de reloj asociada a sector
- [ ] Alertas in-app cuando un reloj se desconecta
- [ ] Reporte mensual exportable a Excel
- [ ] Mapa con marcadores de edificios

## ✅ Criterios de Aceptación Post-MVP

- [ ] Exportación PDF
- [ ] Gestión de licencias con flujo de aprobación
- [ ] Excepciones de turno por día
- [ ] Soporte multi-driver para otros modelos ZKTeco
- [ ] Tipos de contrato: contratado y maestranza
- [ ] Feriados nacionales/provinciales con importación
- [ ] Reportes de tardanza y ausentismo

---

## 📁 Archivos de Referencia

| Archivo | Descripción |
|---------|-------------|
| `.agents/workflows/promt.md` | Requerimientos originales del usuario |
| `.agents/workflows/detalles.md` | Respuestas a preguntas de análisis |
| `.agents/skills/design/SKILL.md` | Design system institucional |
| `.agents/skills/laravel-best-practices/SKILL.md` | Patrones Laravel |
| `.agents/skills/vue-best-practices/SKILL.md` | Patrones Vue 3 |
| `.agents/skills/laravel-security/SKILL.md` | Seguridad backend |

---

*Este documento es el plan maestro vivo del proyecto. Debe actualizarse a medida que se completan fases o cambian requerimientos.*
