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

## 📅 Hoja de Ruta: Sprints por Nivel de Complejidad

El desarrollo se organiza en **sprints de complejidad incremental**. Cada sprint entrega un incremento de software **100% testeable y validable en campo** antes de pasar al siguiente nivel de sofisticación.

```
[ Sprint 1: Núcleo, Planta Permanente y Sector Único ] ➔ Validación con 1 reloj y personal de planta
       │
       ▼
[ Sprint 2: Multi-Sector e Itinerancia Básica ] ➔ Validación entrada/salida cruzada y auto-match DNI
       │
       ▼
[ Sprint 3: Distribución Biométrica Autorizada y Clave ] ➔ Rostros a técnicos/choferes/porteros de urgencia
       │
       ▼
[ Sprint 4: Análisis e Integración de Porteros ] ➔ Análisis de francos, rotaciones y turnos escolares
       │
       ▼
[ Sprint 5: Análisis e Integración de Contratados ] ➔ Cargas horarias especiales y vencimientos
       │
       ▼
[ Sprint 6: Analítica Demográfica y APIs Externas ] ➔ Proyección jubilatoria, mapa de ausentismo y RENAPER
```

---

### SPRINT 1 — Núcleo, Planta Permanente y Sector Único (MVP Base)

**Objetivo:** Lograr el ciclo completo de control de asistencia para un único sector (ej. Depósito Central), con un solo reloj físico ZKTeco MB20-VL y personal de **Planta Permanente**.

#### 1.1 Base de Datos y Modelos
- `edificios` — id, nombre, direccion, lat, lng
- `areas` — id, edificio_id, nombre
- `sectores` — id, area_id, nombre
- `empleados` — id, nombre, apellido, dni, legajo, pin_reloj (string, nullable, unique), sexo (string, nullable), fecha_nacimiento (date, nullable), tipo_contrato (planta_permanente), activo, foto
- `turnos` — id, nombre, hora_inicio, hora_fin, horas_diarias, horas_semanales, es_plantilla (boolean, default true)
- `asignaciones_laborales` — id, empleado_id, sector_id, turno_id, dias_semana (JSON), fecha_desde, fecha_hasta (nullable), horario_personalizado_inicio (nullable), horario_personalizado_fin (nullable), justificacion_cambio (nullable), activa
- `excepciones_turno` — id, empleado_id, fecha, hora_inicio, hora_fin, justificacion, aprobado_por (user_id)
- `dispositivos` — id, serie, ip, alias, sector_id (nullable), modelo, fabricante, protocolo (adms|sdk), activo, ultimo_heartbeat (timestamp, nullable)
- `marcaciones_brutas` — id, empleado_id (nullable), pin_marcado, dispositivo_id, marcado_en (timestamp), direccion (entrada|salida|desconocida), sincronizado
- `jornadas_calculadas` — id, empleado_id, fecha, entrada_en, salida_en, dispositivo_entrada_id, dispositivo_salida_id, es_itinerante (default false), horas_trabajadas, estado (presente|ausente|tardanza_permitida|tardanza_intolerable|licencia|feriado|justificado)
- `configuraciones` — tolerancias de tardanza (minutos), tolerancia intolerable
- `notificaciones` — alertas del sistema

#### 1.2 Funcionalidad y Pruebas
- Login y roles (`super_admin`, `jefe`, `administrativo`).
- Alta de estructura única (1 Edificio ➔ 1 Área ➔ 1 Sector).
- Alta de empleados de planta permanente con PIN flexible (4 dígitos o DNI).
- Asignación de turnos estándar o con horario individual justificado (ej. 08:00 a 16:00).
- Conexión ADMS con ZKTeco: recepción de transacciones `ATTLOG`.
- **Tablero en vivo del Sector (`/sectores/:id/dashboard`):**
  - Si el reloj está conectado: presencia en tiempo real, entradas, salidas y tardanzas.
  - **Modo Preparación (sin reloj asignado):** Permite organizar y consultar la nómina de empleados antes de instalar el equipo físico, mostrando banner informativo ámbar.
- Cálculo de jornada simple (entrada y salida en el mismo sector) y cálculo de tardanzas.
- **Criterio de Validación:** Probar con reloj real o simulador en un sector con empleados de planta permanente, verificando el cálculo de horas.

---

### SPRINT 2 — Multi-Sector, Personal Itinerante y Auto-Match DNI

**Objetivo:** Soportar múltiples dependencias (ej. Depósito Central y Centro Cívico), empleados que entran en un lugar y salen en otro, y trazabilidad total si se cambian los PINs en los relojes.

#### 2.1 Base de Datos Adicional
- `historial_pins_reloj` — id, empleado_id, pin_anterior, pin_nuevo, origen (`auto_match_dni`|`manual`|`reloj_adms`), cambiado_en, motivo

#### 2.2 Algoritmo de Jornada Multi-Sede (`JornadaService`)
- Consolidación por `(empleado_id, fecha)` unificando marcaciones de distintos dispositivos:
  - Primera marca del día = Entrada (`dispositivo_entrada_id`).
  - Última marca del día = Salida (`dispositivo_salida_id`).
  - Si los edificios difieren: marca `es_itinerante = true`. Las horas se acreditan al sector laboral de origen del agente.
