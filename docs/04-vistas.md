# Vistas Blade

Stack frontend: **Blade + Tailwind CSS v4 + Alpine.js 3.x**  
Compilación con Vite (~38 KB CSS + ~92 KB JS).

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

### Dashboard (`dashboard/index.blade.php` + `dashboard/partials/*`)
- Contenedor que incluye un panel distinto según el rol del usuario, con banner de alertas (compras pendientes, ventas borrador, stock crítico)
- **Admin**: KPIs (ventas del período, ventas de hoy, utilidad estimada, valor del inventario), Top 5 clientes y proveedores del mes, ventas y compras pendientes, últimos movimientos del kardex
- **Vendedor**: ventas de hoy, facturas pendientes de cobro/emisión y productos más vendidos
- **Deposito**: productos en stock crítico (con acceso a generar compra), compras esperando ingreso, productos sin movimiento en 30 días y últimos movimientos de almacén

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

### Reportes (`reportes/index.blade.php`)
- Tarjeta de stock actual: PDF y Excel (todos los roles)
- Tarjetas de ventas y compras por período (desde/hasta, por defecto el mes en curso), cada una con PDF y Excel
- Las tarjetas de ventas y compras se muestran solo con `@can('exportar', …)`: ventas para Admin/Vendedor, compras para Admin/Deposito (el controller también lo valida)

---

## Componentes compartidos

### Layout `layouts/app.blade.php`
- Barra lateral (sidebar) colapsable con navegación agrupada; cada ítem se muestra según la policy (`@can('viewAny', …)`):
  - **Operaciones**: Dashboard, Ventas, Compras
  - **Inventario & Catálogo**: Productos, Kardex Inventario
  - **Gestión & Reportes**: Clientes, Proveedores, Reportes
- Topbar con breadcrumb, badge del rol y botón "Nueva Venta" (solo si `@can('create', Venta::class)`)
- Tarjeta del usuario actual (nombre + rol) con botón de logout
- El perfil (`/perfil`) no tiene enlace en el menú

### `productos/form.blade.php`
- Campos: SKU, Nombre, Categoría (select), Descripción, Unidad de Medida, Precio Costo, Precio Venta, Stock Mínimo, Stock Actual
- El Stock Actual no se guarda directo: la diferencia con el stock vigente se registra en el kardex como ajuste (`ajuste_entrada` / `ajuste_salida`) vía `ProductoService`
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
