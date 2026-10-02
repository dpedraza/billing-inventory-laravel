# Seeders — Datos Demo

`php artisan migrate --seed` deja el sistema listo para probar. `DatabaseSeeder` ejecuta, en orden:

| Seeder | Contenido |
|---|---|
| `RoleSeeder` | Roles `Admin`, `Vendedor`, `Deposito` (guard `web`) |
| `CatalogoSeeder` | Condiciones de IVA, tipos de comprobante, categorías y configuraciones |
| `UserSeeder` | Un usuario por rol |
| `ProductoSeeder` | 24 productos (stock inicial 0) |
| `ClienteSeeder` | 10 clientes |
| `ProveedorSeeder` | 5 proveedores |
| `OperacionesDemoSeeder` | ~5 meses de compras y ventas generadas con los servicios de negocio |

Todos los datos son **ficticios**: emails `@example.com`, direcciones inventadas, teléfonos con número de abonado que empieza en 0 (no discable) y CUIT/CUIL inventados con dígito verificador válido (módulo 11).

---

## Usuarios

Contraseña de todos: `password`

| Nombre | Email | Rol |
|---|---|---|
| Administrador Demo | `admin@demo.test` | Admin |
| Vendedor Demo | `vendedor@demo.test` | Vendedor |
| Depósito Demo | `deposito@demo.test` | Deposito |

---

## Catálogos (`CatalogoSeeder`)

**Condiciones de IVA:** Responsable Inscripto, Monotributista, Exento, Consumidor Final, Sujeto No Categorizado.

**Tipos de comprobante:**

| Código | Nombre |
|---|---|
| `FC-A` | Factura A |
| `FC-B` | Factura B |
| `REM` | Remito |
| `PRES` | Presupuesto |

**Categorías:** Electrónica, Indumentaria, Alimentos, Limpieza, Herramientas.

**Configuraciones:**

| Clave | Valor |
|---|---|
| `allow_negative_stock` | `false` |
| `iva_porcentaje` | `21` |

---

## Productos, clientes y proveedores

- **Productos:** 24 SKUs (`TEC-001` … `HER-005`), 4–6 por categoría. Se crean con stock 0: todo el stock entra por compras confirmadas o ajustes, así el kardex queda completo desde el primer movimiento.
- **Clientes:** 4 Responsables Inscriptos, 1 Exento, 2 Monotributistas y 3 Consumidores Finales.
- **Proveedores:** uno por rubro (tecnología, textil, alimentos, limpieza, herramientas).

---

## Operaciones (`OperacionesDemoSeeder`)

Simula ~150 días de actividad hasta la fecha actual. **Todas** las operaciones pasan por los servicios de negocio —`CompraService` y `VentaService` (`crear()` → `confirmar()` → `anular()`) y `StockService::ajustarStock()` para el conteo físico—, de modo que `stock_actual`, `stock_movements` y la numeración de comprobantes quedan consistentes. No se insertan filas directamente.

- Compra inicial de apertura por proveedor y reposiciones periódicas de los productos con stock bajo.
- Ventas en días hábiles a clientes ponderados por frecuencia (Factura A a Responsables Inscriptos, Factura B al resto).
- Una compra anulada (lote devuelto al proveedor) y dos ventas anuladas: la anulación genera el movimiento inverso en el kardex.
- Pendientes del día: una venta, un presupuesto y dos compras sin confirmar.
- Un conteo físico de depósito detecta 3 bidones de lavandina rotos y corrige el stock con un `ajuste_salida` vía `StockService::ajustarStock()`.
- Una venta mayorista 3 días antes de hoy deja cuatro productos por debajo del stock mínimo (alertas del dashboard), que luego no se venden ni se reponen; así el resultado no depende de la fecha de ejecución. La amoladora no registra movimientos en los últimos 30 días.

Cada operación se fecha con `Carbon::setTestNow()`, por lo que el `created_at` de compras, ventas y movimientos coincide con `fecha_emision`. Al terminar, el seeder verifica que el `stock_actual` de cada producto sea igual a la suma de su kardex.

---

## Idempotencia

Los seeders de catálogos y maestros usan `firstOrCreate()`. `OperacionesDemoSeeder` se omite si ya existen compras o ventas. Por eso `php artisan db:seed` puede ejecutarse varias veces sin duplicar datos (el contenedor Docker lo ejecuta en cada arranque).

```bash
docker compose exec app php artisan db:seed
docker compose exec app php artisan db:seed --class=RoleSeeder
```
