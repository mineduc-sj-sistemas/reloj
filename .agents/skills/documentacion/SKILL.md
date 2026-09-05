---
name: documentacion
description: Estándares y reglas obligatorias para mantener actualizada la documentación viva del proyecto en /doc (arquitectura.md, procedimientos.md, manual_de_usuario.md) en cada sprint o cambio técnico.
---

# Skill: Documentación Continua del Sistema

Este skill rige el mantenimiento, redacción y actualización de la documentación técnica y operativa del **Sistema de Control de Asistencia** del Ministerio de Educación de San Juan.

## 🎯 Objetivo

Garantizar que el código, la infraestructura y los procedimientos operativos estén sincronizados en todo momento con los tres documentos de referencia en d:/reloj/doc/:
1. doc/arquitectura.md (Decisiones técnicas, modelos, base de datos, modularidad, integraciones)
2. doc/procedimientos.md (Procedimientos de configuración física/red de relojes ZKTeco, enrolamiento, contingencias)
3. doc/manual_de_usuario.md (Guía operativa por rol: super_admin, jefe, dministrativo)

---

## 🚨 Regla Obligatoria de Sprint

**Todo agente o desarrollador que culmine una fase, tarea o sprint DEBE:**
1. **Actualizar el archivo correspondiente en /doc:**
   - Si cambió backend, BD o frontend: actualizar doc/arquitectura.md.
   - Si cambió la forma de configurar dispositivos o sincronizar personal: actualizar doc/procedimientos.md.
   - Si cambió la experiencia de usuario, permisos o pantallas: actualizar doc/manual_de_usuario.md.
2. **Actualizar plan_maestro.md:** Marcar los criterios de aceptación completados ([x]).
3. **Formato:** Mantener español claro, estructurado con Markdown estándar, diagramas Mermaid cuando aplique y bloques de alerta GFM.

---

## 📂 Responsabilidades por Documento

### 1. doc/arquitectura.md
- Diagrama de módulos backend y frontend.
- Esquema de base de datos y relaciones Eloquent.
- Flujo de comunicación ADMS (heartbeats, envío de transacciones ATTLOG, comandos push DATA USER).
- Principios de modularidad y contratos (interfaces para drivers de dispositivos y proveedores de datos personales).
- Convenciones de rutas API y eventos.

### 2. doc/procedimientos.md
- Paso a paso para configurar un ZKTeco MB20-VL (parámetros de servidor ADMS: IP, puerto, path /iclock/).
- Procedimiento para asociar un reloj a un Sector y sincronización de PINs de empleados.
- Migración gradual de identificadores: de PIN de 4 dígitos a DNI.
- Protocolo ante fallas: diagnóstico de reloj offline y modo contingencia en sectores sin dispositivo.

### 3. doc/manual_de_usuario.md
- Guía para el rol dministrativo: alta de empleados, asignación a sectores/turnos, justificación de tardanzas.
- Guía para el rol jefe: aprobación de excepciones de turnos, licencias médicas y contingencias climáticas.
- Guía para el rol super_admin: gestión de edificios, áreas, sectores, auditoría y configuración de tolerancias.
- Interpretación del tablero diario (estados: Presente, Ausente, Tardanza, Licencia, Modo Preparación).
