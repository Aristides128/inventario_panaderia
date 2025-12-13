<?php

namespace App\Filament\Widgets;

use App\Models\Productos;
use Filament\Widgets\ChartWidget;

class StockStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Niveles de Stock Actual';
    
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Obtener los 10 productos con más stock
        $productos = Productos::with('categoria')
            ->orderBy('stock_actual', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Stock Actual',
                    'data' => $productos->pluck('stock_actual')->toArray(),
                    'backgroundColor' => [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(67, 56, 202, 0.8)',
                        'rgba(55, 48, 163, 0.8)',
                        'rgba(99, 102, 241, 0.6)',
                        'rgba(79, 70, 229, 0.6)',
                        'rgba(67, 56, 202, 0.6)',
                        'rgba(55, 48, 163, 0.6)',
                        'rgba(99, 102, 241, 0.4)',
                        'rgba(79, 70, 229, 0.4)',
                    ],
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $productos->pluck('nombre')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
