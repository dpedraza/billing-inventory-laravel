# Estructura del Proyecto

Arquitectura monolítica con Blade (sin API). Sigue el patrón:
**Controller → Service ↔ Model → Policy | FormRequest**

```
app/
├── Console/
├── Enums/                          # Enums con tipo string (PHP 8.1+)
│   ├── CompraEstado.php
│   ├── MovimientoTipo.php
│   └── VentaEstado.php
│
├── Exceptions/
│
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php   # Login/logout
│   │   ├── Controller.php                            # Base abstracta
│   │   └── Web/
│   │       ├── DashboardController.php               # Panel principal
│   │       ├── ProductoController.php                 # CRUD productos
│   │       ├── ClienteController.php                  # CRUD clientes
│   │       ├── ProveedorController.php                # CRUD proveedores
│   │       ├── CompraController.php                   # Compras + confirmar/anular
│   │       ├── VentaController.php                    # Ventas + confirmar/anular
│   │       ├── InventarioController.php               # Stock + kardex
│   │       ├── ReporteController.php                  # Exportación PDF/Excel
│   │       └── PerfilController.php                   # Perfil de usuario
│   │
│   └── Requests/
│       ├── StoreClienteRequest.php
│       ├── UpdateClienteRequest.php
│       ├── StoreProveedorRequest.php
│       ├── UpdateProveedorRequest.php
│       ├── StoreProductoRequest.php
│       ├── UpdateProductoRequest.php
│       ├── StoreCompraRequest.php
│       └── StoreVentaRequest.php
│
├── Livewire/                       # (no usado — solo Alpine.js)
│
├── Models/
│   ├── Categoria.php
│   ├── Cliente.php
│   ├── Compra.php
│   ├── CompraItem.php
│   ├── CondicionIva.php
│   ├── Configuracion.php
│   ├── Producto.php
│   ├── Proveedor.php
│   ├── StockMovement.php
│   ├── TipoComprobante.php
│   ├── User.php                    # Spatie HasRoles trait
│   ├── Venta.php
│   └── VentaItem.php
│
├── Providers/
│   ├── AppServiceProvider.php
│   └── AuthServiceProvider.php     # Registro de políticas
│
├── Policies/
│   ├── ProductoPolicy.php
│   ├── ClientePolicy.php
│   ├── ProveedorPolicy.php
│   ├── CompraPolicy.php
│   └── VentaPolicy.php
│
└── Services/
    ├── DashboardService.php        # Métricas del panel
    ├── ProductoService.php         # Alta/edición de productos (stock vía ajustes)
    ├── CompraService.php           # Lógica de compras + stock
    ├── VentaService.php            # Lógica de ventas + stock
    ├── StockService.php            # Control de stock transaccional
    └── ReporteService.php          # Exportaciones PDF/Excel
```

---

## Models — Detalle

### `User` (extends `Authenticatable`)
| Propiedad | Descripción |
|---|---|
| Traits | `HasFactory`, `HasRoles` (Spatie), `Notifiable`, `SoftDeletes` |
| fillable | name, email, password, is_active |
| casts | password → hashed, is_active → boolean |
| Relaciones | productosCreados, productosActualizados |

### `Producto`
| Propiedad | Descripción |
|---|---|
| SoftDeletes | Sí |
| fillable | sku, nombre, descripcion, categoria_id, unidad_medida, precio_costo, precio_venta, stock_minimo, stock_actual, activo, created_by, updated_by |
| casts | precio_costo, precio_venta, stock_minimo, stock_actual → decimal:2 |
| Relaciones | categoria(), creador(), actualizador(), stockMovements(), compraItems(), ventaItems() |
| Métodos | `getStockBajoAttribute(): bool` — true si stock_actual ≤ stock_minimo |

