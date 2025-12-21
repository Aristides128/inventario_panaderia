<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
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
        <h1>REPORTE DE PRODUCCIÓN</h1>
        <p>Documento generado el {{ $fechaGeneracion }}</p>
        <p><span class="badge">ID PRODUCCIÓN: #{{ $produccion->id_produccion }}</span></p>
    </div>

    <!-- Información de la Producción -->
    <div class="info-section">
        <h2>📋 Información General</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Fecha de Producción:</div>
                <div class="info-value">{{ date('d/m/Y', strtotime($produccion->fecha_produccion)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estado:</div>
                <div class="info-value">COMPLETADO</div>
            </div>
        </div>
    </div>

    <!-- Detalle de Productos -->
    <div class="info-section">
        <h2>📦 Productos Producidos / Entregados</h2>
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Producto</th>
                    <th style="width: 20%; text-align: center;">Cantidad</th>
                    <th style="width: 20%;">Recibido por</th>
                    <th style="width: 20%; text-align: center;">Fecha/Hora</th>
                </tr>
            </thead>
            <tbody>
                @php $totalCantidad = 0; @endphp
                @foreach($detalles as $detalle)
                <tr>
                    <td>{{ $detalle->Producto->nombre ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $detalle->cantidad_utilizada }}</td>
                    <td>{{ $detalle->Empleado->nombre ?? 'Sin asignar' }}</td>
                    <td style="text-align: center;">{{ $detalle->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @php $totalCantidad += $detalle->cantidad_utilizada; @endphp
                @endforeach
                <tr class="total-row">
                    <td style="text-align: right; padding-right: 10px;">
                        <strong>TOTAL DE UNIDADES:</strong>
                    </td>
                    <td style="text-align: center;">
                        <strong>{{ $totalCantidad }}</strong>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($produccion->observaciones)
        <div class="observaciones-box">
            <strong>Observaciones:</strong><br>
            {{ $produccion->observaciones }}
        </div>
    @endif

    <div style="margin-top: 50px; display: table; width: 100%;">
        <div style="display: table-row;">
            <div style="display: table-cell; width: 50%; text-align: center; padding-top: 40px;">
                <div style="border-top: 1px solid #333; width: 150px; margin: 0 auto;"></div>
                <p style="margin-top: 10px; font-size: 11px;">Firma Responsable</p>
            </div>
            <div style="display: table-cell; width: 50%; text-align: center; padding-top: 40px;">
                <div style="border-top: 1px solid #333; width: 150px; margin: 0 auto;"></div>
                <p style="margin-top: 10px; font-size: 11px;">Firma Recibido</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>© {{ date('Y') }} - Panadería Francesa - Todos los derechos reservados</p>
    </div>
</body>
</html>
