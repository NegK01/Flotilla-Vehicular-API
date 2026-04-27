# **Flotilla Vehicular API - Fleet Management REST API**

🌐 Available in: [English](#english) | [Español](#español)

---

## English

### Overview

**Flotilla Vehicular API** is a fleet management backend developed in PHP 8.2+ with Laravel 12.
It provides functionality for coordinating vehicle requests, maintenance cycles, and trip execution through a RESTful API with role-based access control (RBAC) for Drivers, Operators, and Administrators.
Critical business logic (availability checks, status transitions, mileage updates) is enforced at the PostgreSQL database layer via triggers and stored procedures.

---

### Features

* **Token-based Authentication:** Secure login/logout and driver registration via Laravel Sanctum
* **Role-Based Access Control (RBAC):** Granular permissions for Admin, Operator, and Driver roles
* **Vehicle & Maintenance Management:** Full CRUD with soft-delete/restore and automatic state transitions
* **Vehicle Request Workflow:** Lifecycle from PENDING → APPROVED / REJECTED / CANCELLED
* **Trip Execution:** Linked to approved requests with automatic mileage updates
* **Reporting:** Fleet availability, vehicle history, and driver history
* **API Documentation:** Auto-generated OpenAPI docs via Scramble

---

### Tech Stack

**Language:** PHP 8.2+
**Framework / Libraries:** Laravel 12, Laravel Sanctum, Dedoc Scramble
**Database:** PostgreSQL
**Minimum Requirements:** PHP 8.2, Composer, Node.js, PostgreSQL
**Build / Deployment:** Vite, Laravel Sail (Docker), Tailwind CSS

---

### Project Structure (simplified)

```
Flotilla-Vehicular-API/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  └─ Requests/
│  ├─ Models/
│  ├─ Policies/
│  └─ Providers/
├─ config/
├─ database/
│  ├─ migrations/
│  ├─ seeders/
│  └─ factories/
├─ routes/
│  └─ api.php
├─ public/
└─ README.md
```

---

### Setup & Installation

```bash
# 1. Clone repository
git clone https://github.com/NegK01/Flotilla-Vehicular-API.git
cd Flotilla-Vehicular-API

# 2. Install dependencies
composer install

# 3. Environment setup
cp .env.example .env

# 4. Configure database in .env

# 5. Run migrations & seeders
php artisan migrate --seed

# 6. Link storage
php artisan storage:link

# 7. Run server
php artisan serve

# 8. (Optional) Frontend
npm install
```

---

### Environment Variables (.env)

```
APP_NAME=FlotillaVehicularAPI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=flotilla_vehicular
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

---

## Español

### Descripción

**Flotilla Vehicular API** es un sistema backend de gestión de flotilla desarrollado en PHP 8.2+ con Laravel 12.
Permite coordinar solicitudes de vehículos, mantenimientos y ejecución de viajes mediante una API REST con control de acceso basado en roles (RBAC).
La lógica crítica del negocio se implementa en la base de datos PostgreSQL mediante triggers y procedimientos almacenados.

---

### Funcionalidades

* **Autenticación por token:** Login, logout y registro mediante Sanctum
* **Control de acceso (RBAC):** Roles Admin, Operador y Conductor
* **Gestión de vehículos y mantenimiento:** CRUD completo con estados automáticos
* **Flujo de solicitudes:** PENDIENTE → APROBADO / RECHAZADO / CANCELADO
* **Ejecución de viajes:** Actualización automática de kilometraje
* **Reportes:** Disponibilidad, historial de vehículos y conductores
* **Documentación API:** Generada automáticamente

---

### Tecnologías

**Lenguaje:** PHP 8.2+
**Framework / Librerías:** Laravel 12, Sanctum, Scramble
**Base de Datos:** PostgreSQL
**Requisitos Mínimos:** PHP 8.2, Composer, Node.js, PostgreSQL
**Construcción / Despliegue:** Vite, Docker (Sail), Tailwind

---

### Estructura del Proyecto (simplificada)

```
Flotilla-Vehicular-API/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  └─ Requests/
│  ├─ Models/
│  ├─ Policies/
│  └─ Providers/
├─ config/
├─ database/
│  ├─ migrations/
│  ├─ seeders/
│  └─ factories/
├─ routes/
│  └─ api.php
├─ public/
└─ README.md
```

---

### Instalación y Ejecución

```bash
# 1. Clonar el repositorio
git clone https://github.com/NegK01/Flotilla-Vehicular-API.git
cd Flotilla-Vehicular-API

# 2. Instalar dependencias
composer install

# 3. Configuración de entorno
cp .env.example .env

# 4. Configurar base de datos en .env

# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Enlazar almacenamiento
php artisan storage:link

# 7. Iniciar el servidor
php artisan serve

# 8. (Opcional) Frontend
npm install
```

---

### Variables de Entorno

```
APP_NAME=FlotillaVehicularAPI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=flotilla_vehicular
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```