- En el dashboard del sector de origen: figura `Presente (Salida en Sede Central)` sin registrar falta de salida.
- En el dashboard del edificio visitado: figura en solapa secundaria de personal en comisión.

#### 2.3 Auto-Match Inteligente de PIN a DNI
- Si en un reloj se cambia un ID de 4 dígitos a DNI: al recibir la marcación, el sistema busca coincidencia en `empleados.dni`.
- Auto-asocia la marca a la persona, actualiza `pin_reloj = DNI` y guarda el cambio en `historial_pins_reloj`. Responde a: *"¿A qué ID pertenecía antes?"*.
- **Criterio de Validación:** Fichar entrada en reloj A, salida en reloj B, y probar cambio de PIN en el reloj comprobando auto-detección e historial.

---

### SPRINT 3 — Distribución Biométrica Autorizada y Marcación por Clave

**Objetivo:** Permitir que personal móvil (choferes, técnicos, porteros en comisión de urgencia) pueda fichar en relojes donde nunca estuvieron físicamente, sin saturar la memoria de los equipos.

#### 3.1 Base de Datos Adicional
- `empleados`: campos adicionales `permite_marcar_por_clave` (boolean, default false), `password_reloj` (nullable), `alcance_biometrico` (enum: `sector_habitual`|`sector_mas_central`|`red_global`|`comision_temporal`)
- `comisiones_biometricas` — id, empleado_id, sector_destino_id, dispositivo_destino_id, fecha_desde, fecha_hasta, motivo, activo
- `biometria_templates` — id, empleado_id, tipo (rostro|huella), version_algoritmo, template_data, synced_at

#### 3.2 Sincronización Biométrica Push (ADMS)
- Cuando el empleado se enrola en su sede de origen, el servidor almacena su template (`POST /iclock/cdata?table=BIODATA`).
- **Distribución Selectiva y de Urgencia:**
  - **Choferes / Inspectores:** Con `alcance_biometrico = red_global`, sus templates se encolan para todos los relojes ministeriales.
  - **Técnicos de Sistemas:** Templates enviados al Depósito y Centro Cívico.
  - **Porteros de Urgencia / Reemplazos:** Al asignar una comisión temporal a una escuela, el servidor empuja su template al reloj de dicha escuela (`DATA UPDATE BIODATA`). Al expirar la fecha, se encola comando de remoción para liberar memoria física del MB20-VL.
- **Marcación por Clave (Password):**
  - Para casos donde no hay biometría previa ni tiempo de sincronizar, se habilita marcación con PIN + Contraseña numérica en el reloj.
- **Criterio de Validación:** Enrolar usuario en un reloj, autorizar comisión a otro reloj, verificar descarga automática del rostro y fichada exitosa sin enrolamiento presencial.

---

### SPRINT 4 — Análisis y Gestión de Porteros (Maestranza Escolar)

**Objetivo:** Incorporar la lógica y particularidades del personal de portería tras analizar sus requerimientos con las autoridades.

#### 4.1 Relevamiento y Modelado
- Análisis de regímenes de trabajo: guardias, turnos cortados, francos rotativos, eventos escolares extraordinarios.
- Habilitación del perfil `portero` en la interfaz.
- Esquema de reemplazos y asignaciones de emergencia inter-escuelas integrado con el módulo de biometría del Sprint 3.
- **Criterio de Validación:** Configurar porteros con turnos específicos, reemplazos de urgencia y control de jornada escolar.

---

### SPRINT 5 — Análisis y Gestión de Personal Contratado

**Objetivo:** Incorporar y controlar el cumplimiento de contratos de locación y servicios no docentes.

#### 5.1 Relevamiento y Modelado
- Habilitación del perfil `contratado`.
- Control de carga horaria reducida o diferenciada (20h, 30h semanales).
- Gestión de fechas de vencimiento de contrato con alertas preventivas para RRHH.
- Cómputo de horas mensuales acumuladas para certificación de facturación/servicio.
- **Criterio de Validación:** Simulación de contratos de 20h y 30h con alertas de cumplimiento mensual y vencimientos.

---

### SPRINT 6 — Analítica Demográfica, Salud Laboral y APIs Externas

**Objetivo:** Inteligencia de datos, reportes estratégicos e interoperabilidad con el gobierno provincial.

#### 6.1 Módulos Avanzados
- **Alerta Demográfica y Proyección de Jubilaciones:** Reportes cruzando `sexo` y `fecha_nacimiento` (edad dinámica) para proyectar jubilaciones a 6, 12 y 24 meses.
- **Salud Laboral y Ausentismo:** Indexación de licencias médicas, tareas pasivas, ART y mapas de calor estacionales por sector.
- **Interoperabilidad:** Implementación de `PersonaDataProviderInterface` para auto-completar legajos desde APIs provinciales (Ciudadano Digital / RENAPER).
- Exportación formal de reportes a PDF y Excel para liquidaciones.

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
/sectores/:id/dashboard   → Dashboard específico del sector (con o sin reloj)
/usuarios                 → Lista de empleados
/usuarios/nuevo           → Alta de empleado
/usuarios/:id             → Perfil + asignaciones
/turnos                   → ABM de plantillas de turnos y personalizaciones
/licencias                → Gestión de licencias
/configuracion            → Configuración general
/configuracion/feriados   → ABM de feriados
/configuracion/estructura → Edificios → Áreas → Sectores
```

---

## 🎨 Design System

- **Skill activo:** `.agents/skills/design/SKILL.md`
- Paleta institucional estricta: naranja `#FE8204`, rojo `#E43C2F`, amarillo `#FADC3C`, fondo blanco, texto negro.
- Accesibilidad WCAG AA en contrastes y tablas.

