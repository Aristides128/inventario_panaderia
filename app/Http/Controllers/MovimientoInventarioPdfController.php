<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\lotes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MovimientoInventarioPdfController extends Controller
{
    /**
     * Generar PDF de movimientos de inventario por semana
     */
    public function generarPdfSemanal(Request $request)
    {
        // Obtener parámetros de la URL o usar valores por defecto
        $semana = $request->input('semana', now()->weekOfYear);
        $anio = $request->input('anio', now()->year);
        
        // Buscar el lote correspondiente
        $lote = lotes::where('semana', $semana)
            ->where('anio', $anio)
            ->first();
        
        if (!$lote) {
            abort(404, 'No se encontró un lote para la semana ' . $semana . ' del año ' . $anio);
        }
        
        // Calcular fechas de inicio y fin de la semana
        $fechaInicio = Carbon::now()->setISODate($anio, $semana)->startOfWeek();
        $fechaFin = Carbon::now()->setISODate($anio, $semana)->endOfWeek();
        
        // Obtener todos los movimientos de esa semana
        $movimientos = MovimientoInventario::with(['producto.categoria', 'lote', 'usuario'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calcular estadísticas
        $totalEntradas = $movimientos->where('tipo_movimiento', 'ENTRADA')->sum('cantidad');
        $totalSalidas = $movimientos->where('tipo_movimiento', 'SALIDA')->sum('cantidad');
        
        // Agrupar por tipo de referencia
        $porTipo = $movimientos->groupBy('referencia_tipo');
        
        // Resumen por producto
        $resumenProductos = $movimientos->groupBy('id_producto')->map(function ($movs) {
            $producto = $movs->first()->producto;
            $entradas = $movs->where('tipo_movimiento', 'ENTRADA')->sum('cantidad');
            $salidas = $movs->where('tipo_movimiento', 'SALIDA')->sum('cantidad');
            
            return [
                'producto' => $producto->nombre,
                'entradas' => $entradas,
                'salidas' => $salidas,
                'neto' => $entradas - $salidas,
                'stock_actual' => $producto->stock_actual,
            ];
        })->sortByDesc('neto');
        
        // Productos más movidos (top 5)
        $productosMasMovidos = $movimientos->groupBy('id_producto')->map(function ($movs) {
            return [
                'producto' => $movs->first()->producto->nombre,
                'total_movimientos' => $movs->count(),
                'cantidad_total' => $movs->sum('cantidad'),
            ];
        })->sortByDesc('total_movimientos')->take(5);
        
        // Preparar datos para el PDF
        $data = [
            'semana' => $semana,
            'mes' => $lote->mes ?? Carbon::now()->setISODate($anio, $semana)->month,
            'anio' => $anio,
            'fechaInicio' => $fechaInicio->format('d/m/Y'),
            'fechaFin' => $fechaFin->format('d/m/Y'),
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'movimientos' => $movimientos,
            'totalEntradas' => $totalEntradas,
            'totalSalidas' => $totalSalidas,
            'totalMovimientos' => $movimientos->count(),
            'porTipo' => $porTipo,
            'resumenProductos' => $resumenProductos,
            'productosMasMovidos' => $productosMasMovidos,
        ];
        
        // Generar el PDF
        $pdf = Pdf::loadView('pdf.movimientos', $data);
        $pdf->setPaper('a4', 'portrait');
        
        // Ver el PDF en el navegador
        return $pdf->stream('movimientos_semana_' . $semana . '_' . $anio . '.pdf');
    }
}
