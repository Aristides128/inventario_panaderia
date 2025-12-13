<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Movimientos - Semana {{ $semana }}/{{ $anio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #6366f1;
        }
        
        .header h1 {
            color: #4f46e5;
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #64748b;
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .header p {
            color: #64748b;
            font-size: 10px;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
        }
        
        .summary-card.entradas {
            border-color: #10b981;
            background-color: #d1fae5;
        }
        
        .summary-card.salidas {
            border-color: #ef4444;
            background-color: #fee2e2;
        }
        
        .summary-card.total {
            border-color: #6366f1;
            background-color: #e0e7ff;
        }
        
        .summary-card .label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .summary-card.entradas .value {
            color: #059669;
        }
        
        .summary-card.salidas .value {
            color: #dc2626;
        }
        
        .summary-card.total .value {
            color: #4f46e5;
        }
        
        .info-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .info-section h2 {
            background-color: #6366f1;
            color: white;
            padding: 8px 12px;
            font-size: 13px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        
        .movements-table thead {
            background-color: #4f46e5;
            color: white;
        }
        
        .movements-table th {
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        
        .movements-table td {
            padding: 6px 5px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        
        .movements-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-entrada {
            background-color: #10b981;
            color: white;
        }
        
        .badge-salida {
            background-color: #ef4444;
            color: white;
        }
        
        .badge-compra {
            background-color: #3b82f6;
            color: white;
        }
        
        .badge-produccion {
            background-color: #f59e0b;
            color: white;
        }
        
        .badge-envio {
            background-color: #06b6d4;
            color: white;
        }
        
        .badge-ajuste {
            background-color: #6b7280;
            color: white;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        
        .summary-table thead {
            background-color: #059669;
            color: white;
        }
        
        .summary-table th {
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .summary-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
        }
        
        .summary-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-success {
            color: #059669;
            font-weight: bold;
        }
        
        .text-danger {
            color: #dc2626;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 9px;
        }
        
        .top-products {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
        }
        
        .top-products h3 {
            color: #92400e;
            font-size: 11px;
            margin-bottom: 8px;
        }
        
        .top-products ol {
            margin-left: 20px;
            font-size: 10px;
        }
        
        .top-products li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 REPORTE DE MOVIMIENTOS DE INVENTARIO</h1>
        <p class="subtitle">Semana {{ $semana }} - {{ \Carbon\Carbon::create($anio, $mes, 1)->locale('es')->monthName }} {{ $anio }}</p>
        <p>Período: {{ $fechaInicio }} al {{ $fechaFin }}</p>
        <p>Documento generado el {{ $fechaGeneracion }}</p>
    </div>

    <!-- Resumen General -->
    <div class="summary-cards">
        <div class="summary-card entradas">
            <div class="label">⬇️ Total Entradas</div>
            <div class="value">{{ number_format($totalEntradas) }}</div>
            <div class="label">unidades</div>
        </div>
        <div class="summary-card salidas">
            <div class="label">⬆️ Total Salidas</div>
            <div class="value">{{ number_format($totalSalidas) }}</div>
            <div class="label">unidades</div>
        </div>
        <div class="summary-card total">
            <div class="label">📋 Total Movimientos</div>
            <div class="value">{{ $totalMovimientos }}</div>
            <div class="label">registros</div>
        </div>
    </div>

    <!-- Productos Más Movidos -->
    @if($productosMasMovidos->count() > 0)
    <div class="top-products">
        <h3>🏆 Top 5 Productos Más Movidos</h3>
        <ol>
            @foreach($productosMasMovidos as $prod)
            <li><strong>{{ $prod['producto'] }}</strong> - {{ $prod['total_movimientos'] }} movimientos ({{ number_format($prod['cantidad_total']) }} unidades)</li>
            @endforeach
        </ol>
    </div>
    @endif

    <!-- Resumen por Producto -->
    <div class="info-section">
        <h2>📦 Resumen por Producto</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Producto</th>
                    <th style="width: 15%; text-align: center;">Entradas</th>
                    <th style="width: 15%; text-align: center;">Salidas</th>
                    <th style="width: 15%; text-align: center;">Neto</th>
                    <th style="width: 15%; text-align: center;">Stock Actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumenProductos as $resumen)
                <tr>
                    <td>{{ $resumen['producto'] }}</td>
                    <td class="text-center text-success">+{{ number_format($resumen['entradas']) }}</td>
                    <td class="text-center text-danger">-{{ number_format($resumen['salidas']) }}</td>
                    <td class="text-center" style="font-weight: bold; color: {{ $resumen['neto'] >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $resumen['neto'] >= 0 ? '+' : '' }}{{ number_format($resumen['neto']) }}
                    </td>
                    <td class="text-center">{{ number_format($resumen['stock_actual']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Movimientos por Tipo de Referencia -->
    <div class="info-section">
        <h2>🔗 Movimientos por Tipo de Operación</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Tipo de Operación</th>
                    <th style="width: 20%; text-align: center;">Cantidad de Movimientos</th>
                    <th style="width: 20%; text-align: center;">Total Unidades</th>
                    <th style="width: 20%; text-align: center;">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porTipo as $tipo => $movs)
                <tr>
                    <td>
                        @if($tipo == 'COMPRA') 🛒 Compras
                        @elseif($tipo == 'PRODUCCION') 🏭 Producciones
                        @elseif($tipo == 'ENVIO') 🚚 Envíos
                        @elseif($tipo == 'AJUSTE') 🔧 Ajustes
                        @else {{ $tipo }}
                        @endif
                    </td>
                    <td class="text-center">{{ $movs->count() }}</td>
                    <td class="text-center">{{ number_format($movs->sum('cantidad')) }}</td>
                    <td class="text-center">{{ number_format($movs->avg('cantidad'), 1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Detalle de Todos los Movimientos -->
    <div class="info-section">
        <h2>📋 Detalle Completo de Movimientos</h2>
        <table class="movements-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Fecha</th>
                    <th style="width: 8%;">Hora</th>
                    <th style="width: 8%;">Tipo</th>
                    <th style="width: 25%;">Producto</th>
                    <th style="width: 8%;">Cantidad</th>
                    <th style="width: 8%;">Stock Ant.</th>
                    <th style="width: 8%;">Stock Nvo.</th>
                    <th style="width: 12%;">Referencia</th>
                    <th style="width: 15%;">Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $mov)
                <tr>
                    <td>{{ $mov->created_at->format('d/m/Y') }}</td>
                    <td>{{ $mov->created_at->format('H:i') }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($mov->tipo_movimiento) }}">
                            {{ $mov->tipo_movimiento == 'ENTRADA' ? '⬇️' : '⬆️' }} {{ $mov->tipo_movimiento }}
                        </span>
                    </td>
                    <td>{{ $mov->producto->nombre ?? 'N/A' }}</td>
                    <td class="text-center" style="font-weight: bold; color: {{ $mov->tipo_movimiento == 'ENTRADA' ? '#059669' : '#dc2626' }};">
                        {{ $mov->tipo_movimiento == 'ENTRADA' ? '+' : '-' }}{{ $mov->cantidad }}
                    </td>
                    <td class="text-center">{{ $mov->cantidad_anterior }}</td>
                    <td class="text-center">{{ $mov->cantidad_nueva }}</td>
                    <td>
                        @if($mov->referencia_tipo)
                        <span class="badge badge-{{ strtolower($mov->referencia_tipo) }}">
                            {{ $mov->referencia_tipo }} #{{ $mov->referencia_id }}
                        </span>
                        @else
                        N/A
                        @endif
                    </td>
                    <td>{{ $mov->usuario->name ?? 'Sistema' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Inventario</p>
        <p>© {{ date('Y') }} - Panadería Francesa - Todos los derechos reservados</p>
        <p>Total de registros en este reporte: {{ $totalMovimientos }}</p>
    </div>
</body>
</html>
