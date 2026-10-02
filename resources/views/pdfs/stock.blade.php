<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Reporte de Stock</title>
<style>body{font-family:sans-serif;font-size:10pt}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:4px;text-align:left}th{background:#f0f0f0}h1{font-size:14pt;margin-bottom:5px}</style>
</head>
<body>
    <h1>Reporte de Stock</h1>
    <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table><thead><tr><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Precio Costo</th><th>Precio Venta</th></tr></thead>
    <tbody>@foreach($productos as $p)<tr><td>{{ $p->sku }}</td><td>{{ $p->nombre }}</td><td>{{ $p->categoria?->nombre ?? '-' }}</td><td>{{ $p->stock_actual }}</td><td>{{ $p->stock_minimo }}</td><td>${{ number_format($p->precio_costo,2) }}</td><td>${{ number_format($p->precio_venta,2) }}</td></tr>@endforeach</tbody></table>
</body>
</html>
