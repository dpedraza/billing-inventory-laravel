# Endpoints y Rutas

Todas las rutas están definidas en `routes/web.php`.  
El sistema es **monolítico con Blade** — no hay API REST.

---

## Rutas públicas (guest)

| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/` | — | `fn() => redirect('/dashboard')` |
| GET | `/login` | `login` | `AuthenticatedSessionController@create` |
| POST | `/login` | — | `AuthenticatedSessionController@store` |

---

## Rutas autenticadas (middleware `auth`)

### Dashboard
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/dashboard` | `dashboard` | `DashboardController` (__invoke) |

### Productos (CRUD completo)
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/productos` | `productos.index` | `ProductoController@index` |
| GET | `/productos/create` | `productos.create` | `ProductoController@create` |
| POST | `/productos` | `productos.store` | `ProductoController@store` |
| GET | `/productos/{producto}` | `productos.show` | `ProductoController@show` |
| GET | `/productos/{producto}/edit` | `productos.edit` | `ProductoController@edit` |
| PUT/PATCH | `/productos/{producto}` | `productos.update` | `ProductoController@update` |
| DELETE | `/productos/{producto}` | `productos.destroy` | `ProductoController@destroy` |

### Clientes (CRUD completo)
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/clientes` | `clientes.index` | `ClienteController@index` |
| GET | `/clientes/create` | `clientes.create` | `ClienteController@create` |
| POST | `/clientes` | `clientes.store` | `ClienteController@store` |
| GET | `/clientes/{cliente}` | `clientes.show` | `ClienteController@show` |
| GET | `/clientes/{cliente}/edit` | `clientes.edit` | `ClienteController@edit` |
| PUT/PATCH | `/clientes/{cliente}` | `clientes.update` | `ClienteController@update` |
| DELETE | `/clientes/{cliente}` | `clientes.destroy` | `ClienteController@destroy` |

### Proveedores (CRUD completo)
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/proveedores` | `proveedores.index` | `ProveedorController@index` |
| GET | `/proveedores/create` | `proveedores.create` | `ProveedorController@create` |
| POST | `/proveedores` | `proveedores.store` | `ProveedorController@store` |
| GET | `/proveedores/{proveedor}` | `proveedores.show` | `ProveedorController@show` |
| GET | `/proveedores/{proveedor}/edit` | `proveedores.edit` | `ProveedorController@edit` |
| PUT/PATCH | `/proveedores/{proveedor}` | `proveedores.update` | `ProveedorController@update` |
| DELETE | `/proveedores/{proveedor}` | `proveedores.destroy` | `ProveedorController@destroy` |

### Compras (sin edit/update)
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/compras` | `compras.index` | `CompraController@index` |
| GET | `/compras/create` | `compras.create` | `CompraController@create` |
| POST | `/compras` | `compras.store` | `CompraController@store` |
| GET | `/compras/{compra}` | `compras.show` | `CompraController@show` |
| GET | `/compras/{compra}/pdf` | `compras.pdf` | `CompraController@pdf` |
| POST | `/compras/{compra}/confirmar` | `compras.confirmar` | `CompraController@confirmar` |
| POST | `/compras/{compra}/anular` | `compras.anular` | `CompraController@anular` |

### Ventas (sin edit/update)
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/ventas` | `ventas.index` | `VentaController@index` |
| GET | `/ventas/create` | `ventas.create` | `VentaController@create` |
| POST | `/ventas` | `ventas.store` | `VentaController@store` |
| GET | `/ventas/{venta}` | `ventas.show` | `VentaController@show` |
| GET | `/ventas/{venta}/pdf` | `ventas.pdf` | `VentaController@pdf` |
| POST | `/ventas/{venta}/confirmar` | `ventas.confirmar` | `VentaController@confirmar` |
| POST | `/ventas/{venta}/anular` | `ventas.anular` | `VentaController@anular` |

### Inventario
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/inventario` | `inventario.index` | `InventarioController@index` |
| GET | `/inventario/kardex/{producto}` | `inventario.kardex` | `InventarioController@kardex` |
| GET | `/inventario/stock-bajo` | `inventario.stock-bajo` | `InventarioController@stockBajo` |

### Reportes
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/reportes` | `reportes.index` | `ReporteController@index` |
| GET | `/reportes/stock/{formato}` | `reportes.stock` | `ReporteController@exportStock` |
| GET | `/reportes/ventas/{formato}` | `reportes.ventas` | `ReporteController@exportVentas` |
| GET | `/reportes/compras/{formato}` | `reportes.compras` | `ReporteController@exportCompras` |

Donde `{formato}` es `pdf` o `excel` (otro valor → 404). El reporte de ventas requiere la habilidad `exportar` de `VentaPolicy` (Admin, Vendedor) y el de compras la de `CompraPolicy` (Admin, Deposito).

No hay registro público: los usuarios se crean con los seeders.

### Perfil
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| GET | `/perfil` | `perfil.edit` | `PerfilController@edit` |
| PUT | `/perfil` | `perfil.update` | `PerfilController@update` |

### Logout
| Método | URI | Nombre | Controlador |
|---|---|---|---|
| POST | `/logout` | `logout` | `AuthenticatedSessionController@destroy` |

---

## Resumen por Módulo

| Módulo | Total rutas | CRUD | Acciones extra |
|---|---|---|---|
| Auth | 3 | — | login, logout |
| Dashboard | 1 | — | métricas |
| Productos | 7 | Completo | — |
| Clientes | 7 | Completo | — |
| Proveedores | 7 | Completo | — |
| Compras | 7 | Listar, Crear, Ver | pdf, confirmar, anular |
| Ventas | 7 | Listar, Crear, Ver | pdf, confirmar, anular |
| Inventario | 3 | — | kardex, stock-bajo |
| Reportes | 4 | — | export PDF/Excel |
| Perfil | 2 | — | editar perfil |

**Total: 49 rutas** (incluye `/` → redirect a `/dashboard` y las rutas de guest; verificado con `php artisan route:list --except-vendor`)
