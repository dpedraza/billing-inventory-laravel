# Modelo de Datos

Base de datos: PostgreSQL 17  
Base: `sistema_facturacion`

---

## Diagrama Entidad-Relación (textual)

```
┌───────────────────┐       ┌─────────────────────┐
│    categorias      │       │   condiciones_iva    │
├───────────────────┤       ├─────────────────────┤
│ id (PK)           │       │ id (PK)              │
│ nombre             │       │ nombre               │
│ descripcion        │       │ activo               │
│ activo             │       └──────────┬──────────┘
└────────┬──────────┘                  │
         │                            ├───────────────────────┐
         │ 1:N                        │ 1:N                   │ 1:N
         ▼                            ▼                       ▼
┌───────────────────┐       ┌─────────────────────┐  ┌──────────────────────┐
│    productos       │       │     clientes         │  │    proveedores        │
├───────────────────┤       ├─────────────────────┤  ├──────────────────────┤
│ id (PK)           │       │ id (PK)              │  │ id (PK)               │
│ sku (UQ)          │       │ razon_social         │  │ razon_social          │
│ nombre             │       │ cuit_dni (UQ)        │  │ cuit_dni (UQ)         │
│ descripcion        │       │ condicion_iva_id(FK) │  │ condicion_iva_id(FK)  │
│ categoria_id (FK)  │       │ telefono             │  │ telefono              │
│ unidad_medida      │       │ email                │  │ email                 │
│ precio_costo       │       │ direccion            │  │ direccion             │
│ precio_venta       │       │ activo               │  │ activo                │
│ stock_minimo       │       │ created_by (FK)      │  │ created_by (FK)       │
│ stock_actual *     │       │ updated_by (FK)      │  │ updated_by (FK)       │
│ activo *           │       └─────────────────────┘  └──────────────────────┘
│ created_by (FK)    │
│ updated_by (FK)    │
└────────┬──────────┘
         │
         ├───────────────────────────────────────────┐
         │ 1:N                                       │ 1:N
         ▼                                           ▼
┌──────────────────────┐              ┌──────────────────────┐
│   stock_movements    │              │    compra_items       │
├──────────────────────┤              ├──────────────────────┤
│ id (PK)              │              │ id (PK)               │
│ producto_id (FK) *   │              │ compra_id (FK)        │
│ tipo_movimiento      │              │ producto_id (FK)      │
│ referencia_type (M)  │              │ cantidad              │
│ referencia_id (M)    │              │ costo_unitario        │
│ cantidad              │              │ subtotal              │
│ costo_unitario       │              └──────────┬───────────┘
│ saldo_anterior       │                         │
│ saldo_posterior      │                         │ N:1
│ created_by (FK)      │                         ▼
│ metadata (JSONB)     │              ┌──────────────────────┐
└──────────────────────┘              │      compras          │
                                      ├──────────────────────┤
┌──────────────────────┐              │ id (PK)               │
│   venta_items        │              │ proveedor_id (FK)     │
├──────────────────────┤              │ numero_orden          │
│ id (PK)              │              │ subtotal              │
│ venta_id (FK)        │              │ impuesto              │
│ producto_id (FK)     │              │ total                 │
│ cantidad              │              │ estado *              │
│ precio_unitario      │              │ fecha_emision         │
│ subtotal              │              │ notas                 │
└──────────┬───────────┘              │ created_by (FK)       │
           │                          │ updated_by (FK)       │
           │ N:1                      └──────────────────────┘
           ▼
┌──────────────────────┐
│      ventas           │         ┌──────────────────────┐
├──────────────────────┤         │  tipos_comprobante    │
│ id (PK)               │         ├──────────────────────┤
│ cliente_id (FK)       │         │ id (PK)               │
│ tipo_comprobante(FK)  │◄────────│ codigo               │
│ numero_comprobante(UQ)│         │ nombre               │
│ subtotal              │         │ activo               │
│ impuesto              │         └──────────────────────┘
│ total                 │
│ estado *              │         ┌──────────────────────┐
│ fecha_emision *       │         │ configuraciones      │
│ notas                 │         ├──────────────────────┤
│ created_by (FK)       │         │ id (PK)               │
│ updated_by (FK)       │         │ clave (UQ)           │
└──────────────────────┘         │ valor                │
                                  └──────────────────────┘

Leyenda:
  (PK)  = Primary Key
  (FK)  = Foreign Key
  (UQ)  = Unique
  (M)   = Morph (polimórfico)
  *     = Indexed
```

