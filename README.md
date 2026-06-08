# Sistema de Ventas con Flujo de Caja en Codeigniter 4

# 🛒 Base de Datos — Sistema de Ventas

Script SQL listo para usar con **CodeIgniter 4**. Contiene la estructura completa de tablas y datos de ejemplo para arrancar el proyecto sin configuración adicional.

---

## 📋 Requisitos

- MySQL 5.7 o superior
- PHP 7.4+ con CodeIgniter 4
- HeidiSQL, phpMyAdmin, o cualquier cliente MySQL

---

## 🗄️ Tablas incluidas

| Tabla | Descripción |
|---|---|
| `tbl_users` | Usuarios del sistema (admin, vendedores) |
| `tbl_producto` | Catálogo de productos con código de barras |
| `tbl_egreso` | Registro de compras y gastos |
| `tbl_venta` | Cabecera de cada venta realizada |
| `tbl_detalle_venta` | Líneas de detalle por venta (productos, cantidades, costos) |

---

## 🚀 Instalación

### 1. Importar la base de datos

Ejecuta el script en tu cliente MySQL favorito, o desde la terminal:

```bash
mysql -u root -p < ventas.sql
```

Esto creará automáticamente la base de datos `ventas` con todas las tablas y datos de ejemplo.

### 2. Configurar CodeIgniter 4

Abre el archivo `.env` en la raíz de tu proyecto CI4 y ajusta las credenciales:

```env
database.default.hostname = 127.0.0.1
database.default.database = ventas
database.default.username = root
database.default.password = tu_contraseña
database.default.DBDriver = MySQLi
database.default.port     = 3306
```

> 💡 Si no tienes el archivo `.env`, copia `env` → `.env` y descomenta las líneas de base de datos.

### 3. Verificar la conexión

Desde el navegador abre tu proyecto CI4. Si la página carga sin errores de base de datos, ¡estás listo! 🎉

---

## 👤 Usuario por defecto

El script incluye un usuario administrador listo para iniciar sesión:

| Campo | Valor |
|---|---|
| **Usuario** | `admin` |
| **Contraseña** | `admin123` *(ver nota abajo)* |
| **Tipo** | Administrador (`tipo = 1`) |

> ⚠️ La contraseña está hasheada con `bcrypt` (`password_hash` de PHP). Si necesitas cambiarla, genera un nuevo hash desde PHP:
> ```php
> echo password_hash('tu_nueva_contraseña', PASSWORD_BCRYPT);
> ```
> Luego actualiza el campo `password` directamente en la tabla `tbl_users`.

---

## 📦 Datos de ejemplo

El script trae datos de prueba para que puedas explorar el sistema de inmediato:

- **5 productos** registrados (gafas, casacas, camisas, zapatillas)
- **4 ventas** realizadas con sus respectivos detalles
- **4 egresos** de compra de materiales

---

## 🔗 Relaciones entre tablas

```
tbl_users          tbl_egreso
     |                  |
     |            (sin FK, independiente)
     |
tbl_producto ─────────────┐
                          │
tbl_venta ────────────────┤
                          ▼
                  tbl_detalle_venta
```

`tbl_detalle_venta` depende de `tbl_venta` y `tbl_producto`, por eso el script las crea en el orden correcto para respetar las claves foráneas.

---

## 🛠️ Notas adicionales

- El charset de la base de datos es `utf8` / `utf8_unicode_ci`.
- Todos los campos `status` usan `tinyint(1)`: `1` = activo, `0` = inactivo.
- El campo `costo_delivery` en `tbl_detalle_venta` está disponible si tu negocio maneja envíos.

---

## 📄 Licencia

Proyecto de uso libre. Modifícalo según las necesidades de tu negocio. 😊