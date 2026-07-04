# Acalis Pharma Web

**Sistema de gestión farmacéutica institucional** para residencias de larga estadía (asilos) en Chile. Desarrollado por **IntegralServices Spa**.

| | |
|---|---|
| **Producto** | ACALIS-PHARMA |
| **Stack** | Laravel 13 · PHP 8.3+ · Livewire 4 · Tailwind/DaisyUI · Chart.js · SQLite (dev) |
| **CTO** | Mauricio Durán Torres — IntegralServices Spa |
| **Contacto** | [mauriciodurant@gmail.com](mailto:mauriciodurant@gmail.com) |

---

## Índice

1. [Contexto y objetivo](#1-contexto-y-objetivo)
2. [Qué hace el sistema](#2-qué-hace-el-sistema)
3. [Requisitos previos](#3-requisitos-previos)
4. [Cómo levantar el sistema](#4-cómo-levantar-el-sistema)
5. [Usuarios demo](#5-usuarios-demo)
6. [Roles y permisos](#6-roles-y-permisos)
7. [Notificaciones y eventos](#7-notificaciones-y-eventos)
8. [Módulos — flujo completo](#8-módulos--flujo-completo)
9. [Diccionario HTTP y verbos](#9-diccionario-http-y-verbos)
10. [Auditoría y seguridad](#10-auditoría-y-seguridad)
11. [Tests](#11-tests)
12. [Interfaz, idioma y UX](#12-interfaz-idioma-y-ux)
13. [Documentación complementaria (local)](#13-documentación-complementaria-local)

---

## 1. Contexto y objetivo

Las residencias de adultos mayores deben garantizar **trazabilidad farmacéutica** desde el ingreso de medicamentos hasta su administración o baja, cumpliendo normativa de fármacos controlados y protección de datos personales (Ley N° 21.719).

**Acalis Pharma** centraliza en un solo entorno:

- Inventario por bodega y lote con vencimientos.
- Movimientos con responsable identificado.
- Fichas de residentes e historial de medicación.
- Reportes operacionales y gerenciales.
- Alertas en pantalla y por correo.
- Controles de acceso por rol y registro de auditoría.

**Usuarios objetivo:** TENS, enfermería jefe, dirección médica y administración del sistema.

**Contexto regional:** español Chile (`APP_LOCALE=es_CL`), zona horaria `America/Santiago`, montos en CLP, interfaz y paginación en español.

---

## 2. Qué hace el sistema

| Módulo | Función principal |
|--------|-------------------|
| **Acceso** | Login, términos, activación OTP, política de sesión, sesión única |
| **Dashboard** | KPIs, alertas con íconos por tipo, movimientos recientes |
| **Inventario** | Fármacos, lotes, entradas, traslados, administraciones, mermas |
| **Bodegas** | Ubicaciones físicas, centros de costo, traslados entre bodegas |
| **Residentes** | Fichas clínicas, administraciones, gate de datos sensibles |
| **Reportes** | Kardex, consumo, valorización, mermas, proyección de compra, gráficos analíticos |
| **Usuarios** | Alta, activación, roles, auditoría de acceso |
| **Soporte** | Centro de ayuda institucional |
| **Notificaciones** | Campana en tiempo real (Reverb) + correo (Gmail SMTP) |

---

## 3. Requisitos previos

| Componente | Versión mínima |
|------------|----------------|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 20+ |
| npm | 10+ |

**Extensiones PHP requeridas:** `pdo_sqlite`, `mbstring`, `openssl`, `curl`, `fileinfo`, `json`.

Verificar PHP:

```bash
php -v
php -m | grep -E 'pdo_sqlite|mbstring|openssl|curl'
composer -V
node -v && npm -v
```

---

## 4. Cómo levantar el sistema

### 4.1 Instalación por primera vez

Ejecutar **una sola vez** al clonar el repositorio:

```bash
cd acalis-pharma-web

# 1. Dependencias PHP
composer install

# 2. Entorno
cp .env.example .env
php artisan key:generate

# 3. Base de datos SQLite + datos demo
touch database/database.sqlite   # si no existe
php artisan migrate --seed

# 4. Dependencias front y compilación inicial
npm install
npm run build
```

**Alternativa todo-en-uno:**

```bash
composer setup
```

### 4.2 Configurar `.env` antes de arrancar

Editar `.env` con los valores mínimos:

```env
APP_NAME="Acalis Pharma Web"
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=America/Santiago
APP_LOCALE=es_CL
APP_FALLBACK_LOCALE=es

DB_CONNECTION=sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Notificaciones en tiempo real
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=acalis-pharma
REVERB_APP_KEY=acalis-local-key
REVERB_APP_SECRET=acalis-local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
VITE_REVERB_ENABLED=true

# Correo (Gmail — contraseña de aplicación Google)
ACALIS_EMAIL_NOTIFICATIONS=tu-cuenta@gmail.com
ACALIS_EMAIL_NOTIFICATIONS_PASSWORD=xxxxxxxxxxxxxxxx
MAIL_MAILER=gmail

# Demo local
ACALIS_DEMO_MODE=true
ACALIS_DEMO_NOTIFICATION_EMAIL="${ACALIS_EMAIL_NOTIFICATIONS}"
```

Limpiar caché tras cualquier cambio en `.env`:

```bash
php artisan config:clear
```

---

### 4.3 Escenario A — Desarrollo local (recomendado)

Para trabajar en tu PC con recarga automática de CSS/JS.

**Un solo comando:**

```bash
composer dev
```

Esto levanta **5 procesos en paralelo**:

| Proceso | Puerto | Obligatorio | Función |
|---------|--------|:-----------:|---------|
| `php artisan serve` | **8000** | ✅ | Aplicación web |
| `php artisan queue:listen` | — | ✅ | Envía correos y notificaciones en cola |
| `php artisan reverb:start` | **8080** | ✅* | Campana y alertas en tiempo real |
| `php artisan pail` | — | Opcional | Logs en consola |
| `npm run dev` (Vite) | **5173** | ✅ | Hot-reload de assets |

\* Sin Reverb la app funciona, pero la campana no se actualiza en vivo (solo al refrescar F5).

**Abrir en el navegador:**

```
http://127.0.0.1:8000/login
```

**Detener:** `Ctrl + C` en la terminal (cierra todos los procesos).

**Verificar que todo está bien:**

```bash
# En otra terminal, mientras composer dev corre:
curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/login   # debe devolver 200
php artisan acalis:mail-test                                          # prueba correo Gmail
```

En DevTools → Consola **no** debe aparecer error de WebSocket a `ws://127.0.0.1:8080`.

---

### 4.4 Escenario B — Procesos manuales (terminales separadas)

Útil cuando quieres controlar cada servicio por separado.

**Terminal 1 — Servidor web**

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

**Terminal 2 — Cola de trabajos (correos y notificaciones)**

```bash
php artisan queue:work
# o con reintentos:
php artisan queue:listen --tries=1
```

**Terminal 3 — WebSocket Reverb**

```bash
php artisan reverb:start --host=127.0.0.1 --port=8080
```

**Terminal 4 — Vite (solo desarrollo local con hot-reload)**

```bash
npm run dev
```

> Si **no** usas Vite dev, compila assets estáticos una vez: `npm run build` (y asegúrate de que **no** exista `public/hot`).

**URL:** `http://127.0.0.1:8000`

---

### 4.5 Escenario C — Acceso desde Internet / DNS dinámico

Para demo remota, IP pública o **DNS dinámico (DDNS)** — por ejemplo `http://sistema-farmacia.ddns.net/`.

> **No uses `composer dev`** hacia Internet: Vite en `:5173` apunta a localhost y el navegador externo bloqueará los assets (error CORS / loopback).

#### 4.5.1 Ejemplo completo: `sistema-farmacia.ddns.net`

Esta guía asume que ya tienes el hostname DDNS apuntando a tu IP pública y que el servidor Laravel corre en la red local (Mac o PC en la misma LAN del router).

**Arquitectura**

```
Internet
   │
   ▼
Router (reenvío de puertos)
   ├── :8000  ──►  PC servidor :8000   (Laravel)
   └── :8080  ──►  PC servidor :8080   (Reverb WebSocket)
   │
   ▼
sistema-farmacia.ddns.net  →  IP pública actual
```

**Paso 1 — Cliente DDNS en el router o en el servidor**

En el panel de tu proveedor DDNS (No-IP, DuckDNS, el router, etc.):

| Campo | Valor |
|-------|-------|
| Hostname | `sistema-farmacia.ddns.net` |
| IP destino | Tu IP pública actual (actualización automática si el cliente DDNS está activo) |

Comprobar que resuelve:

```bash
nslookup sistema-farmacia.ddns.net
# o
dig +short sistema-farmacia.ddns.net
```

Debe devolver tu IP pública (no `127.0.0.1`).

**Paso 2 — Reenvío de puertos en el router**

Apunta al **IP local fija** del equipo donde corre Laravel (ej. `192.168.1.50`):

| Nombre regla | Puerto externo | IP interna | Puerto interno | Protocolo |
|--------------|----------------|------------|----------------|-----------|
| Acalis Web | 8000 | 192.168.1.50 | 8000 | TCP |
| Acalis Reverb | 8080 | 192.168.1.50 | 8080 | TCP |

> **Alternativa sin `:8000` en la URL:** reenvía puerto **80 → 8000** y usa `APP_URL=http://sistema-farmacia.ddns.net` (sin puerto). Laravel sigue escuchando en `:8000` dentro de la LAN.

**Paso 3 — Firewall del servidor**

Permitir tráfico entrante en los puertos expuestos:

```bash
# macOS (ejemplo)
# Preferencias → Red → Firewall → Opciones → permitir php/node según corresponda
# o temporalmente para pruebas:
sudo /usr/libexec/ApplicationFirewall/socketfilterfw --add $(which php)
```

En Linux: `ufw allow 8000/tcp && ufw allow 8080/tcp`.

**Paso 4 — Configurar `.env`**

Opción **A** — URL con puerto `:8000` (la más directa con `composer internet`):

```env
APP_URL=http://sistema-farmacia.ddns.net:8000

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=acalis-pharma
REVERB_APP_KEY=acalis-local-key
REVERB_APP_SECRET=acalis-local-secret
REVERB_HOST=sistema-farmacia.ddns.net
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
VITE_REVERB_ENABLED=true
```

Opción **B** — URL limpia sin puerto (requiere reenvío router **80 → 8000**):

```env
APP_URL=http://sistema-farmacia.ddns.net
REVERB_HOST=sistema-farmacia.ddns.net
REVERB_PORT=8080
REVERB_SCHEME=http
# ... resto igual que opción A
```

> **Importante:** `REVERB_HOST` debe ser el **mismo hostname** que usa el navegador (`sistema-farmacia.ddns.net`), no `127.0.0.1` ni la IP privada. El front compilado (`echo.js`) conecta el WebSocket a ese host.

**Paso 5 — Compilar assets y limpiar caché**

Cada vez que cambies variables `VITE_*` o `APP_URL`:

```bash
composer internet:prepare
```

Equivale a: eliminar `public/hot` + `npm run build` + `config:clear`.

**Paso 6 — Levantar servicios**

```bash
composer internet
```

Levanta en `0.0.0.0` (acepta conexiones externas):

| Proceso | Puerto | Escucha en |
|---------|--------|------------|
| Servidor web | 8000 | `0.0.0.0:8000` |
| Cola | — | background |
| Reverb | 8080 | `0.0.0.0:8080` |

**Paso 7 — Verificación**

Desde **otra red** (datos móviles, no la misma Wi‑Fi):

```bash
# Página de login (debe devolver 200)
curl -s -o /dev/null -w "%{http_code}" http://sistema-farmacia.ddns.net:8000/login

# Assets compilados (no deben apuntar a localhost:5173)
curl -s http://sistema-farmacia.ddns.net:8000/login | grep -E 'localhost|5173'
# → no debe mostrar nada
```

En el navegador:

```
http://sistema-farmacia.ddns.net:8000/login
```

En DevTools → **Red** → filtrar `ws://`: debe intentar `ws://sistema-farmacia.ddns.net:8080` (no `127.0.0.1`).

**Credenciales demo:** ver [§5 Usuarios demo](#5-usuarios-demo) (`password`).

---

#### 4.5.2 Checklist rápido DDNS

| # | Verificación | OK si… |
|---|--------------|--------|
| 1 | DNS | `nslookup sistema-farmacia.ddns.net` → IP pública |
| 2 | Router | Puertos 8000 y 8080 reenviados al servidor |
| 3 | `.env` | `APP_URL` y `REVERB_HOST` = `sistema-farmacia.ddns.net` |
| 4 | Assets | `composer internet:prepare` ejecutado (no existe `public/hot`) |
| 5 | Servicios | `composer internet` en marcha |
| 6 | WebSocket | Consola sin error `ws://127.0.0.1:8080` |
| 7 | Cola | Correos/notificaciones llegan (`queue:listen` activo) |

#### 4.5.3 Problemas frecuentes con DDNS

| Síntoma | Causa | Solución |
|---------|-------|----------|
| DNS no resuelve | Cliente DDNS detenido | Activar actualizador en router o PC |
| Timeout desde fuera | Puerto no reenviado o firewall | Revisar reglas router + firewall OS |
| CSS roto / CORS | Vite dev activo | `composer internet:prepare` (borra `public/hot`) |
| WebSocket a `127.0.0.1` | `REVERB_HOST` incorrecto o assets viejos | `.env` + `npm run build` |
| Login OK pero sin campana | Puerto 8080 no expuesto | Reenviar 8080 → 8080 en router |
| IP cambió y dejó de funcionar | DDNS desactualizado | Esperar propagación o forzar update DDNS |
| Mixed content (HTTPS) | Acceso por HTTPS sin certificado | Usar `http://` o configurar reverse proxy con TLS |

> **Producción real:** `php artisan serve` + DDNS es válido para **demo/PoC**. Para uso continuo conviene Nginx/Apache + PHP-FPM + certificado Let's Encrypt en el mismo hostname.

---

#### 4.5.4 Acceso por IP pública (sin dominio)

Si no usas DDNS, sustituye el hostname por tu IP:

```env
APP_URL=http://186.105.151.111:8000
REVERB_HOST=186.105.151.111
REVERB_PORT=8080
REVERB_SCHEME=http
```

El resto del flujo es idéntico: `internet:prepare` → `composer internet` → reenvío de puertos 8000/8080.

**URL de acceso externo (DDNS):**

```
http://sistema-farmacia.ddns.net:8000/login
```

**URL sin puerto (si reenviaste 80 → 8000):**

```
http://sistema-farmacia.ddns.net/login
```

---

### 4.6 Resumen rápido — ¿qué comando uso?

| Situación | Comando |
|-----------|---------|
| Primera instalación | `composer setup` |
| Desarrollo diario en tu PC | `composer dev` |
| Demo / acceso por Internet | `composer internet:prepare` → `composer internet` |
| Demo con DDNS `sistema-farmacia.ddns.net` | Ver [§4.5.1](#451-ejemplo-completo-sistema-farmaciaddnsnet) |
| Solo probar sin hot-reload | `npm run build` + `php artisan serve` + `queue:work` + `reverb:start` |
| Cambié `.env` | `php artisan config:clear` |
| Cambié JS/CSS o `VITE_*` | `npm run build` |
| Cambié gráficos de reportes | `npm run build` (entry `reports-charts.js`) |
| Probar correo | `php artisan acalis:mail-test` |

---

### 4.7 Servicios que deben estar activos

| Funcionalidad | Servicios necesarios |
|---------------|---------------------|
| Login, módulos, CRUD | `php artisan serve` |
| Correos y notificaciones por email | `queue:work` o `queue:listen` |
| Campana en tiempo real / toast | `reverb:start` + `BROADCAST_CONNECTION=reverb` |
| CSS/JS en dev local | `npm run dev` **o** `npm run build` |
| CSS/JS desde Internet | Solo `npm run build` (sin Vite dev) |

---

### 4.8 Solución de problemas al levantar

| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| CSS/JS no cargan (HTML sin estilos) | `public/hot` apunta a Vite `:5173` | `composer internet:prepare` o borrar `public/hot` + `npm run build`; no dejar `npm run dev` activo en demo |
| CSS/JS no cargan desde IP pública | Vite dev activo (`public/hot`) | `composer internet:prepare` |
| Error WebSocket en consola | Reverb no corre | `php artisan reverb:start --host=127.0.0.1 --port=8080` |
| Correos no llegan | Cola detenida | `php artisan queue:work` |
| Correo 535 Gmail | Contraseña incorrecta | Usar contraseña de aplicación Google |
| `Route [login] not defined` | Caché de rutas | `php artisan route:clear && config:clear` |
| Pantalla en blanco tras deploy | Assets sin compilar | `npm run build` |
| Paginación en inglés | Falta locale o caché de vistas | `APP_LOCALE=es_CL` + `php artisan view:clear` |
| `SQLSTATE[HY000]` SQLite | Falta archivo BD | `touch database/database.sqlite && php artisan migrate --seed` |

> **`composer internet`** elimina automáticamente `public/hot` al arrancar, para evitar servir assets desde Vite dev cuando se expone el sistema por Internet/DDNS.

---

### 4.9 Comandos de mantenimiento

```bash
php artisan test                    # Ejecutar pruebas (140+ tests)
php artisan migrate --seed          # Resetear BD con datos demo
php artisan config:clear            # Limpiar caché de configuración
php artisan route:clear             # Limpiar caché de rutas
php artisan queue:restart           # Reiniciar workers tras cambios de código
npm run build                       # Recompilar assets para producción/demo
```

### 4.10 Ciclo de desarrollo típico

1. Clonar → `composer setup` → configurar `.env`.
2. Desarrollo diario → `composer dev` → abrir `http://127.0.0.1:8000`.
3. Antes de commit → `php artisan test`.
4. Demo externa → `composer internet:prepare` → ajustar `APP_URL` → `composer internet`.
5. No commitear `.env`, `database.sqlite` ni docs en `.gitignore`.

---

## 5. Usuarios demo

Contraseña para todos: **`password`**

Los correos usan **alias Gmail** (`+rol`) para login único en BD; todas las **notificaciones** llegan a la bandeja configurada en `ACALIS_EMAIL_NOTIFICATIONS`.

| Rol | Correo de login | Nombre |
|-----|-----------------|--------|
| Administrador | `acalisnotificaciones+admin@gmail.com` | Administrador Sistema |
| Director Médico | `acalisnotificaciones+director@gmail.com` | Carlos Muñoz |
| Enfermero Jefe | `acalisnotificaciones+jefe@gmail.com` | Patricia López |
| TENS | `acalisnotificaciones+tens@gmail.com` | Andrea Rojas |

En entorno `local`, el panel de cuentas demo aparece en la pantalla de login.

```bash
php artisan db:seed   # Recrea usuarios demo si es necesario
```

---

## 6. Roles y permisos

Cada usuario tiene **un solo rol** (Spatie Permission).

### 6.1 Matriz de capacidades

| Permiso / Área | Admin | Director | Jefe | TENS |
|----------------|:-----:|:--------:|:----:|:----:|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Ver inventario | ✅ | ✅ | ✅ | ✅ |
| Registrar movimientos | ✅ | ✅ | ✅ | ✅ |
| Mermas y vencimientos | ✅ | ✅ | ✅ | ❌ |
| Gestionar bodegas / centros de costo | ✅ | ✅ | ❌ | ❌ |
| Ver residentes (con gate) | ✅ | ✅ | ✅ | ✅ |
| Gestionar residentes | ✅ | ✅ | ✅ | ❌ |
| Reportes internos (kardex, consumo) | ✅ | ✅ | ✅ | ❌ |
| Reportes gerenciales | ✅ | ✅ | ❌ | ❌ |
| Gestionar usuarios | ✅ | ❌ | ❌ | ❌ |
| Autorizar fármacos controlados | ✅ | ✅ | ❌ | ❌ |
| Auditoría general | ✅ | ❌ | ❌ | ❌ |
| Soporte | ✅ | ✅ | ✅ | ✅ |

### 6.2 Permisos técnicos (`app/Enums/Permission.php`)

| Slug | Descripción |
|------|-------------|
| `dashboard.view` | Panel central |
| `inventory.view` | Consultar stock, fármacos, lotes |
| `inventory.move` | Entradas, traslados, administraciones |
| `inventory.waste` | Mermas y vencimientos |
| `pharmacies.manage` | Bodegas y centros de costo |
| `residents.view` | Consultar residentes |
| `residents.manage` | Alta/edición/baja residentes |
| `reports.internal` | Kardex y consumo |
| `reports.executive` | Valorización, mermas, proyección, gráficos |
| `users.manage` | Personal del sistema |
| `drugs.controlled.authorize` | Código autorización controlados |
| `support.access` | Página de soporte |

---

## 7. Notificaciones y eventos

### 7.1 Canales de notificación

| Canal | Tecnología | Qué muestra |
|-------|------------|-------------|
| **Correo** | Gmail SMTP + cola | Plantillas `AcalisMail` |
| **Base de datos** | Tabla `notifications` | Campana del header |
| **Broadcast** | Laravel Reverb + Echo | Toast instantáneo |

### 7.2 Eventos → acciones → destinatarios

| Evento | Se dispara cuando | Correo | Tiempo real | Destinatarios |
|--------|-------------------|:------:|:-----------:|---------------|
| `UserCreated` | Admin crea personal | ✅ | ✅ | Administradores |
| `UserStatusChanged` | Activar / desactivar / baja / restaurar | ✅ | ✅ | Admin + director + jefe |
| `ResidentRegistered` | Alta de residente | ✅ | ✅ | Director + jefe |
| `InventoryMovementRecorded` | Cualquier movimiento de stock | ✅ | ✅ | Según tipo (ver abajo) |
| `HighValueWasteRecorded` | Merma ≥ $50.000 CLP | ✅ | ✅ | Admin + director |
| `ControlledDrugAuthorizationRequested` | Movimiento controlado sin código | ✅ | ✅ | Admin + director |
| **OTP activación** | Alta usuario / reenvío | ✅ | — | Usuario nuevo |
| **Restablecer contraseña** | Formulario forgot-password | ✅ | — | Usuario solicitante |

**Destinatarios por tipo de movimiento (`InventoryMovementRecorded`):**

| Tipo | Destinatarios |
|------|---------------|
| Entrada | Admin, director, jefe |
| Administración a residente | Director, jefe |
| Otros (traslado, merma baja, etc.) | Director médico |
| Merma alto valor | Solo alerta dedicada (no duplica movimiento genérico) |

> Las notificaciones a usuarios demo se **deduplican por bandeja de correo** para enviar un solo email por evento.

### 7.3 Arquitectura de notificaciones

```
Acción HTTP → Servicio de dominio → Evento Laravel
    → Listener → Notification (mail + database + broadcast)
        → Cola → SMTP Gmail
        → Reverb → Echo → NotificationBell (Livewire)
```

---

## 8. Módulos — flujo completo

### 8.1 Acceso y autenticación

```
/login → credenciales + términos
    → ¿sesión activa en otro dispositivo? → modal → confirmar sin re-password
    → dashboard

/activar-cuenta → OTP por correo → definir contraseña
/forgot-password → enlace restablecimiento
```

**Política de sesión:** 60 min máximo · 15 min inactividad · modal 60 s · una sesión por dispositivo.

**Perfil (`/profile`):** datos personales, contraseña, **auditoría de acceso** (conexiones/desconexiones, export Excel/PDF).

### 8.2 Dashboard (`/dashboard`)

- KPIs: residentes activos, movimientos del día, alertas pendientes, lotes activos.
- Tabla de **movimientos recientes** (últimas operaciones de inventario).
- **Alertas del sistema** con ícono según tipo:
  - Stock crítico → triángulo de advertencia (`low_stock`).
  - Vencimiento próximo → calendario (`expiring_soon`).
  - Merma de alto valor → papelera (`high_value_waste`).
- Acceso según `dashboard.view`.

Las alertas se generan automáticamente al registrar movimientos (stock bajo mínimo, lote ≤ 30 días para vencer).

### 8.3 Inventario (`/inventario`)

```
/inventario                    → Stock por lote (filtros, paginación, Registros encontrados)
/inventario/farmacos           → Catálogo de fármacos + export CSV/PDF
/inventario/farmacos/{id}      → Detalle + lotes + movimientos
/inventario/movimientos        → Historial global (filtros: tipo, bodega, fechas, búsqueda)
/inventario/movimientos/entrada        → POST entrada Cenabast/proveedor
/inventario/movimientos/traslado       → Entre bodegas
/inventario/movimientos/administracion → A residente (requiere receta)
/inventario/movimientos/merma          → Salida por merma
/inventario/movimientos/vencimiento    → Baja por vencimiento
/lotes/{batch}                 → Trazabilidad del lote
```

Cada movimiento registra: usuario, cantidad, valor, bodega, lote y dispara `InventoryMovementRecorded`. Los lotes guardan `supplier_name` y `received_at` (usados en gráficos de proveedores).

### 8.4 Bodegas (`/bodegas`)

```
/bodegas                       → Listado (central, botiquín, emergencia)
/bodegas/centros-de-costo      → Pisos / pabellones
/bodegas/traslados             → Historial de traslados
CRUD bodegas y centros         → Solo pharmacies.manage
```

### 8.5 Residentes (`/residentes`)

```
/residentes/acceso             → Gate: contraseña + aviso Ley 21.719 (15 min)
/residentes                    → Listado + export
/residentes/crear              → Alta (dispara ResidentRegistered)
/residentes/{id}               → Ficha + historial administraciones
```

Todo acceso queda en `resident_access_logs`.

### 8.6 Reportes (`/reportes`)

#### Rutas y permisos

| Ruta | Rol | Contenido |
|------|-----|-----------|
| `/reportes/kardex` | Interno | Trazabilidad global de movimientos |
| `/reportes/consumo-residentes` | Interno | Administraciones agrupadas por residente |
| `/reportes/graficos` | Gerencial | 8 gráficos analíticos (Chart.js) |
| `/reportes/valorizacion` | Gerencial | Valor del inventario por bodega/fármaco |
| `/reportes/mermas-mensuales` | Gerencial | Mermas por mes |
| `/reportes/proyeccion-compra` | Gerencial | Reposición sugerida bajo mínimo |
| `/reportes/export/{report}/{format}` | Según permiso | CSV o PDF (respeta filtros de la URL) |

#### Filtros, Select2 y contador de resultados

Todos los listados con barra de filtros (`filter-toolbar`) comparten el mismo patrón UX:

1. **Select2** en todos los `<select>` (búsqueda habilitada en filtros; locale `es`).
2. **Flatpickr** en campos de fecha (`dd/mm/aaaa`).
3. **Registros encontrados:** texto en negrita sobre la tabla, alineado a la derecha:
   - Etiqueta: `Registros encontrados:`
   - Valor total filtrado en **rojo suave** (`text-red-500`), negrita y formato chileno.
   - Componente: `resources/views/components/ui/records-found.blade.php`
   - En paginados usa `->total()` (no solo la página visible).

**Kardex** — filtros alineados con columnas de la tabla:

| Filtro | Columna / lógica |
|--------|------------------|
| Desde / Hasta | Fecha del movimiento |
| Tipo | Tipo de movimiento (`MovementType`) |
| Fármaco | Nombre y código |
| Bodega | Bodega origen o destino (traslados) |
| Centro de costo | Centro asociado al movimiento |
| Profesional | Usuario que registró la operación |

Los demás reportes y módulos (inventario, bodegas, residentes, usuarios, auditoría) aplican el mismo contador donde hay filtros + tabla.

#### Gráficos analíticos (`/reportes/graficos`)

Reporte gerencial con **Chart.js** (`resources/js/reports-charts.js`). Los datos salen de `ReportService::charts()` y se adaptan a la lógica **institucional** (no retail):

| Grupo | Gráfico | Lógica Acalis |
|-------|---------|---------------|
| **Inventario y caducidad** | Barras agrupadas | Valor stock actual vs mínimo por categoría de fármaco |
| | Medidor (gauge) | % unidades con vencimiento ≤ 90 días (verde/amarillo/rojo) |
| **Consumo operativo** | Líneas RX vs Non-RX | Administraciones: controlados/psicotrópicos vs resto |
| | Burbujas | Rotación (uds. administradas) vs eficiencia consumo/stock |
| **Compras y proveedores** | Dispersión | Ciclo de reposición (días) vs cumplimiento por proveedor de lote |
| | Dona | Distribución de entradas por `supplier_name` del lote |
| **Auditoría de pérdidas** | Control Shewhart | Merma + vencimiento diario con media y límites ±2σ |
| | Embudo | Flujo: entradas → traslados → administraciones → pérdidas |

Filtros: rango de fechas (por defecto últimos 6 meses), bodega, centro de costo y fármaco. Requiere `npm run build` (entry Vite `reports-charts.js`).

Datos demo enriquecidos: `ChartDemoSeeder` (movimientos históricos, proveedores variados, lotes próximos a vencer).

### 8.7 Administración (`/usuarios`)

```
/usuarios              → Listado, filtros, estadísticas
/usuarios/crear        → Alta + OTP activación por correo
/usuarios/{id}         → Ficha + auditoría + auditoría de acceso
/roles                 → Matriz de permisos por rol
/auditoria             → Log global de cambios
```

### 8.8 Soporte (`/soporte`)

Centro de ayuda y contacto institucional.

---

## 9. Diccionario HTTP y verbos

### 9.1 Verbos usados en rutas

| Verbo | Uso en Acalis Pharma |
|-------|----------------------|
| **GET** | Pantallas, listados, exportaciones, APIs de estado (`/sesion/estado`) |
| **POST** | Crear registros, login, movimientos, confirmaciones, logout |
| **PUT** | Actualización completa de recursos (fármacos, usuarios, lotes) |
| **PATCH** | Cambios parciales (activar/desactivar usuario, restaurar) |
| **DELETE** | Baja lógica (usuarios, residentes, lotes, bodegas) |

### 9.2 Códigos HTTP — catálogo institucional

El sistema incluye páginas de error personalizadas (`app/Support/HttpErrorCatalog.php`). Galería de desarrollo: `/dev/errores-http` (solo `local`).

| Código | Significado | Contexto Acalis |
|--------|-------------|-----------------|
| **400** | Solicitud inválida | Formulario mal enviado |
| **401** | No autenticado | Sesión expirada o sin login |
| **403** | Acceso denegado | Rol sin permiso (Policy) |
| **404** | No encontrado | Fármaco, lote o ruta inexistente |
| **405** | Método no permitido | GET en ruta POST |
| **419** | Token CSRF inválido | Formulario abierto demasiado tiempo |
| **422** | Validación fallida | Reglas de negocio / Form Request |
| **429** | Demasiadas peticiones | Rate limit (login, OTP, gate residentes) |
| **500** | Error interno | Excepción no controlada |
| **503** | Servicio no disponible | Mantenimiento |

Códigos adicionales documentados: 402, 406–418, 421, 423–428, 431, 451, 501–508, 511.

---

## 10. Auditoría y seguridad

| Registro | Ubicación |
|----------|-----------|
| Cambios en entidades | `audit_logs` → `/auditoria` |
| Sesiones de acceso | `user_access_logs` → perfil y ficha usuario |
| Acceso a residentes | `resident_access_logs` |
| Aceptación de términos | Auditoría con versión del documento |

Prácticas de ciberseguridad detalladas en `Cibersecurity.md` (documento local, no en GitHub).

---

## 11. Tests

```bash
php artisan test
```

Cobertura principal: autenticación, sesión única, política de sesión, notificaciones, exportaciones, permisos, activación OTP, access logs, cuentas demo, reportes (kardex, filtros, gráficos).

---

## 12. Interfaz, idioma y UX

### 12.1 Idioma español (Chile)

| Configuración | Valor |
|---------------|-------|
| `APP_LOCALE` | `es_CL` |
| `APP_FALLBACK_LOCALE` | `es` |
| Zona horaria | `America/Santiago` |

Archivos de traducción en `lang/es/` y `lang/es_CL/`:

- `pagination.php` — «Mostrando 1 a 25 de 44 resultados», Anterior/Siguiente
- `auth.php`, `passwords.php`, `validation.php`
- `es.json` / `es_CL.json` — cadenas Laravel y paginación Tailwind

Tras cambiar locale: `php artisan config:clear && php artisan view:clear`.

### 12.2 Componentes de formulario

| Tecnología | Uso | Archivo |
|------------|-----|---------|
| **Select2** | Todos los `<select class="select vx-control">` | `resources/js/form-enhancements.js` |
| **Flatpickr** | Campos `type="date"` → `dd/mm/aaaa` | Idem |
| **Alpine.js** | Sidebar, tema, modales de sesión | `resources/js/` |

En barras `.filter-toolbar`, Select2 siempre permite búsqueda (aunque haya pocas opciones).

### 12.3 Contador «Registros encontrados»

Componente reutilizable `<x-ui.records-found>`:

```blade
{{-- Paginado: usa total filtrado --}}
<x-ui.records-found :items="$movements" />

{{-- Colección sin paginar --}}
<x-ui.records-found :items="$rows" />

{{-- Conteo explícito --}}
<x-ui.records-found :count="42" />
```

Presente en: reportes, inventario, fármacos, movimientos, bodegas, traslados, centros de costo, residentes, usuarios y auditoría.

### 12.4 Assets front (Vite)

| Entry | Contenido |
|-------|-----------|
| `resources/css/app.css` | Tailwind 4 + DaisyUI + estilos Select2/Flatpickr |
| `resources/js/app.js` | Alpine, Echo/Reverb, form-enhancements |
| `resources/js/reports-charts.js` | Chart.js — solo vista `/reportes/graficos` |

**Regla demo/Internet:** usar `npm run build` y **no** dejar `public/hot` (Vite dev). Si existe, Laravel sirve CSS/JS desde `localhost:5173` y la UI queda sin estilos desde DDNS o IP pública.

---

## 13. Documentación complementaria (local)

Los siguientes archivos están en `.gitignore` y **no se suben a GitHub**. Mantenerlos solo en entornos autorizados:

| Archivo | Contenido |
|---------|-----------|
| `README_Negocio.md` | Documento de negocio sin tecnología |
| `README_Notificaciones_y_Correo.md` | Guía detallada Reverb + Gmail |
| `README_Internet.md` | Exposición con módem / IP pública |
| `README_ToDoList.md` | Backlog del proyecto |
| `todo.md` | Tareas pendientes |
| `Cibersecurity.md` | Seguridad y ethical hacking |

---

## Licencia

Código de aplicación propietario — **IntegralServices Spa**. Framework Laravel bajo [licencia MIT](https://opensource.org/licenses/MIT).

---

**Mauricio Durán Torres** · CTO IntegralServices Spa · [mauriciodurant@gmail.com](mailto:mauriciodurant@gmail.com)
