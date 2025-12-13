<?php

namespace App\Filament\Widgets;

use App\Models\MovimientoInventario;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InventoryMovementsWidget extends ChartWidget
{
    protected static ?string $heading = 'Movimientos de Inventario (Últimos 7 Días)';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect();
        $entradas = collect();
        $salidas = collect();

        // Generar los últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->locale('es')->isoFormat('DD MMM');
            
            $days->push($dayName);

            // Contar entradas (compras, producción, ajustes positivos)
            $entradasCount = MovimientoInventario::whereDate('created_at', $date)
                ->whereIn('tipo_movimiento', ['compra', 'produccion', 'ajuste_positivo'])
                ->sum('cantidad');
            
            $entradas->push($entradasCount);

            // Contar salidas (ventas, envíos, ajustes negativos)
            $salidasCount = MovimientoInventario::whereDate('created_at', $date)
                ->whereIn('tipo_movimiento', ['venta', 'envio', 'ajuste_negativo'])
                ->sum('cantidad');
            
            $salidas->push($salidasCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $entradas->toArray(),
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Salidas',
                    'data' => $salidas->toArray(),
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
