# Procedimientos Operativos — Sistema de Control de Asistencia
## Ministerio de Educación de San Juan

Guía de procedimientos técnicos y de administración para el alta, configuración física, vinculación de dispositivos y migración de identificadores.

---

## 1. Configuración de Reloj ZKTeco MB20-VL (Protocolo ADMS)

Para que el dispositivo envíe marcaciones al sistema central, debe configurarse en modo ADMS (Servidor en la Nube):

1. **Acceso al Menú:** Presionar tecla M/OK en el reloj e identificarse como Administrador.
2. **Configuración de Red (Ethernet / Wi-Fi):**
   - Asignar IP fija o verificar arrendamiento DHCP en el segmento del Ministerio.
   - Configurar Máscara de subred y Gateway corporativo.
   - Configurar DNS institucional.
3. **Configuración del Servidor Cloud (ADMS / Servidor Web):**
   - **Dirección del Servidor:** IP o dominio del servidor Laravel (ej. 10.X.X.X o sistencia.educacion.sanjuan.gov.ar).
   - **Puerto del Servidor:** Puerto HTTP del servicio (ej. 80 o 8000).
   - **Habilitar Nombre de Dominio:** Desactivado si se usa IP directa, activado si se usa FQDN.
   - **Ruta de Servicio (Proxy/Path):** /iclock/
4. **Verificación de Enlace:** Reiniciar el equipo. Al iniciar, el icono de mundo/red en la pantalla del reloj debe ponerse en color verde o azul (conectado).

---

## 2. Procedimiento de Alta y Vinculación de un Reloj a un Sector

1. Ingresar al sistema con rol super_admin.
2. Dirigirse a **Relojes ➔ Nuevo Reloj** (/relojes/nuevo).
3. Completar los datos requeridos:
   - **Número de Serie (SN):** Exacto al que figura en la etiqueta del dispositivo (clave primaria de autenticación ADMS).
   - **Alias:** Nombre descriptivo (ej.  Reloj Depósito Puerta Principal).
   - **Ubicación:** Seleccionar Edificio ➔ Área ➔ Sector en cascada.
4. Al guardar:
   - El sistema vincula el reloj al Sector seleccionado.
   - Si el sector ya cuenta con empleados cargados con pin_reloj, el sistema programa el comando de sincronización de usuarios hacia el equipo.
   - El dashboard del sector (/sectores/:id/dashboard) pasa automáticamente de **Modo Preparación** a **Estado Operativo**.

---

## 3. Manejo de Sectores sin Dispositivo (Modo Preparación)

Para dependencias donde aún no se ha instalado físicamente el reloj:

1. El operador puede crear el Edificio, Área y Sector normalmente.
2. Cargar los empleados y asignarles sus turnos o plantillas horarias.
3. El tablero de ese sector indicará:
   > ⚠️ **Sector sin reloj biométrico vinculado — No se están registrando marcaciones automáticas.**
4. El personal de RRHH puede verificar asignaciones y legajos. Una vez instalado el reloj físico, simplemente se asocia y el sector entra en régimen de control en tiempo real.

---

## 4. Transición, Migración de Identificadores y Trazabilidad (PIN a DNI)

- **Situación Inicial:** Empleados que ya cuentan con un PIN numérico histórico de 4 dígitos en el reloj físico deben cargarse con ese mismo número en el campo `pin_reloj` para que el sistema reconozca sus marcaciones.
- **Auto-Match Inteligente:** Si en el reloj físico un técnico o administrador edita el usuario y le asigna su número de DNI:
  1. Al recibir la primera marcación, el backend detecta automáticamente que el nuevo PIN coincide con el `dni` del empleado.
  2. El sistema auto-asocia la marcación a dicho empleado sin cortar su historial de asistencia ni generar usuarios huérfanos.
  3. Actualiza el `pin_reloj` activo al DNI y almacena el evento en la tabla `historial_pins_reloj`.
- **Trazabilidad y Auditoría ("¿Qué ID usaba antes?"):**
  - Cada vez que un PIN cambia, el sistema registra: `pin_anterior` (ej. `4444`), `pin_nuevo` (ej. `28891983`), fecha y origen del cambio.
  - La oficina de personal puede consultar en el perfil del empleado todo el historial de IDs utilizados en los distintos dispositivos a lo largo del tiempo.
## 5. Distribución Biométrica Selectiva y Comisiones de Urgencia

Para evitar saturar la memoria de los relojes ZKTeco MB20-VL (límite de 500 a 1000 rostros) y mantener la seguridad:

1. **Personal con Sede Fija (Administrativos/Planta):**
   - Su biometría se envía únicamente al/los reloj/es de su sector de asignación.
2. **Personal Móvil Homologado (Choferes / Inspectores / Técnicos de Sistemas):**
   - En su ficha se asigna `alcance_biometrico = red_global` o `sector_mas_central`.
   - El sistema encola automáticamente la distribución de su plantilla biométrica a los equipos correspondientes.
## 6. Procedimiento de Baja de Personal y Des-enrolamiento en Relojes

Cuando un agente cesa en sus funciones (renuncia, fallecimiento, jubilación, fin de contrato o abandono de servicio):

1. **Gestión en el Sistema:**
   - El operador con rol `jefe` o `super_admin` accede a la ficha del agente en `/usuarios/:id`.
   - Selecciona **"Dar de Baja"** e ingresa:
     - **Motivo:** Renuncia, Fallecimiento, Jubilación, Abandono de Cargo, Cese de Contrato u Otro.
     - **Fecha de Baja:** Fecha efectiva del cese.
     - **Nº de Resolución / Expediente:** Referencia administrativa formal.
2. **Consecuencias Técnicas Inmediatas:**
   - **En Base de Datos:** Se aplica `SoftDeletes` (`deleted_at`). El agente queda excluido de los tableros de asistencia y nóminas activas, pero sus registros pasados quedan protegidos para fines legales y sumarios.
   - **En los Relojes Físicos:** El sistema localiza los dispositivos donde el agente tenía presencia (sector habitual o comisiones activas) y encola el comando ADMS:
     `DATA DELETE USER PIN=[pin_o_dni]`
   - En la próxima sincronización del equipo biométrico, el usuario, huellas y rostros se eliminan físicamente del aparato, impidiendo marcaciones no autorizadas y recuperando espacio en memoria.
