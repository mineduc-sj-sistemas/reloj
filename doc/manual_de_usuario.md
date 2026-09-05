# Manual de Usuario — Sistema de Control de Asistencia
## Ministerio de Educación de San Juan

Guía de uso del sistema según los roles operativos de la oficina de personal.

---

## 1. Roles del Sistema y Permisos

| Rol | Alcance | Funciones Principales |
|---|---|---|
| super_admin | Configuración Global | Gestión de edificios, áreas, sectores, configuración de tolerancias y relojes biométricos. |
| jefe | Jefatura de Personal | Aprobación de excepciones de turno, licencias médicas, contingencias climáticas y reportes consolidados. |
|  dministrativo | Operación Diaria | Carga de empleados, asignación de turnos, monitoreo del tablero en vivo y carga de justificaciones. |

---

## 2. Tablero de Asistencia en Tiempo Real (/asistencia)

El tablero principal permite monitorear el estado del personal durante la jornada:

- **Presente (Verde):** Marcó entrada en horario dentro del margen de tolerancia.
- **Presente / Itinerante (Azul verdoso):** Empleado que registró entrada en un edificio y salida en otra sede ministerial (ej. entrada en Depósito y salida en Centro Cívico).
- **Tardanza Permitida (Amarillo):** Entrada registrada dentro de la tolerancia leve (ej. 10 minutos).
- **Tardanza Intolerable (Rojo):** Entrada posterior al límite tolerable (ej. más de 30 minutos).
- **En Licencia (Azul):** Empleado con licencia activa (médica, ordinaria, comisión de servicio).
- **Ausente (Gris):** No registra marcación pasadas las horas de tolerancia sin justificación.

---

## 3. Tableros Específicos por Sector (/sectores/:id/dashboard)

Permite a los encargados de área o personal administrativo visualizar exclusivamente el personal de su dependencia:

- **Con Reloj Conectado:** Muestra marcaciones directas y tasa de presentismo del día.
- **Personal en Comisión / Itinerante:** Si un agente de su sector concluyó su horario en otro edificio ministerial, el tablero lo mostrará con jornada completa, indicando: *"Salida registrada en Sede Central"*, evitando generar alertas falsas de falta de salida.
- **Alerta de Reloj Desconectado:** Aviso visible si el equipo biométrico perdió conexión con el servidor.
- **Modo Preparación (Sector sin Reloj):** Permite consultar la nómina asignada al sector, sus turnos y sus datos antes de que se instale el reloj físico.

---

## 4. Gestión de Empleados (/usuarios)

### Alta de Empleado
1. Completar: Apellido, Nombre, DNI, Legajo.
2. **PIN del Reloj:** Ingresar el PIN numérico con el que ficha en el equipo físico (4 dígitos actuales o DNI si ya fue homologado).
3. **Datos complementarios (Opcionales):** Sexo y Fecha de Nacimiento (permiten alimentar el cálculo demográfico y proyecciones jubilatorias).
4. **Tipo de Contrato:** Seleccionar Planta Permanente, Portero o Contratado.

### Asignación de Turnos y Personalización
- Se puede seleccionar una **Plantilla Estándar** (ej. Mañana 07:00–15:00 o Tarde 13:00–21:00).
- Si el empleado cuenta con un horario diferencial justificado (ej. 08:00 a 16:00 o días rotativos), se especifica en su asignación laboral con el visto bueno de la jefatura.

---

## 5. Contingencias Climáticas y Situaciones Extraordinarias

En casos de alertas climáticas (viento Zonda, inundaciones) donde la autoridad ministerial dispone el cese de actividades anticipado:
1. El rol jefe o super_admin registra la **Contingencia Climática** en el sistema para la fecha y horario afectado.
2. El motor de cálculo computa la jornada de los empleados presentes como cumplida al 100%, evitando deducciones indebidas.
