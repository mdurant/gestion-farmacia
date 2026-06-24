# Acalis Pharma Web

**Sistema de gestión farmacéutica institucional** para residencias de larga estadía (asilos) en Chile. Desarrollado por **IntegralServices Spa**.

| | |
|---|---|
| **Producto** | ACALIS-PHARMA |
| **Stack** | Laravel 13 · PHP 8.3+ · Livewire 4 · Tailwind/DaisyUI · SQLite (dev) |
| **CTO** | Mauricio Durán Torres — IntegralServices Spa |
| **Contacto** | [mauriciodurant@gmail.com](mailto:mauriciodurant@gmail.com) |

---

## Índice

1. [Contexto y objetivo](#1-contexto-y-objetivo)
2. [Qué hace el sistema](#2-qué-hace-el-sistema)
3. [Requisitos e instalación](#3-requisitos-e-instalación)
4. [Flujo de trabajo del proyecto](#4-flujo-de-trabajo-del-proyecto)
5. [Usuarios demo](#5-usuarios-demo)
6. [Roles y permisos](#6-roles-y-permisos)
7. [Notificaciones y eventos](#7-notificaciones-y-eventos)
8. [Módulos — flujo completo](#8-módulos--flujo-completo)
9. [Diccionario HTTP y verbos](#9-diccionario-http-y-verbos)
10. [Auditoría y seguridad](#10-auditoría-y-seguridad)
11. [Tests](#11-tests)
12. [Documentación complementaria (local)](#12-documentación-complementaria-local)

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

**Contexto regional:** español Chile, zona horaria `America/Santiago`, montos en CLP.

---

## 2. Qué hace el sistema

| Módulo | Función principal |
|--------|-------------------|
| **Acceso** | Login, términos, activación OTP, política de sesión, sesión única |
| **Dashboard** | KPIs, alertas de stock/vencimiento, movimientos recientes |
| **Inventario** | Fármacos, lotes, entradas, traslados, administraciones, mermas |
| **Bodegas** | Ubicaciones físicas, centros de costo, traslados entre bodegas |
| **Residentes** | Fichas clínicas, administraciones, gate de datos sensibles |
| **Reportes** | Kardex, consumo, valorización, mermas, proyección de compra |
| **Usuarios** | Alta, activación, roles, auditoría de acceso |
| **Soporte** | Centro de ayuda institucional |
| **Notificaciones** | Campana en tiempo real (Reverb) + correo (Gmail SMTP) |

---

## 3. Requisitos e instalación

### Requisitos

- PHP 8.3+, Composer 2.x
- Node.js 20+, npm
- Extensiones PHP: `pdo_sqlite`, `mbstring`, `openssl`, `curl`

### Instalación inicial

```bash
composer setup
# o manualmente:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

### Configuración mínima `.env`

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb

ACALIS_EMAIL_NOTIFICATIONS=tu-cuenta@gmail.com
ACALIS_EMAIL_NOTIFICATIONS_PASSWORD=contraseña-de-aplicacion-google
```

---

## 4. Flujo de trabajo del proyecto

### 4.1 Desarrollo local (con hot-reload)

```bash
composer dev
```

Levanta en paralelo:

| Proceso | Puerto | Función |
|---------|--------|---------|
| `php artisan serve` | 8000 | Aplicación web |
| `php artisan queue:listen` | — | Correos y notificaciones en cola |
| `php artisan reverb:start` | 8080 | WebSocket (campana en vivo) |
| `php artisan pail` | — | Logs en tiempo real |
| `npm run dev` | 5173 | Vite (solo acceso local) |

> **Importante:** `composer dev` + Vite **no** sirve para acceso desde Internet (ver §4.2).

### 4.2 Exposición en Internet / DNS dinámico

```bash
composer internet:prepare   # elimina public/hot, compila assets
composer internet           # serve 0.0.0.0 + cola + Reverb 0.0.0.0
```

Configurar en `.env`:

```env
APP_URL=http://TU_IP_PUBLICA:8000
REVERB_HOST=TU_IP_PUBLICA
```

Reenviar puertos **8000** (web) y opcionalmente **8080** (Reverb) en el router.

### 4.3 Comandos útiles

```bash
php artisan test                    # Suite de pruebas (138+ tests)
php artisan acalis:mail-test        # Probar correo Gmail
php artisan queue:work              # Cola manual
php artisan config:clear            # Tras cambiar .env
npm run build                       # Tras cambiar JS/CSS o VITE_*
```

### 4.4 Ciclo de desarrollo típico

1. Crear rama / trabajar en feature.
2. `composer dev` para probar localmente.
3. Ejecutar `php artisan test` antes de commit.
4. Para demo externa: `composer internet:prepare` + `composer internet`.
5. No commitear `.env`, SQLite ni documentos listados en `.gitignore`.

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
| `reports.executive` | Valorización, mermas, proyección |
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

- Resumen de stock crítico y lotes por vencer.
- Movimientos recientes y alertas del sistema.
- Acceso según `dashboard.view`.

### 8.3 Inventario (`/inventario`)

```
/inventario                    → Stock por lote (filtros, paginación)
/inventario/farmacos           → Catálogo de fármacos + export CSV/PDF
/inventario/farmacos/{id}      → Detalle + lotes + movimientos
/inventario/movimientos        → Historial global
/inventario/movimientos/entrada        → POST entrada Cenabast/proveedor
/inventario/movimientos/traslado       → Entre bodegas
/inventario/movimientos/administracion → A residente (requiere receta)
/inventario/movimientos/merma          → Salida por merma
/inventario/movimientos/vencimiento    → Baja por vencimiento
/lotes/{batch}                 → Trazabilidad del lote
```

Cada movimiento registra: usuario, cantidad, valor, bodega, lote y dispara `InventoryMovementRecorded`.

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

| Ruta | Rol | Contenido |
|------|-----|-----------|
| `/reportes/kardex` | Interno | Movimientos por fármaco/lote |
| `/reportes/consumo-residentes` | Interno | Consumo por residente |
| `/reportes/valorizacion` | Gerencial | Valor del inventario |
| `/reportes/mermas-mensuales` | Gerencial | Mermas por período |
| `/reportes/proyeccion-compra` | Gerencial | Sugerencia de reposición |
| `/reportes/export/{report}/{format}` | Según permiso | CSV o PDF |

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

Cobertura principal: autenticación, sesión única, política de sesión, notificaciones, exportaciones, permisos, activación OTP, access logs, cuentas demo.

---

## 12. Documentación complementaria (local)

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
