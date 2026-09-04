# Sistema de Control de Asistencia y Servidor ADMS para Relojes Biométricos

Repositorio Oficial: [https://github.com/mineduc-sj-sistemas/reloj.git](https://github.com/mineduc-sj-sistemas/reloj.git)

Servidor de gestión de asistencia multidependencia y recolector en tiempo real para dispositivos biométricos **ZKTeco** (MB20-VL y compatibles con protocolo ADMS/PUSH HTTP).

---

## 🏛️ Arquitectura del Proyecto (Monorepo Desacoplado)

El sistema está dividido en dos capas independientes para garantizar alta disponibilidad:

```text
reloj/
├── backend/            # Servidor Laravel 13 (PHP 8.4) + SQLite (Modo WAL)
│   ├── app/            # Controladores ADMS (ZKTeco) y endpoints API REST
│   ├── routes/         # api.php (para el dashboard) y web.php (/iclock/...)
│   └── database/       # Migraciones y database.sqlite
│
├── frontend/           # SPA en Vue 3 + TypeScript + Vite + Tailwind CSS
│   ├── src/            # Componentes reactivos, tipos TypeScript y servicio API
│   └── vite.config.ts  # Configuración de Vite y proxy local
│
├── package.json        # Orquestación de desarrollo monorepo
└── README.md
```

### ¿Por qué esta arquitectura?
1. **Disponibilidad Continua del Servidor de Relojes:** Aunque el frontend no esté abierto o se esté actualizando, el backend continúa escuchando y guardando las fichadas de los dispositivos en red (`/iclock/cdata`).
2. **Independencia Tecnológica:** El frontend interactúa mediante una API REST estándar (`/api/...`). Si a futuro se decide reemplazar o migrar el backend a Node.js, Go u otro servicio, la interfaz de usuario no requiere modificaciones.
3. **Concurrencia con SQLite:** Configurado con modo `WAL` (Write-Ahead Logging) y `busy_timeout=5000ms`, permitiendo lecturas continuas del frontend sin bloquear las escrituras concurrentes de los relojes.

---

## 🚀 Puesta en Marcha

### Prerrequisitos
- **PHP** >= 8.3 con extensión SQLite3 activa.
- **Composer**
- **Node.js** >= 18 y **npm**

### Instalación Inicial
```bash
# 1. Dependencias del backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# 2. Dependencias del frontend
cd ../frontend
npm install

# 3. Dependencias de la raíz
cd ..
npm install
```

---

## 🖥️ Ejecución de Instancias

### Opción A: Iniciar ambos servicios juntos
Desde la raíz del proyecto:
```bash
npm run dev
```

### Opción B: Iniciar por separado (Recomendado en Producción o Servidor Dedicado)

**1. Backend (Servidor ADMS y API REST):**
```bash
npm run dev:backend
# o manualmente:
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```
> **Nota de red:** El parámetro `--host=0.0.0.0` permite que los dispositivos biométricos de la red local puedan enviar peticiones HTTP al puerto `8000` de este equipo.

**2. Frontend (Panel de Control Vue 3):**
```bash
npm run dev:frontend
# o manualmente:
cd frontend
npm run dev
```
Acceder en el navegador a: `http://localhost:5173`

---

## 📡 Protocolo ZKTeco ADMS (Configuración del Reloj)

En el menú del reloj biométrico ZKTeco MB20-VL:
1. Ir a **Red / Comunicación** > **Servidor en la Nube / Configuración ADMS**.
2. **Dirección del Servidor:** IP de la máquina donde corre el backend (ej. `192.168.1.100`).
3. **Puerto del Servidor:** `8000`
4. **Habilitar Servidor de Dominio:** Desactivado (o activado si usas nombre DNS).
5. Al conectar, el reloj comenzará a realizar peticiones a `/iclock/cdata` y registrará automáticamente las fichadas y el estado online en el dashboard.
