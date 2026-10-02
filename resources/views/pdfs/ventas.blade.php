<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Reporte de Ventas</title>
<style>body{font-family:sans-serif;font-size:10pt}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:4px;text-align:left}th{background:#f0f0f0}h1{font-size:14pt;margin-bottom:5px}</style>
</head>
<body>
    <h1>Reporte de Ventas</h1>
    <p>Período: {{ $fechaDesde ?? 'Inicio' }} - {{ $fechaHasta ?? 'Actual' }} | Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table><thead><tr><th>N° Comprobante</th><th>Cliente</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th><th>Estado</th></tr></thead>
    <tbody>@foreach($ventas as $v)<tr><td>{{ $v->numero_comprobante ?? '-' }}</td><td>{{ $v->cliente?->razon_social ?? 'Consumidor Final' }}</td><td>{{ $v->fecha_emision->format('d/m/Y') }}</td><td>${{ number_format($v->subtotal,2) }}</td><td>${{ number_format($v->impuesto,2) }}</td><td>${{ number_format($v->total,2) }}</td><td>{{ ucfirst($v->estado->value) }}</td></tr>@endforeach</tbody></table>
    @php $totalVentas = $ventas->sum('total'); @endphp
    <p><strong>Total General: ${{ number_format($totalVentas, 2) }}</strong></p>
</body>
</html>
