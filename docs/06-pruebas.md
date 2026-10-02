# Pruebas

Framework: **PHPUnit** 11
Archivos: `tests/Feature/SistemaTest.php`, `tests/Feature/DemoSeederTest.php`, `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`
Trait: `RefreshDatabase` (cada test se ejecuta en una transacción)
Base: PostgreSQL real, base `testing` (configurada en `phpunit.xml`)

> PostgreSQL no revierte las secuencias con `RefreshDatabase`, por lo que los tests nunca usan IDs fijos: siempre consultan el modelo (`TipoComprobante::first()`, etc.).

---

## Tests implementados (32 tests)

### `SistemaTest`

| Test | Verifica |
|---|---|
| `test_login_page_loads` | GET /login → 200 |
| `test_public_registration_is_disabled` | GET/POST /register → 404 |
| `test_admin_can_access_dashboard` | GET /dashboard (admin) → 200 |
| `test_admin_can_list_products` | GET /productos → 200 |
| `test_admin_can_create_product` | POST /productos → redirect + registro en BD |
| `test_product_initial_stock_is_recorded_as_kardex_adjustment` | El stock inicial del alta entra al kardex como `ajuste_entrada` (motivo `stock_inicial`) |
| `test_editing_product_stock_records_adjustment_in_kardex` | Editar el stock registra `ajuste_salida` con saldos correctos; editar sin cambiar el stock no genera movimientos; el kardex muestra los ajustes |
| `test_admin_can_list_clients` | GET /clientes → 200 |
| `test_admin_can_create_client` | POST /clientes → redirect + registro en BD |
| `test_admin_can_list_suppliers` | GET /proveedores → 200 |
| `test_admin_can_show_and_edit_supplier` | GET /proveedores/{proveedor} y /edit → 200 |
| `test_full_purchase_flow` | Crear compra → confirmar → stock actualizado |
| `test_full_sale_flow` | Crear venta → confirmar → stock descontado |
| `test_insufficient_stock_blocks_sale` | Confirmar venta sin stock → error + venta sigue pendiente |
| `test_inventory_page` | GET /inventario → 200 |
| `test_kardex_page` | GET /inventario/kardex/{producto} → 200 |
| `test_reports_page` | GET /reportes → 200 |
| `test_user_can_download_sale_pdf` | GET /ventas/{venta}/pdf → application/pdf |
| `test_user_can_download_purchase_pdf` | GET /compras/{compra}/pdf → application/pdf |
| `test_excel_exports_are_real_xlsx_files` | Excel de stock, ventas y compras: XLSX válido con encabezados y datos |
| `test_sales_and_purchase_reports_are_restricted_by_role` | Reporte de compras: Admin/Deposito; reporte de ventas: Admin/Vendedor |
| `test_vendedor_can_access_sales` | Vendedor → GET /ventas y /ventas/create → 200 |
| `test_vendedor_cannot_access_purchases` | Vendedor → GET /compras → 403 |
| `test_deposito_can_access_purchases` | Deposito → GET /compras → 200 |
| `test_deposito_cannot_access_clients` | Deposito → GET /clientes/create → 403 |

### `DemoSeederTest`

Ejecuta `DatabaseSeeder` completo y verifica la consistencia de los datos demo.

| Test | Verifica |
|---|---|
| `test_demo_users_have_one_role_each` | Cada usuario demo tiene exactamente su rol y la contraseña `password` |
| `test_stock_matches_kardex_for_every_product` | `stock_actual` = suma de `stock_movements` y nunca negativo |
| `test_demo_data_covers_every_document_state` | Hay ventas y compras en todos los estados y productos bajo el mínimo |
| `test_cuits_have_valid_check_digit` | CUIT/CUIL de clientes y proveedores con dígito verificador válido |
| `test_seeder_is_idempotent` | Re-ejecutar el seeder no duplica registros |

Más `ExampleTest` (Feature y Unit) del skeleton de Laravel.

---

## Cómo ejecutar

```bash
# Docker
docker compose exec app php artisan test

# Local (requiere la base "testing" en PostgreSQL)
php artisan test

# Un test puntual
php artisan test --filter=test_full_sale_flow
```
