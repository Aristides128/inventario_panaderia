<?php

namespace App\Filament\Widgets;

use App\Models\Productos;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockAlertWidget extends BaseWidget
{
    protected static ?int $sort = 0;
    
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Productos con stock bajo (menos de 10 unidades)
        $lowStockProducts = Productos::where('stock_actual', '<', 10)
            ->where('stock_actual', '>', 0)
            ->count();

        // Productos sin stock
        $outOfStockProducts = Productos::where('stock_actual', '<=', 0)->count();

        // Total de productos
        $totalProducts = Productos::count();

        // Productos con stock saludable (10 o más unidades)
        $healthyStockProducts = Productos::where('stock_actual', '>=', 10)->count();

        // Stock total en el inventario
        $totalStock = Productos::sum('stock_actual');

        return [
            Stat::make('Productos con Stock Bajo', $lowStockProducts)
                ->description('Menos de 10 unidades')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->chart([7, 5, 8, 6, 9, 7, $lowStockProducts]),
            
            Stat::make('Productos Sin Stock', $outOfStockProducts)
                ->description('Requieren reabastecimiento urgente')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart([3, 2, 4, 3, 2, 1, $outOfStockProducts]),
            
            Stat::make('Productos con Stock Saludable', $healthyStockProducts)
                ->description('10 o más unidades')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([15, 18, 20, 22, 19, 21, $healthyStockProducts]),
            
            Stat::make('Total de Productos', $totalProducts)
                ->description('En el inventario')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart([25, 28, 30, 32, 30, 29, $totalProducts]),
            
            Stat::make('Stock Total', number_format($totalStock, 0))
                ->description('Unidades en inventario')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary')
                ->chart([1000, 1200, 1100, 1300, 1250, 1150, $totalStock]),
        ];
    }
}