---

## 📚 Documentación Continua y Regla de Sprint

- **Skill activo:** `.agents/skills/documentacion/SKILL.md`
- **Regla obligatoria de Sprint:** Al concluir cada fase, sprint o cambio arquitectónico, es mandatorio actualizar:
  1. `doc/arquitectura.md` con las decisiones técnicas y diagramas adoptados.
  2. `doc/procedimientos.md` con los pasos operativos de configuración y despliegue.
  3. `doc/manual_de_usuario.md` con las instrucciones funcionales para los roles de usuario.
  4. Los criterios de aceptación cumplidos en este plan maestro.

---

### ✅ Criterios de Aceptación por Nivel de Complejidad
 
### Sprint 1 — Núcleo, Planta Permanente y Sector Único
- [ ] Login funcional con roles (`super_admin`, `jefe`, `administrativo`).
- [ ] Estructura base (Edificio ➔ Área ➔ Sector) cargada.
- [ ] Alta de empleados de planta permanente con `pin_reloj` (flexible para 4 dígitos o DNI), `sexo` y `fecha_nacimiento` nullable.
- [ ] Plantillas de turnos y asignaciones con horario personalizado justificado.
- [ ] Conexión ADMS con ZKTeco MB20-VL (recepción y guardado de marcaciones en `marcaciones_brutas`).
- [ ] Tablero del sector en vivo (`/sectores/:id/dashboard`) con telemetría online y con banner preventivo en Modo Preparación.
- [ ] Cálculo de jornada simple (entrada/salida en el mismo sector) y tolerancias de tardanza.
 
### Sprint 2 — Multi-Sector e Itinerancia Básica
- [ ] Consolidación multi-sede en `JornadaService` (primera entrada y última salida sin importar el reloj).
- [ ] Atribución de horas al sector formal del empleado con flag `es_itinerante = true`.
- [ ] Tableros de origen y destino reflejan adecuadamente la presencia sin registrar faltas de salida.
- [ ] Auto-match inteligente al cambiar PIN a DNI en el reloj y registro de auditoría en `historial_pins_reloj`.
 
### Sprint 3 — Distribución Biométrica y Clave
- [ ] Almacenamiento centralizado de plantillas biométricas (`biometria_templates`).
- [ ] Distribución selectiva de rostros según `alcance_biometrico` (choferes, técnicos, comisiones).
- [ ] Asignación temporal de comisión para porteros de urgencia y despacho automático de biometría al reloj destino.
- [ ] Soporte de marcación por contraseña/clave en el ZKTeco para usuarios autorizados (`permite_marcar_por_clave`).
 
### Sprint 4 — Porteros (Maestranza Escolar)
- [ ] Relevamiento y formalización de turnos escolares, guardias y rotaciones.
- [ ] Habilitación visual y operativa del perfil `portero`.
- [ ] Flujo validado de suplencias y coberturas de emergencia entre escuelas.
 
### Sprint 5 — Contratados
- [ ] Relevamiento y parametrización de contratos administrativos y de servicio (20h/30h semanales).
- [ ] Habilitación visual y operativa del perfil `contratado`.
- [ ] Alertas preventivas de vencimiento de contrato y cómputo de horas mensuales acumuladas.
 
### Sprint 6 — Analítica Avanzada y Conectividad
- [ ] Proyección automática de jubilaciones por rango etario y género.
- [ ] Mapa de calor de licencias médicas, ART y tareas pasivas.
- [ ] Interfaz de integración con APIs externas (Ciudadano Digital / RENAPER).
- [ ] Exportación de reportes a PDF y Excel.

---

## 📁 Archivos de Referencia

| Archivo | Descripción |
|---------|-------------|
| `.agents/workflows/promt.md` | Requerimientos originales del usuario |
| `.agents/workflows/detalles.md` | Respuestas a preguntas de análisis |
| `.agents/workflows/plan_maestro.md` | Plan maestro vivo del proyecto |
| `.agents/skills/design/SKILL.md` | Design system institucional |
| `.agents/skills/documentacion/SKILL.md` | Estándares de documentación técnica y funcional |
| `doc/arquitectura.md` | Documentación técnica y decisiones de arquitectura |
| `doc/procedimientos.md` | Procedimientos operativos y configuración de equipos |
| `doc/manual_de_usuario.md` | Manual de usuario para operadores y jefatura |

---

*Este documento es el plan maestro vivo del proyecto. Debe actualizarse a medida que se completan fases o cambian requerimientos.*
