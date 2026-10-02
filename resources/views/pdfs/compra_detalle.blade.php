<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Compra N° {{ $compra->numero_orden }}</title>
    <style>
        @page {
            margin: 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f4c81;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .brand-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            text-align: right;
        }
        .doc-type {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .doc-number {
            font-size: 16px;
            font-weight: bold;
            font-family: monospace;
            color: #0f4c81;
            margin-top: 4px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-card {
            width: 48%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            vertical-align: top;
        }
        .card-title {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            border-b: 1px solid #f1f5f9;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .info-line {
            margin-bottom: 4px;
        }
        .info-label {
            color: #64748b;
            font-size: 10px;
        }
        .info-value {
            font-weight: bold;
            color: #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-completada { background: #dcfce7; color: #166534; }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-anulada { background: #fee2e2; color: #991b1b; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #0f4c81;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px;
            text-align: left;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .totals-table td {
            padding: 6px 8px;
            font-size: 10px;
        }
        .totals-label {
            color: #64748b;
        }
        .totals-value {
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            background-color: #f1f5f9;
            border-top: 2px solid #0f4c81;
            font-size: 12px !important;
        }
        .total-row td {
            color: #0f4c81;
            font-weight: bold;
        }
        .clear { clear: both; }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="brand-title">Sistema Facturación & Inventario</div>
                <div class="brand-subtitle">PYME ERP — Orden de Compra</div>
                <div style="margin-top: 8px; color: #475569;">
                    <strong>Emisión:</strong> {{ $compra->fecha_emision?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                </div>
            </td>
            <td width="40%">
                <div class="doc-box">
                    <div class="doc-type">ORDEN DE COMPRA</div>
                    <div class="doc-number">{{ $compra->numero_orden }}</div>
                    <div style="margin-top: 6px;">
                        @php $st = $compra->estado->value ?? (string)$compra->estado; @endphp
                        <span class="badge badge-{{ $st }}">{{ strtoupper($st) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Info Grid -->
    <table class="info-grid">
        <tr>
            <td class="info-card">
                <div class="card-title">Datos del Proveedor</div>
                <div class="info-line">
                    <span class="info-label">Razón Social:</span> 
                    <span class="info-value">{{ $compra->proveedor?->razon_social ?? '—' }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">CUIT / DNI:</span> 
                    <span class="info-value">{{ $compra->proveedor?->cuit_dni ?? '—' }}</span>
                </div>
                @if($compra->proveedor?->telefono)
                    <div class="info-line">
                        <span class="info-label">Teléfono:</span> 
                        <span class="info-value">{{ $compra->proveedor->telefono }}</span>
                    </div>
                @endif
            </td>
            <td width="4%"></td>
            <td class="info-card">
                <div class="card-title">Detalles de la Orden</div>
                <div class="info-line">
                    <span class="info-label">N° Operación:</span> 
                    <span class="info-value">#{{ $compra->id }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">Fecha de Registro:</span> 
                    <span class="info-value">{{ $compra->fecha_emision?->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div class="info-line">
                    <span class="info-label">Registrado por:</span> 
                    <span class="info-value">{{ $compra->creador?->name ?? 'Sistema' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="15%">SKU</th>
                <th width="45%">Producto</th>
                <th width="12%" class="text-right">Cantidad</th>
                <th width="14%" class="text-right">Costo Unit.</th>
                <th width="14%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compra->items as $item)
                <tr>
                    <td style="font-family: monospace; color: #64748b;">{{ $item->producto?->sku ?? '—' }}</td>
                    <td><strong>{{ $item->producto?->nombre ?? 'Producto no encontrado' }}</strong></td>
                    <td class="text-right">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                    <td class="text-right">$ {{ number_format($item->costo_unitario, 2, ',', '.') }}</td>
                    <td class="text-right">$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 20px;">Sin ítems registrados en esta orden.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="totals-table">
        <tr>
            <td class="totals-label">Subtotal:</td>
            <td class="totals-value">$ {{ number_format($compra->subtotal, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="totals-label">IVA (21%):</td>
            <td class="totals-value">$ {{ number_format($compra->impuesto, 2, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td class="totals-label" style="color: #0f4c81;">TOTAL:</td>
            <td class="totals-value" style="color: #0f4c81;">$ {{ number_format($compra->total, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="clear"></div>

    @if($compra->notas)
        <div style="margin-top: 15px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 8px;">
            <strong style="color: #475569; font-size: 10px;">Notas / Observaciones:</strong>
            <p style="margin: 4px 0 0 0; color: #334155; font-size: 10px;">{{ $compra->notas }}</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Orden de compra generada electrónicamente por Sistema Facturación — {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
