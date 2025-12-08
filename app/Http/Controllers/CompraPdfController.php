<?php

namespace App\Http\Controllers;

use App\Models\Compras;
use App\Models\DetalleCompras;
use App\Models\Productos;
use App\Models\Proveedores;
use App\Models\Sucursales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CompraPdfController extends Controller
{
    public function generarPdf($id)
    {
        // Buscar la compra con sus relaciones
        $compra = Compras::with(['Sucursales', 'detalleCompras.producto', 'detalleCompras.proveedor'])->findOrFail($id);
        
        // Calcular el total
        $total = $compra->detalleCompras->sum('subtotal');
        
        // Preparar los datos para el PDF
        $data = [
            'compra' => $compra,
            'sucursal' => $compra->Sucursales,
            'detalles' => $compra->detalleCompras,
            'total' => $total,
            'fecha' => now()->format('d/m/Y H:i:s'),
        ];
        
        // Generar el PDF
        $pdf = Pdf::loadView('pdf.compra', $data);

        // Ver el PDF en el navegador
        return $pdf->stream('compra_' . $compra->id_compra . '_' . now()->format('Ymd_His') . '.pdf');
        
        // Descargar el PDF
        return $pdf->download('compra_' . $compra->id_compra . '_' . now()->format('Ymd_His') . '.pdf');
    }
}