### `Compra`
| Propiedad | Descripción |
|---|---|
| SoftDeletes | Sí |
| casts | estado → `CompraEstado`, fecha_emision → date, subtotal/impuesto/total → decimal:2 |
| Relaciones | proveedor(), creador(), actualizador(), items() |

### `Venta`
| Propiedad | Descripción |
|---|---|
| SoftDeletes | Sí |
| casts | estado → `VentaEstado`, fecha_emision → date, subtotal/impuesto/total → decimal:2 |
| Relaciones | cliente(), tipoComprobante(), creador(), actualizador(), items() |

### `StockMovement`
| Propiedad | Descripción |
|---|---|
| casts | tipo_movimiento → `MovimientoTipo`, cantidad/costo_unitario/saldo_anterior/saldo_posterior → decimal:2, metadata → array |
| Relaciones | producto(), creador() |
| Polimórfico | referencia_type, referencia_id — apunta a compras o ventas |

---

## Services — Detalle

### `StockService`
```php
registrarMovimiento(Producto $producto, MovimientoTipo $tipo, float $cantidad,
    float $costoUnitario, ?Model $referencia = null, ?array $metadata = []): StockMovement
```
- Lockea la fila del producto con `lockForUpdate()` (FOR UPDATE)
- Rechaza stock negativo a menos que `allow_negative_stock` = true en configuraciones
- Registra saldo_anterior y saldo_posterior
- Actualiza `productos.stock_actual`

```php
ajustarStock(Producto $producto, float $stockObjetivo, string $motivo = 'ajuste_manual'): ?StockMovement
```
- Lockea el producto, calcula la diferencia con el stock objetivo y registra `AjusteEntrada` o `AjusteSalida` (vía `registrarMovimiento`)
- No registra nada si el stock ya coincide; guarda `motivo` y `stock_objetivo` en `metadata`

```php
anularMovimientos(Model $referencia): void
```
- Crea movimientos inversos (AnulacionCompra / AnulacionVenta)

```php
getKardex(Producto $producto, ?array $filters = []): LengthAwarePaginator
```
- Filtrable por tipo, desde, hasta

### `ProductoService`
| Método | Función |
|---|---|
| `crear(array $data): Producto` | Transacción: crea el producto con stock 0 y registra el stock inicial como ajuste de entrada (motivo `stock_inicial`). |
| `actualizar(Producto $producto, array $data): Producto` | Transacción: actualiza los datos y toma `stock_actual` como conteo físico; la diferencia se registra como ajuste (motivo `ajuste_manual`). |

`stock_actual` nunca se escribe directamente desde los formularios: siempre coincide con la suma del kardex.

### `CompraService`
| Método | Función |
|---|---|
| `crear(array $data, array $items): Compra` | Crea compra + items. NO modifica stock. |
| `confirmar(Compra $compra): Compra` | Transacción: por cada item, registra movimiento de stock (+), actualiza precio_costo del producto, cambia estado a Completada. |
| `anular(Compra $compra): Compra` | Transacción: anula movimientos de stock, cambia a Anulada. |

### `VentaService`
| Método | Función |
|---|---|
| `crear(array $data, array $items): Venta` | Crea venta + items + número de comprobante correlativo (ej. FC-B-001). NO modifica stock. |
| `confirmar(Venta $venta): Venta` | Transacción: por cada item, registra movimiento de stock (-), cambia estado a Pagada. |
| `anular(Venta $venta): Venta` | Transacción: anula movimientos de stock, cambia a Anulada. |

