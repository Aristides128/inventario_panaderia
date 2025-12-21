+<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Envío #{{ $envio->id_envio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }
        
        .header h1 {
            color: #1e40af;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #64748b;
            font-size: 11px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-section h2 {
            background-color: #2563eb;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px;
            background-color: #f1f5f9;
            width: 35%;
            border: 1px solid #e2e8f0;
        }
        
        .info-value {
            display: table-cell;
            padding: 8px;
            border: 1px solid #e2e8f0;
            background-color: white;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .products-table thead {
            background-color: #1e40af;
            color: white;
        }
        
        .products-table th {
            padding: 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
        }
        
        .products-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .products-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .products-table tbody tr:hover {
            background-color: #e0f2fe;
        }
        
        .total-row {
            background-color: #dbeafe !important;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }
        
        .observaciones-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin-top: 10px;
            border-radius: 4px;
        }
        
        .observaciones-box strong {
            color: #92400e;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #10b981;
            color: white;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE ENVÍO</h1>
        <p>Documento generado el {{ $fecha }}</p>
        <p><span class="badge">ID ENVÍO: #{{ $envio->id_envio }}</span></p>
    </div>

    <!-- Información de Sucursales -->
    <div class="info-section">
        <h2>🏢 Información de Sucursales</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Sucursal Origen:</div>
                <div class="info-value">{{ $sucursalOrigen->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sucursal Destino:</div>
                <div class="info-value">{{ $sucursalDestino->nombre ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Información del Envío -->
    <div class="info-section">
        <h2>Información del Envío</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Fecha de Envío:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($envio->fecha_envio)->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="info-section">
        <h2>Detalle de Productos</h2>
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 60%;">Producto</th>
                    <th style="width: 30%; text-align: center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCantidad = 0;
                @endphp
                @foreach($detalles as $index => $detalle)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $detalle->producto->nombre ?? 'Producto desconocido' }}</td>
                    <td style="text-align: center;">{{ $detalle->cantidad }}</td>
                </tr>
                @php
                    $totalCantidad += $detalle->cantidad;
                @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-right: 10px;">
                        <strong>TOTAL DE PRODUCTOS:</strong>
                    </td>
                    <td style="text-align: center;">
                        <strong>{{ $totalCantidad }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

       
    </div>
    @if($envio->observaciones)
        <div class="observaciones-box">
            <strong>Observaciones:</strong><br>
            {{ $envio->observaciones }}
        </div>
    @endif

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>© {{ date('Y') }} - Panadería Francesa - Todos los derechos reservados</p>
    </div>
</body>
</html>
