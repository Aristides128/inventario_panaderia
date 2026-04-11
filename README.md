# Sistema de Gestión de Inventario para Panadería

Sistema integral desarrollado con Laravel y Filament PHP para la gestión eficiente del inventario, compras, producciones y distribución de productos en sucursales.

## 🚀 Características Principales

- **Gestión de Compras y Proveedores:** Asistente de múltiples pasos (wizard) intuitivo para registrar compras, detalles de productos y su recepción en bodega.
- **Control Automático de Inventario:** Utiliza el patrón Observer de Laravel para generar automáticamente los movimientos de inventario (entradas, salidas y anulaciones) ajustando el stock y los lotes.
- **Control de Producción:** Administración detallada de componentes y lotes de producción diaria de panadería.
- **Distribución a Sucursales:** Gestión optimizada de envíos mediante interfaces paso a paso, integrando el control detallado de los productos enviados a cada sucursal.
- **Gestión de Catálogo y Personal:** Control consolidado de categorías, productos, información de empleados y lista de sucursales.
- **Panel Administrativo Moderno:** Interfaz interactiva, rápida y responsive construida con el ecosistema de Filament PHP.

## 📋 Requisitos del Sistema

- PHP 8.1 o superior
- Composer
- Base de datos MySQL/MariaDB
- Laravel
- Node.js y NPM (para compilar recursos front-end)

## 🛠️ Instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/Aristides128/inventario_panaderia.git
   cd inventario_panaderia
   ```

2. Instalar dependencias de servidor:
   ```bash
   composer install
   ```

3. Configurar el entorno:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configurar las credenciales de la base de datos en el archivo `.env`.

5. Ejecutar las migraciones y seeders para estructurar la base de datos:
   ```bash
   php artisan migrate --seed
   ```
   *(Nota: Opcionalmente puede tener la base de datos previamente creada o restaurada desde un respaldo).*

6. Iniciar el servidor local:
   ```bash
   php artisan serve
   ```

## 🔒 Credenciales por Defecto

- **Email:** admin@admin.com (o el configurado en sus Database Seeders)
- **Contraseña:** 123

## 📝 Uso

1. Inicie sesión en el sistema usando las credenciales predeterminadas.
2. Navegue en el panel lateral a través de los diversos módulos del inventario.
3. Utilice los formularios estructurados en pasos (como en el módulo de *Compras* y *Envíos*) para el registro exacto de cantidades y productos.
4. Revise el módulo de *Movimientos de Inventario* para confirmar que el stock se ajusta y actualiza dinámicamente cuando entran pedidos o se anula una operación.
5. Supervise la operación diaria mediante los apartados de *Producciones*, y consulte o agregue nuevas *Sucursales* catalogando a los *Empleados* respectivos.

## 📚 Estructura del Proyecto

- `app/Models/` - Contiene todos los modelos de negocio y sus relaciones funcionales.
- `app/Observers/` - Agrupa las funciones delegadas al Observer Pattern (ej. `DetalleComprasObserver`, `ComprasObserver`) que automatizan el inventario.
- `app/Filament/Resources/` - Contiene los Recursos del ecosistema de Filament, integrando listas cruzadas y formularios complejos (`ProductosResource`, `EnviosResource`, etc.).
- `database/migrations/` - Estructura tabular y física de la base de datos relacional.
- `resources/` - Vistas personalizadas y layouts de la aplicación.
- `public/` - Directorio de archivos y carpetas directamente accesibles de modo público.

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor, considera solicitar revisión antes de enviar *Pull Requests* de cambios mayores.

---

Desarrollado por Aristides De Jesús Alvarenga Sibirian
