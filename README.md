# 🧾 Interpreters Billing System – Backend API (Laravel)

Backend de la aplicación de gestión de servicios de interpretación. Desarrollado con **Laravel**, este sistema provee endpoints RESTful para manejar usuarios, servicios, facturación, reportes, intérpretes y más.

---

## ⚙️ Tech Stack

- **Laravel 10+**
- **MySQL**
- **JWT Authentication**
- **Laravel Sanctum**
- **Spatie Permissions**
- **Dompdf**
- **Laravel Scheduler / Cron Jobs**
- **Laravel Excel**

## 🔐 Autenticación

Se utiliza **JWT Auth** para la protección de rutas API:

* Registro
* Login
* Recuperación de contraseña vía correo
* Middleware `auth:api` para proteger rutas

---

## 📂 Estructura de Módulos

* **UsersController** – Registro, login, perfil
* **InvoicesController** – Crear y gestionar facturas
* **InterpretersController** – Crear, editar y listar intérpretes
* **AgenciesController** – CRUD de agencias
* **LanguagesController** – Lenguajes disponibles
* **PayrollController** – Generación de planillas quincenales
* **ReportsController** – Consultas por rango de fechas
* **PDFController** – Generación de PDFs y cheques
* **SettingsController** – Configuración de cuenta y app

---

## 🧾 Facturación

* Cada servicio generado se guarda como una **factura** (`invoices`).
* Se almacena:
  * Agencia
  * Intérprete
  * Lenguaje
  * Fecha
  * Monto
  * Estado (`abierto`, `cerrado`, `pagado`)
* Generación automática de PDF.

---

## 📄 Planillas

* Se generan **quincenalmente**
* Los servicios entran a planilla si han pasado **45 días desde la fecha del servicio**
* Las planillas pueden incluir montos acumulados por intérprete

---

## 📊 Reportes

* Reporte por fecha
* Ingresos por mes
* Lenguajes más solicitados
* Exportación a Excel / PDF

---

## 📅 Tareas programadas

* Laravel Scheduler (`app/Console/Kernel.php`) puede ejecutar:

  * Generación de planillas automáticas
  * Envío de notificaciones
  * Limpieza de registros antiguos

---

## 📤 Endpoints principales (ejemplo)

| Método | Ruta                  | Descripción                |
| ------ | --------------------- | -------------------------- |
| POST   | /api/register         | Registro de usuario        |
| POST   | /api/login            | Inicio de sesión           |
| GET    | /api/dashboard        | Datos para panel principal |
| POST   | /api/invoices         | Crear factura              |
| GET    | /api/payroll?from\&to | Ver planilla por fechas    |
| GET    | /api/reports?from\&to | Reportes por rango         |
| GET    | /api/interpreters     | Lista de intérpretes       |
| GET    | /api/languages        | Lista de lenguajes         |

---

## 👤 Autor

Proyecto desarrollado por jagudo2514@gmail.com como solución real a procesos administrativos de empresas de servicios de interpretación.
