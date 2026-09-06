---
description: 
---


1	Autenticación	No se menciona login, roles ni permisos. ¿Quién usa el sistema? ¿Administrador, mesa de ayuda, RRHH?

Este sistema es para la oficina del personal administrativo, controla empleado contratados, estatales, y porteros (no docentes), entonces un rol jefe y  roles administrativos, mas un rol super_admin para contexto del proyecto 


2	Estructura organizacional	Depósito Central / Área de sistemas sugiere jerarquía: Edificio → Área/Sector. ¿Hay niveles intermedios?

        Es que hay un Edifico, un Area y un sector, no hay niveles intermedios

3	Turno	¿Los turnos son fijos (07:00–15:00) o configurables? ¿Se definen por organismo?

        Los turnos los determina la oficina del personal admininistrativos, legalmente es de 07:00 a 15:00 o de 13:00 a 21:00, se elije un turno o otro, pero para pedir modificacion de turno como en mi caso, entro a las 08:00 y salgo a las 16:00 horas para poder llevar a mi hijo, pero hay alguno que van 4 veces en la mañana y 1 uno en la tarde porque ese dia da clases en la mañana, deberia ser fijos, pero no inmutables, que si lo cambia modificar y justificar, otro tema que debo averiguar es que por ejemplo si hay una incontigencia climatica, nos despáchan y no se cumple con la carga horaria, que haya forma de justificar y que se redondee el total laboral del dia,

4	Tolerancia de tardanza	¿Cuántos minutos es "tardanza permitida" vs "intolerable"? ¿Es configurable?

        Desconozco, cuando muestre el mvp les consultare, pero se que hay ciertos criterios, quizas es configurable

5	Feriados/Francos	¿Se manejan feriados nacionales/provinciales? ¿Días franco para porteros?

        Hay feriado nacionles/provinciales, lunes a viernes, no existe dias francos, pero tampoco se si trabajan todos los dias, eso lo debo evaluar,


6	Tipo de contrato	Planta permanente vs contratado vs maestranza — ¿hay más categorías?

        no, pero vamos a manejar primero con planta permanente, despues implementaremos los dos que debo consultar

7	Vista Mapa	¿Es un mapa real (Google Maps/Leaflet) o un diagrama estático de sectores?

        es (Google Maps/Leaflet)  con un punto donde se pueda ver que haya offilne, online, tasa de presencia, ya lo veremos bien

8	Notificaciones offline	¿Email, WhatsApp, solo en-app? ¿A quién notifica?

        En la oficina debe estar atento a eso, ademas de una campanita de notificaciones por si no estan en la vista

9	Exportación	¿Se necesitan reportes PDF/Excel de asistencia?

        Definitivamente 

10	Marcaciones múltiples	Si entro y salgo varias veces en el día, ¿se suman? ¿Se toma solo 1ra entrada y última salida?

        Deberia ser así, debo consultar

11	Licencias, Vacaciones y Francos Compensatorios	¿Cómo se administran las vacaciones por antigüedad y los francos?

        Las vacaciones se calculan según la antigüedad. Al solicitarse, se descuentan del saldo total (pudiendo tomarse todo el bloque o días sueltos/fraccionados). Los días sobrantes se arrastran/acumulan para el próximo año (pendiente confirmar si 1 o 2 años). Los francos compensatorios se van descontando a medida que se ocupan, pero son vigentes exclusivamente dentro del año en curso. Los campos de antigüedad y saldos son 'nullable' para no bloquear la operatividad: cuando se justifique una inasistencia (para que el reloj no compute falta injustificada), en ese momento se carga o completa la ficha del agente. Requiere circuito de solicitud, cálculo de días disponibles y firma/autorización de autoridades.
        REGLA CLAVE DE FERIADOS: Las licencias nunca deben solapar ni descontar días feriados/asuetos. Si en el período solicitado (ej. semana de 5 días) hay un feriado (ej. miércoles), el sistema no le descuenta ese día de la bolsa y le da dos opciones al operador/empleado: o toma 4 días de vacaciones (ahorrando 1 día en su bolsa) o corre el 5° día hábil al siguiente día laborable (ej. lunes siguiente) para que complete los 5 días reales de descanso.
        CANAL DUAL (PRESENCIAL + CIDI): Como CiDi aún no está en producción, no se espera a nadie. El sistema opera de forma 100% autónoma en la oficina de personal (/licencias). Al mismo tiempo, deja expuesta una API REST limpia. Cuando el empleado pida en ventanilla, entra como origen 'oficina_personal'. Cuando en el futuro pidan por CiDi, entrará vía API como origen 'cidi'. Ambas vías usan el mismo motor (LicenciaService) y la misma base de datos.





