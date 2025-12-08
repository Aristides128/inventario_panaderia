<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Compra #{{ $compra->id_compra }}</title>
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
            border-bottom: 3px solid #10b981;
        }
        
        .header h1 {
            color: #059669;
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
            background-color: #10b981;
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
            background-color: #059669;
            color: white;
        }
        
        .products-table th {
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        .products-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        
        .products-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .products-table tbody tr:hover {
            background-color: #d1fae5;
        }
        
        .total-row {
            background-color: #d1fae5 !important;
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
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-pendiente {
            background-color: #fbbf24;
            color: #78350f;
        }
        
        .badge-recibido {
            background-color: #10b981;
            color: white;
        }
        
        .badge-cancelado {
            background-color: #ef4444;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE COMPRA PARIS CAKE</h1>
        <p>Documento generado el {{ $fecha }}</p>
        <p>
            <span class="badge 
                @if($compra->estado_compra == 'pendiente') badge-pendiente
                @elseif($compra->estado_compra == 'Recibido') badge-recibido
                @else badge-cancelado
                @endif">
                ID COMPRA: #{{ $compra->id_compra }} - 
                @if($compra->estado_compra == 'pendiente') PENDIENTE
                @elseif($compra->estado_compra == 'Recibido') RECIBIDO
                @else CANCELADO
                @endif
            </span>
        </p>
    </div>

    <!-- Información General -->
    <div class="info-section">
        <h2> INFORMACIÓN GENERAL</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Sucursal:</div>
                <div class="info-value">{{ $sucursal->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Compra:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estado:</div>
                <div class="info-value">
                    @if($compra->estado_compra == 'pendiente') Pendiente a recibir
                    @elseif($compra->estado_compra == 'Recibido') Recibido
                    @else Cancelado
                    @endif
                </div>
            </div>
        </div>
        
      
    </div>

    <!-- Detalle de Productos -->
    <div class="info-section">
        <h2>Detalle de Productos</h2>
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Producto</th>
                    <th style="width: 25%;">Proveedor</th>
                    <th style="width: 10%; text-align: center;">Cantidad</th>
                    <th style="width: 15%; text-align: center;">Precio Unit.</th>
                    <th style="width: 15%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGeneral = 0;
                @endphp
                @foreach($detalles as $index => $detalle)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $detalle->producto->nombre ?? 'Producto desconocido' }}</td>
                    <td>{{ $detalle->proveedor->nombre ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $detalle->cantidad_producto }}</td>
                    <td style="text-align: center;">$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td style="text-align: right;">$ {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @php
                    $totalGeneral += $detalle->subtotal;
                @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align: right; padding-right: 10px;">
                        <strong>TOTAL GENERAL:</strong>
                    </td>
                    <td style="text-align: right;">
                        <strong>$ {{ number_format($totalGeneral, 2) }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

      @if($compra->observaciones)
        <div class="observaciones-box">
            <strong>Observaciones:</strong><br>
            {{ $compra->observaciones }}
        </div>
        @endif

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>© {{ date('Y') }} - Panadería Francesa - Todos los derechos reservados</p>
    </div>
</body>
</html>