---

## Migraciones (orden de ejecución)

### `0001_01_01_000000_create_users_table.php`
```sql
CREATE TABLE users (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password        VARCHAR(255) NOT NULL,
    is_active       BOOLEAN DEFAULT true,
    remember_token  VARCHAR(100) NULL,
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);

CREATE TABLE password_reset_tokens (
    email       VARCHAR(255) PRIMARY KEY,
    token       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NULL
);

CREATE TABLE sessions (
    id            VARCHAR(255) PRIMARY KEY,
    user_id       BIGINT NULL REFERENCES users(id),
    ip_address    VARCHAR(45) NULL,
    user_agent    TEXT NULL,
    payload       LONGTEXT NOT NULL,
    last_activity INTEGER NOT NULL INDEX
);
```

### `2026_07_28_000001_create_categorias_table.php`
```sql
CREATE TABLE categorias (
    id          BIGSERIAL PRIMARY KEY,
    nombre      VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    activo      BOOLEAN DEFAULT true,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

### `2026_07_28_000002_create_condiciones_iva_table.php`
```sql
CREATE TABLE condiciones_iva (
    id         BIGSERIAL PRIMARY KEY,
    nombre     VARCHAR(255) NOT NULL,
    activo     BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### `2026_07_28_000003_create_tipos_comprobante_table.php`
```sql
CREATE TABLE tipos_comprobante (
    id         BIGSERIAL PRIMARY KEY,
    codigo     VARCHAR(20) NOT NULL,
    nombre     VARCHAR(255) NOT NULL,
    activo     BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### `2026_07_28_000004_create_productos_table.php`
```sql
CREATE TABLE productos (
    id              BIGSERIAL PRIMARY KEY,
    sku             VARCHAR(50) NOT NULL UNIQUE,
    nombre          VARCHAR(255) NOT NULL,
    descripcion     TEXT NULL,
    categoria_id    BIGINT NULL REFERENCES categorias(id) ON DELETE SET NULL,
    unidad_medida   VARCHAR(20) DEFAULT 'unidad',
    precio_costo    DECIMAL(12,2) DEFAULT 0,
    precio_venta    DECIMAL(12,2) DEFAULT 0,
    stock_minimo    DECIMAL(12,2) DEFAULT 0,
    stock_actual    DECIMAL(12,2) DEFAULT 0,
    activo          BOOLEAN DEFAULT true,
    created_by      BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    updated_by      BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
CREATE INDEX ON productos(stock_actual);
CREATE INDEX ON productos(activo);
```

### `2026_07_28_000005_create_clientes_table.php`
```sql
CREATE TABLE clientes (
    id                BIGSERIAL PRIMARY KEY,
    razon_social      VARCHAR(255) NOT NULL,
    cuit_dni          VARCHAR(20) NOT NULL UNIQUE,
    condicion_iva_id  BIGINT NULL REFERENCES condiciones_iva(id) ON DELETE SET NULL,
    telefono          VARCHAR(50) NULL,
    email             VARCHAR(255) NULL,
    direccion         TEXT NULL,
    activo            BOOLEAN DEFAULT true,
    created_by        BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    updated_by        BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    deleted_at        TIMESTAMP NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL
);
```

### `2026_07_28_000006_create_proveedores_table.php`
```sql
CREATE TABLE proveedores (
    id                BIGSERIAL PRIMARY KEY,
    razon_social      VARCHAR(255) NOT NULL,
    cuit_dni          VARCHAR(20) NOT NULL UNIQUE,
    condicion_iva_id  BIGINT NULL REFERENCES condiciones_iva(id) ON DELETE SET NULL,
    telefono          VARCHAR(50) NULL,
    email             VARCHAR(255) NULL,
    direccion         TEXT NULL,
    activo            BOOLEAN DEFAULT true,
    created_by        BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    updated_by        BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    deleted_at        TIMESTAMP NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL
);
```

### `2026_07_28_000007_create_compras_table.php`
```sql
CREATE TABLE compras (
    id              BIGSERIAL PRIMARY KEY,
    proveedor_id    BIGINT NULL REFERENCES proveedores(id) ON DELETE SET NULL,
    numero_orden    VARCHAR(50) NULL,
    subtotal        DECIMAL(12,2) DEFAULT 0,
    impuesto        DECIMAL(12,2) DEFAULT 0,
    total           DECIMAL(12,2) DEFAULT 0,
    estado          VARCHAR(255) DEFAULT 'pendiente',
    fecha_emision   DATE NOT NULL,
    notas           TEXT NULL,
    created_by      BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    updated_by      BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
CREATE INDEX ON compras(estado);
```

### `2026_07_28_000008_create_compra_items_table.php`
```sql
CREATE TABLE compra_items (
    id              BIGSERIAL PRIMARY KEY,
    compra_id       BIGINT NOT NULL REFERENCES compras(id) ON DELETE CASCADE,
    producto_id     BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    cantidad        DECIMAL(12,2) NOT NULL,
    costo_unitario  DECIMAL(12,2) NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

### `2026_07_28_000009_create_ventas_table.php`
```sql
CREATE TABLE ventas (
    id                  BIGSERIAL PRIMARY KEY,
    cliente_id          BIGINT NULL REFERENCES clientes(id) ON DELETE SET NULL,
    tipo_comprobante_id BIGINT NULL REFERENCES tipos_comprobante(id) ON DELETE SET NULL,
    numero_comprobante  VARCHAR(50) NULL UNIQUE,
    subtotal            DECIMAL(12,2) DEFAULT 0,
    impuesto            DECIMAL(12,2) DEFAULT 0,
    total               DECIMAL(12,2) DEFAULT 0,
    estado              VARCHAR(255) DEFAULT 'pendiente',
    fecha_emision       DATE NOT NULL,
    notas               TEXT NULL,
    created_by          BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    updated_by          BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    deleted_at          TIMESTAMP NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL
);
CREATE INDEX ON ventas(estado);
CREATE INDEX ON ventas(fecha_emision);
```

### `2026_07_28_000010_create_venta_items_table.php`
```sql
CREATE TABLE venta_items (
    id              BIGSERIAL PRIMARY KEY,
    venta_id        BIGINT NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
    producto_id     BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    cantidad        DECIMAL(12,2) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

### `2026_07_28_000011_create_stock_movements_table.php`
```sql
CREATE TABLE stock_movements (
    id                BIGSERIAL PRIMARY KEY,
    producto_id       BIGINT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    tipo_movimiento   VARCHAR(20) NOT NULL,
    referencia_type   VARCHAR(100) NULL,
    referencia_id     BIGINT NULL,
    cantidad          DECIMAL(12,2) NOT NULL,
    costo_unitario    DECIMAL(12,2) DEFAULT 0,
    saldo_anterior    DECIMAL(12,2) DEFAULT 0,
    saldo_posterior   DECIMAL(12,2) DEFAULT 0,
    created_by        BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    metadata          JSONB NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL
);
CREATE INDEX ON stock_movements(producto_id);
CREATE INDEX ON stock_movements(created_at);
CREATE INDEX ON stock_movements(referencia_type, referencia_id);
```

### `2026_07_28_000012_create_configuraciones_table.php`
```sql
CREATE TABLE configuraciones (
    id         BIGSERIAL PRIMARY KEY,
    clave      VARCHAR(100) NOT NULL UNIQUE,
    valor      TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tablas de Spatie Permission
6 tablas auto-generadas por `Spatie\Permission\Models\Permission`:
- `permissions` — permisos del sistema
- `roles` — roles (Admin, Vendedor, Deposito)
- `model_has_permissions` — asignación directa de permisos a usuarios (morph)
- `model_has_roles` — asignación de roles a usuarios (morph)
- `role_has_permissions` — permisos asignados a roles

---

## Enums

### `CompraEstado` (string)
| Case | Valor |
|---|---|
| Pendiente | `'pendiente'` |
| Completada | `'completada'` |
| Anulada | `'anulada'` |

### `VentaEstado` (string)
| Case | Valor |
|---|---|
| Pendiente | `'pendiente'` |
| Pagada | `'pagada'` |
| Anulada | `'anulada'` |

### `MovimientoTipo` (string)
| Case | Valor |
|---|---|
| Compra | `'compra'` |
| Venta | `'venta'` |
| AjusteEntrada | `'ajuste_entrada'` |
| AjusteSalida | `'ajuste_salida'` |
| AnulacionVenta | `'anulacion_venta'` |
| AnulacionCompra | `'anulacion_compra'` |
