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

- **Presente (Verde):** Marcó entrada en horario dentro del margen de tolerancia. Si el agente completó su horario marcando salida en otro edificio ministerial (ej. por una reunión en Sede Central), el estado se muestra como `Presente (Entrada: Depósito | Salida: Centro Cívico)`.
- **Tardanza Permitida (Amarillo):** Entrada registrada dentro de la tolerancia leve (ej. 10 minutos).
- **Tardanza Intolerable (Rojo):** Entrada posterior al límite tolerable (ej. más de 30 minutos).
- **En Licencia (Azul):** Empleado con licencia activa (médica, ordinaria, comisión de servicio).
- **Ausente (Gris):** No registra marcación pasadas las horas de tolerancia sin justificación.

---

## 3. Tableros Específicos por Sector (/sectores/:id/dashboard)

Permite a los encargados de área o personal administrativo visualizar exclusivamente el personal de su dependencia:

- **Con Reloj Conectado:** Muestra marcaciones directas y tasa de presentismo del día.
- **Salidas en Otra Dependencia (Reuniones / Gestiones):** Si un agente de su sector asistió a una reunión o comisión puntual y concluyó su horario en otro edificio ministerial, el tablero lo mostrará con jornada completa, indicando claramente la sede donde se registró la salida (ej: *"Salida en Centro Cívico"*), evitando generar alertas falsas de falta de salida.
- **Alerta de Reloj Desconectado:** Aviso visible si el equipo biométrico perdió conexión con el servidor.
- **Modo Preparación (Sector sin Reloj):** Permite consultar la nómina asignada al sector, sus turnos y sus datos antes de que se instale el reloj físico.

---

## 4. Gestión de Empleados (/usuarios)

### Alta de Empleado
1. Completar: Apellido, Nombre, DNI, Legajo.
2. **PIN del Reloj:** Ingresar el PIN numérico con el que ficha en el equipo físico (4 dígitos actuales o DNI si ya fue homologado).
3. **Datos complementarios (Opcionales):** Sexo y Fecha de Nacimiento (permiten alimentar el cálculo demográfico y proyecciones jubilatorias).
4. **Tipo de Contrato:** Seleccionar `Planta Permanente`, `Portero` o `Contratado`.

### Alertas de Ausentismo Crónico y Tramitación de Bajas
- **Detección Automática:** Cuando un empleado acumula un número configurable de ausencias consecutivas injustificadas (por defecto 5 días sin parte médico ni licencia cargada), el sistema genera una alerta destacada en el panel de Personal.
- **Investigación Administrativa:** El área de personal verifica la situación con el jefe del sector (intimación o verificación de situación).
- **Proceso de Baja:** Si se constata renuncia, jubilación, fallecimiento o abandono de servicio:
  - Se registra la baja en el sistema con su motivo y número de resolución.
  - El sistema archiva al agente de forma segura (`SoftDelete`) manteniendo intacto el historial probatorio de sus marcaciones pasadas y ordenando el borrado automático de su biometría en el reloj físico para liberar espacio.

### Asignación de Turnos y Personalización
- Se puede seleccionar una **Plantilla Estándar** (ej. Mañana 07:00–15:00 o Tarde 13:00–21:00).
- Si el empleado cuenta con un horario diferencial justificado (ej. 08:00 a 16:00 o días rotativos), se especifica en su asignación laboral con el visto bueno de la jefatura.

---

## 5. Operativos Especiales, Banco de Horas y Devolución Semanal (`/operativos`)

### Banco de Horas Compensatorias (Exclusivo de Operativos Especiales)
- **Regla Estricta:** El banco de horas **no se alimenta por permanencia voluntaria** fuera de hora de ningún agente. Quedarse más tiempo en la oficina no genera horas compensatorias. No está permitido trabajar 40 horas una semana para trabajar 20 la siguiente por decisión propia.
- **Único origen habilitado:** Resoluciones oficiales o Memos de la autoridad para **Operativos Especiales** (fines de semana o refuerzos de depósito). Solo estas horas aprobadas se acreditan a favor del agente para futuros francos o liquidación extraordinaria.

### Balance Automático Intra-Semanal (Flexibilidad de 30 hs sin trámites)
Permite gestionar la flexibilidad horaria dentro de los días hábiles ordinarios sin necesidad de cargar solicitudes ni pedir autorizaciones previas en el sistema:
- **Casos de Uso Habituales:**
  - Salir 1 hora antes de lunes a jueves (4 hs adeudadas) y devolverlas completas quedándose 4 horas más el viernes.
  - Salir 1 hora antes 2 días (2 hs adeudadas) y devolverlas juntas en 1 solo día o repartidas en dos días (1 hora cada uno).
- **Cálculo Automático por el Sistema:**
  - El sistema no penaliza los retiros anticipados ni exige trámites burocráticos.
  - Al cierre del día viernes, el motor de cálculo verifica la sumatoria de horas acumuladas en la semana:
    - Si el total alcanza o supera las **30 horas reglamentarias** (o la carga del turno asignado), la semana se computa como **100% cumplida**.
    - Si al finalizar la semana quedan horas adeudadas no devueltas, únicamente ese saldo faltante neto pasa a liquidación como débito horario.

---

## 6. Contingencias Climáticas y Situaciones Extraordinarias

En casos de alertas climáticas (viento Zonda, inundaciones) donde la autoridad ministerial dispone el cese de actividades anticipado:
1. El rol jefe o super_admin registra la **Contingencia Climática** en el sistema para la fecha y horario afectado.
2. El motor de cálculo computa la jornada de los empleados presentes como cumplida al 100%, evitando deducciones indebidas.
