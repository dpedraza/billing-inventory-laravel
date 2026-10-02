# Sistema de Facturación y Gestión de Inventario — Documentación técnica

Sistema web monolítico desarrollado con **Laravel 12**, **Blade + Tailwind CSS v4 + Alpine.js**, **PostgreSQL 17**, **Spatie Permission**, **DomPDF** y **OpenSpout**.

Para instalación y uso ver el [README en español](README.es.md) o el [README en inglés](../README.md).

---

## Documentación

| Archivo | Contenido |
|---|---|
| [01-modelo-datos.md](01-modelo-datos.md) | Diagrama entidad-relación, migraciones, tipos de datos, enums |
| [02-estructura-proyecto.md](02-estructura-proyecto.md) | Arquitectura, Models, Services (lógica de negocio), Policies (permisos), FormRequests |
| [03-endpoints.md](03-endpoints.md) | Rutas web, endpoints por módulo, tabla resumen |
| [04-vistas.md](04-vistas.md) | Estructura de vistas Blade, descripción de pantallas principales |
| [05-seeders.md](05-seeders.md) | Datos demo: usuarios, catálogos, productos, clientes, proveedores y operaciones |
| [06-pruebas.md](06-pruebas.md) | Tests automatizados (PHPUnit), instrucciones de ejecución |

---

## Stack tecnológico

| Componente | Versión / Detalle |
|---|---|
| Laravel | 12 |
| PHP | 8.3+ (imagen Docker: PHP 8.5) |
| Base de datos | PostgreSQL 17 (servicio `db` en Docker, puerto expuesto `5433`) |
| Frontend | Tailwind CSS v4 + Alpine.js 3 (bundled con Vite) |
| Autenticación | Laravel Auth manual (sin Breeze/Jetstream), sin registro público |
| Roles | Spatie Laravel Permission v6 |
| PDF | barryvdh/laravel-dompdf |
| Excel | OpenSpout (XLSX) |

## Configuración del entorno (Docker)

`compose.yaml` levanta dos servicios: `app` (PHP 8.5 CLI) y `db` (PostgreSQL 17). Se usa el mismo `.env.example` que en local: compose sobreescribe `DB_HOST`/`DB_PORT` para apuntar al servicio interno.

```
# App web
http://localhost:8080        # APP_PORT

# Acceso a PostgreSQL desde el host (por ejemplo, pgAdmin)
Host: 127.0.0.1 | Port: 5433 # FORWARD_DB_PORT
DB_DATABASE=sistema_facturacion
DB_USERNAME=facturacion
DB_PASSWORD=secret
```

## Comandos útiles (Docker)

```bash
docker compose up -d                          # levantar servicios
docker compose logs -f app                    # ver logs
docker compose exec app php artisan migrate   # aplicar migraciones nuevas
docker compose exec app php artisan db:seed   # datos demo (idempotente)
docker compose exec app php artisan test      # ejecutar tests
```