### `DashboardService`
| Método | Retorno / Descripción |
|---|---|
| `getResumenVentas(?string $fechaDesde, ?string $fechaHasta): array` | total_ventas, total_impuesto, cantidad_ventas, total_ventas_hoy, cantidad_ventas_hoy, utilidad_estimada |
| `getProductosMasVendidos(int $limit = 10): Collection` | Top N productos con total_cantidad y total_ingresos |
| `getResumenInventario(): array` | total_productos, productos_activos, stock_bajo, valor_inventario |
| `getProductosStockCritico(int $limit = 5): Collection` | Productos activos donde stock_actual ≤ stock_minimo |
| `getProductosSinMovimiento(int $days = 30, int $limit = 5): Collection` | Productos activos sin movimientos de stock en los últimos N días |
| `getVentasPendientes(int $limit = 5): Collection` | Ventas en estado borrador / pendiente |
| `getComprasPendientes(int $limit = 5): Collection` | Compras en estado pendiente de confirmación |
| `getTopClientes(int $limit = 5): Collection` | Ranking Top 5 clientes por facturación total del mes |
| `getTopProveedores(int $limit = 5): Collection` | Ranking Top 5 proveedores por compras del mes |
| `getMovimientosRecientes(int $limit = 8): Collection` | Últimos movimientos de Kardex con producto y creador |
| `getAlertasAtencion(): array` | Conteo consolidado de stock crítico, compras pendientes, ventas pendientes y sin movimiento |
| `getDashboardDataForUser(User $user): array` | Empaqueta todas las métricas requeridas adaptadas al rol del usuario |


### `ReporteService`
| Método | Descripción |
|---|---|
| `exportarStockPDF(): DomPDF` | Todos los productos con categoría |
| `exportarVentasPDF(?string $fechaDesde, ?string $fechaHasta): DomPDF` | Ventas filtradas por fecha |
| `exportarComprasPDF(?string $fechaDesde, ?string $fechaHasta): DomPDF` | Compras filtradas por fecha |
| `exportarVentaIndividualPDF(Venta $venta): DomPDF` | Generación de comprobante PDF A4 individual de Venta |
| `exportarCompraIndividualPDF(Compra $compra): DomPDF` | Generación de orden PDF A4 individual de Compra |
| `exportarStockExcel(): StreamedResponse` | XLSX de stock (OpenSpout) |
| `exportarVentasExcel(?string $fechaDesde, ?string $fechaHasta): StreamedResponse` | XLSX de ventas filtradas por fecha |
| `exportarComprasExcel(?string $fechaDesde, ?string $fechaHasta): StreamedResponse` | XLSX de compras filtradas por fecha |

Los Excel se escriben directamente en la respuesta (`response()->streamDownload()` + `php://output`) y leen los registros por lotes con `lazy()`.

---

## Policies — Matriz de Permisos

| Módulo | viewAny / view | create | update | delete | confirmar | anular | exportar (reporte) |
|---|---|---|---|---|---|---|---|
| **Productos** | Todos | Admin, Depósito | Admin, Depósito | Admin | — | — | — |
| **Clientes** | Todos | Admin, Vendedor | Admin, Vendedor | Admin | — | — | — |
| **Proveedores** | Todos | Admin, Depósito | Admin, Depósito | Admin | — | — | — |
| **Compras** | Admin, Depósito | Admin, Depósito | Admin, Depósito | Admin | Admin, Depósito | Admin | Admin, Depósito |
| **Ventas** | Todos | Admin, Vendedor | Admin, Vendedor | Admin | Admin, Vendedor | Admin | Admin, Vendedor |

Inventario, kardex, reporte de stock y perfil están disponibles para cualquier usuario autenticado.

---

## FormRequests — Validación

Cada Request verifica:
- **authorize()**: policy correspondiente del modelo
- **rules()**: validaciones de Laravel con reglas únicas (sku, cuit_dni), exists, numeric min:0, etc.

Ejemplo `StoreVentaRequest`:
```php
'cliente_id'          => 'required|exists:clientes,id',
'tipo_comprobante_id' => 'required|exists:tipos_comprobante,id',
'fecha_emision'       => 'required|date',
'items'               => 'required|array|min:1',
'items.*.producto_id' => 'required|exists:productos,id',
'items.*.cantidad'    => 'required|numeric|min:0.01',
'items.*.precio_unitario' => 'required|numeric|min:0',
```
