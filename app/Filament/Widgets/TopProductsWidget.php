<?php

namespace App\Filament\Widgets;

use App\Models\MovimientoInventario;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends ChartWidget
{
    protected static ?string $heading = 'Productos Más Activos (Último Mes)';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Obtener los productos con más movimientos en el último mes
        $topProducts = MovimientoInventario::with('producto')
            ->select('id_producto', DB::raw('SUM(cantidad) as total_movimientos'))
            ->where('created_at', '>=', now()->subMonth())
            ->groupBy('id_producto')
            ->orderBy('total_movimientos', 'desc')
            ->limit(8)
            ->get();

        $labels = $topProducts->map(function ($item) {
            return $item->producto->nombre ?? 'Desconocido';
        })->toArray();

        $data = $topProducts->pluck('total_movimientos')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Movimientos',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(99, 102, 241, 0.6)',
                        'rgba(79, 70, 229, 0.6)',
                        'rgba(139, 92, 246, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
                    ],
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
