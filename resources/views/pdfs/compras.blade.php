<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Reporte de Compras</title>
<style>body{font-family:sans-serif;font-size:10pt}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:4px;text-align:left}th{background:#f0f0f0}h1{font-size:14pt;margin-bottom:5px}</style>
</head>
<body>
    <h1>Reporte de Compras</h1>
    <p>Período: {{ $fechaDesde ?? 'Inicio' }} - {{ $fechaHasta ?? 'Actual' }} | Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table><thead><tr><th>N° Orden</th><th>Proveedor</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th><th>Estado</th></tr></thead>
    <tbody>@foreach($compras as $c)<tr><td>{{ $c->numero_orden ?? '-' }}</td><td>{{ $c->proveedor?->razon_social ?? '-' }}</td><td>{{ $c->fecha_emision->format('d/m/Y') }}</td><td>${{ number_format($c->subtotal,2) }}</td><td>${{ number_format($c->impuesto,2) }}</td><td>${{ number_format($c->total,2) }}</td><td>{{ ucfirst($c->estado->value) }}</td></tr>@endforeach</tbody></table>
    @php $totalCompras = $compras->sum('total'); @endphp
    <p><strong>Total General: ${{ number_format($totalCompras, 2) }}</strong></p>
</body>
</html>
