# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project follows [Semantic Versioning](https://semver.org/).

## [1.1.0] — 2026-10-03

### Added
- Demo seeder guarantees activity in the current month (at least two sales per day, Sundays included, and a restock purchase to every supplier on day 1), so the dashboard period KPIs and rankings are never empty.
- Application screenshots in the README (`docs/screenshots/`).

### Fixed
- Sales form: picking a product now prefills its sale price and enables the insufficient-stock warning (the handler was reading the customer/document-type selects instead of the product row).
- Removed a hardcoded version ("v2.4") from the sidebar.

## [1.0.0] — 2026-10-02 — Initial public release

### Added
- Products, customers and suppliers management (CRUD, soft deletes, unique SKU and CUIT/DNI, VAT condition).
- Purchases and sales with dynamic item rows, 21 % VAT and a pending → confirmed → voided lifecycle (no edit/update).
- Sequential document numbering per document type (`FC-A`, `FC-B`, `REM`, `PRES`).
- Transactional stock control in `StockService` (`DB::transaction` + `lockForUpdate()`), configurable negative-stock guard and stock kardex (`stock_movements`) with JSONB metadata.
- Stock set on the product form is recorded in the kardex as an adjustment (`ajuste_entrada` / `ajuste_salida`).
- Role-specific dashboards for Admin, Vendedor and Deposito.
- PDF reports (DomPDF): stock, sales and purchases by period, individual sales document and purchase order.
- Excel reports (OpenSpout, streamed): stock, sales and purchases by period.
- Role-based access control with Spatie Permission v6 and Laravel Policies, including per-role report export permissions.
- Demo data seeders: one user per role and ~5 months of purchases and sales generated through the business services.
- One-command Docker setup (PHP 8.5 + PostgreSQL 17).
- Feature test suite on PostgreSQL and GitHub Actions CI.
