# Vistas Blade

Stack frontend: **Blade + Tailwind CSS v4 + Alpine.js 3.x**  
Compilación con Vite (28KB CSS + 92KB JS).

---

## Estructura de vistas

```
resources/views/
├── layouts/
│   ├── app.blade.php          # Layout principal ERP (sidebar, topbar, Alpine.js)
│   └── guest.blade.php        # Layout para login
│
├── components/                # Componentes Blade reutilizables
│   ├── card-kpi.blade.php     # Tarjeta de indicador clave + firma visual
│   ├── card-alerta.blade.php  # Banner de atención requerida
│   ├── card-resumen.blade.php # Contenedor de rankings (clientes/proveedores)
│   ├── card-actividad.blade.php # Últimos movimientos del Kardex
│   └── badge-estado.blade.php # Píldoras de estado (Pagada, Pendiente, etc.)
│
├── auth/
│   └── login.blade.php        # Pantalla de inicio de sesión
│
├── dashboard/
│   ├── index.blade.php        # Contenedor dinámico según rol
│   └── partials/              # Vistas específicas por perfil
│       ├── admin.blade.php    # Visión macro financiera y utilidad
│       ├── vendedor.blade.php # Ventas hoy y borrador mostrador
│       └── deposito.blade.php # Reposición stock crítico y kardex
│
├── productos/                 # ABM Productos
│   ├── index.blade.php        # Listado con filtros + paginación
│   ├── create.blade.php       # Formulario de creación
│   ├── edit.blade.php         # Formulario de edición
│   ├── show.blade.php         # Detalle + movimientos de stock
│   └── form.blade.php         # Partial del formulario
│
├── clientes/                  # ABM Clientes
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── form.blade.php
│
├── proveedores/               # ABM Proveedores
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── form.blade.php
│
├── compras/                   # Módulo Compras
│   ├── index.blade.php        # Listado con filtros
│   ├── create.blade.php       # Formulario (incluye form.blade)
│   ├── form.blade.php         # Formulario con Alpine.js (items dinámicos)
│   └── show.blade.php         # Detalle + PDF
│
├── ventas/                    # Módulo Ventas
│   ├── index.blade.php        # Listado con filtros
│   ├── create.blade.php       # Formulario (incluye form.blade)
│   ├── form.blade.php         # Formulario con Alpine.js (items dinámicos)
│   └── show.blade.php         # Detalle + PDF
│
├── inventario/                # Control de Stock
│   ├── index.blade.php        # Listado general con filtro por categoría
│   ├── kardex.blade.php       # Movimientos de un producto
│   └── stock-bajo.blade.php   # Productos con stock bajo
│
├── reportes/
│   └── index.blade.php        # Panel de exportación PDF/Excel
│
├── pdfs/                      # Vistas para DomPDF
│   ├── stock.blade.php        # Reporte PDF consolidado de stock
│   ├── ventas.blade.php       # Reporte PDF consolidado de ventas
│   ├── compras.blade.php      # Reporte PDF consolidado de compras
│   ├── venta_detalle.blade.php # Comprobante PDF individual de venta A4
│   └── compra_detalle.blade.php# Orden PDF individual de compra A4
│
└── perfil/
    └── edit.blade.php         # Edición de perfil + cambio password
```


---

## Vistas principales

### Login (`auth/login.blade.php`)
- Formulario con campos email y password
- Checkbox "Recordarme"
- Estilo minimalista con layout `guest`
- Sin registro público: los usuarios se crean con los seeders

### Dashboard (`dashboard/index.blade.php`)
- 3 tarjetas de resumen (ventas del mes, productos más vendidos, inventario)
- Gráfico simple de ventas por día (tabla de datos)
- Salud del stock bajo

### Productos — Listado (`productos/index.blade.php`)
- Tabla con columnas: SKU, Nombre, Categoría, Precio Venta, Stock Actual, Stock Mínimo, Estado, Acciones
- Filtros: búsqueda por nombre/SKU + selector de categoría
- Paginado a 15 registros
- Indicador visual de stock bajo (rojo) vs disponible (verde)
- Acciones: Ver, Editar, Eliminar (con confirmación)

### Compras — Formulario (`compras/form.blade.php`)
- Alpine.js con `x-data="compraForm()"`
- Selector de proveedor
- Tabla dinámica de productos con:
  - Select de producto: precarga el costo unitario actual del producto (editable; al confirmar la compra actualiza el `precio_costo`)
  - Cantidad ajustable
  - Subtotal calculado automáticamente
- Totales en vivo: Subtotal, IVA (21%), Total
- Botón "Añadir Producto" para nuevas filas

### Ventas — Formulario (`ventas/form.blade.php`)
- Misma estructura que compras pero orientado a venta
- Selector de cliente + tipo de comprobante
- Al elegir el producto se precarga su precio de venta (editable) y se avisa si la cantidad supera el stock disponible
- Cálculo de IVA 21% en vivo

### Inventario — Listado (`inventario/index.blade.php`)
- Tabla completa con todos los productos
- Filtro por categoría y búsqueda
- Stock actual, stock mínimo, precio costo y precio venta
- Enlaces a kardex individual de cada producto

---

## Componentes compartidos

### Layout `layouts/app.blade.php`
- Barra lateral (sidebar) con navegación agrupada por rol:
  - **Dashboard**
  - **Gestión**: Productos, Clientes, Proveedores
  - **Movimientos**: Compras, Ventas
  - **Inventario**: Stock general, Kardex, Stock bajo
  - **Reportes**: Exportaciones PDF/Excel
  - **Perfil**: Configuración de usuario
- Menú responsive con Alpine.js (toggle en mobile)
- Nombre de usuario actual + botón de logout

### `productos/form.blade.php`
- Campos: SKU, Nombre, Categoría (select), Descripción, Unidad de Medida, Precio Costo, Precio Venta, Stock Mínimo, Stock Actual
- Validación con errores de Laravel `@error`
- Reutilizado por create y edit

### `clientes/form.blade.php` y `proveedores/form.blade.php`
- Campos: Razón Social, CUIT/DNI, Condición IVA (select), Teléfono, Email, Dirección
- Misma estructura de validación

---

## Vistas PDF (DomPDF)

### `pdfs/stock.blade.php`
- Tabla con todos los productos activos
- Columnas: SKU, Nombre, Categoría, Precio Costo, Precio Venta, Stock Actual, Valor Total
- Total de valor del inventario al final

### `pdfs/ventas.blade.php`
- Filtro por rango de fechas (desde/hasta)
- Por cada venta: comprobante, cliente, fecha, total
- TOTAL GENERAL al final

### `pdfs/compras.blade.php`
- Similar a ventas pero con datos de compras
- Por cada compra: orden, proveedor, fecha, total
- TOTAL GENERAL al final
