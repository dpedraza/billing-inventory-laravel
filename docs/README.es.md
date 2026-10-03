# Sistema de Facturación y Gestión de Inventario

> 🇬🇧 [English version](../README.md)

Sistema web monolítico para la gestión de facturación, control de stock y administración de productos, clientes y proveedores. Orientado a PyMEs argentinas.

**Stack:** Laravel 12 · PHP 8.3+ (desarrollado y dockerizado en PHP 8.5) · PostgreSQL 17 · Blade + Tailwind CSS v4 + Alpine.js 3 · Spatie Permission v6 · DomPDF · OpenSpout

---

## Funcionalidades

- **Roles y permisos**: Admin, Vendedor y Deposito (Spatie Permission v6 + Laravel Policies).
- **Dashboard adaptado por rol**: Admin (ventas, utilidad estimada, valor del inventario, rankings), Vendedor (ventas del día y pendientes), Deposito (stock crítico, compras por recibir, productos sin movimiento).
- **Productos** con categorías, SKU único, precios y stock mínimo.
- **Clientes y proveedores** con condición frente al IVA y CUIT/DNI único.
- **Compras**: carga con ítems dinámicos (Alpine.js), IVA 21 %, ciclo pendiente → completada → anulada.
- **Ventas**: ítems dinámicos que precargan el precio de venta del producto (editable) y avisan si la cantidad supera el stock; numeración correlativa por tipo de comprobante (`FC-A-001`, `FC-B-001`…), ciclo pendiente → pagada → anulada.
- **Stock transaccional**: `DB::transaction` + `lockForUpdate()`, control de stock negativo configurable y kardex (`stock_movements`) con metadata JSONB. El stock cargado en el formulario de producto (inicial o conteo físico) se registra como ajuste en el kardex.
- **Reportes**: PDF (DomPDF) de stock, ventas y compras por período, comprobante individual de venta y orden de compra; Excel (OpenSpout) de stock, ventas y compras por período.

---

## Ejecución con Docker (recomendado)

```bash
cp .env.example .env
docker compose up -d
```

El primer arranque instala dependencias (Composer y pnpm), compila los assets, genera `APP_KEY`, ejecuta `migrate --seed` y crea `storage:link`. Seguir el progreso con `docker compose logs -f app`.

- App: http://localhost:8080 (configurable con `APP_PORT`)
- PostgreSQL desde el host: `127.0.0.1:5433` (configurable con `FORWARD_DB_PORT`)

```bash
docker compose exec app php artisan test    # tests
docker compose exec app php artisan db:seed # seeders (idempotentes)
docker compose down                         # detener (conserva los datos)
```

---

## Instalación local (sin Docker)

Requisitos: PHP 8.3+ con `pdo_pgsql`, `mbstring`, `zip`, `gd`, `intl`, `bcmath`; Composer 2; Node.js 22+ y pnpm; PostgreSQL 17.

```bash
composer install
pnpm install && pnpm run build
cp .env.example .env            # ajustar DB_* a tu PostgreSQL
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

---

## Usuarios demo

Contraseña de todos: `password`

| Rol | Email |
|---|---|
| Admin | `admin@demo.test` |
| Vendedor | `vendedor@demo.test` |
| Deposito | `deposito@demo.test` |

No hay registro público: los usuarios se crean desde los seeders.

---

## Tests

```bash
php artisan test                                  # local
docker compose exec app php artisan test          # Docker
php artisan test --filter=test_full_sale_flow     # un test puntual
```

Los tests corren contra PostgreSQL real, en la base `testing` (en Docker se crea automáticamente al iniciar el contenedor de la base). Detalle en [06-pruebas.md](06-pruebas.md).

---

## Documentación técnica

| Archivo | Contenido |
|---|---|
| [00-indice.md](00-indice.md) | Índice y configuración del entorno |
| [01-modelo-datos.md](01-modelo-datos.md) | Modelo entidad-relación, migraciones, enums |
| [02-estructura-proyecto.md](02-estructura-proyecto.md) | Arquitectura, modelos, servicios, policies |
| [03-endpoints.md](03-endpoints.md) | Rutas web por módulo |
| [04-vistas.md](04-vistas.md) | Estructura de vistas Blade |
| [05-seeders.md](05-seeders.md) | Datos demo |
| [06-pruebas.md](06-pruebas.md) | Tests automatizados |

---

## Licencia

[MIT](../LICENSE)
