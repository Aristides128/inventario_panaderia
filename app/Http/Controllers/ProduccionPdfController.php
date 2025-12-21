<?php

namespace App\Http\Controllers;

use App\Models\Producciones;
use App\Models\DetalleProducciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProduccionPdfController extends Controller
{
    public function generarPdf($id)
    {
        // Primero intentamos buscar si el ID pertenece a un detalle de producción
        $detalle = DetalleProducciones::find($id);
        
        if ($detalle) {
            // Si es un detalle, obtenemos el ID de la producción padre
            $idProduccion = $detalle->id_produccion;
        } else {
            // Si no es un detalle, asumimos que es directamente el ID de producción
            $idProduccion = $id;
        }

        // Buscar la producción con todos sus detalles y relaciones necesarias
        $produccion = Producciones::with(['detalles.Producto', 'detalles.Empleado'])->findOrFail($idProduccion);
        
        // Preparar los datos para la vista
        $data = [
            'produccion' => $produccion,
            'detalles' => $produccion->detalles,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i:s'),
            'titulo' => 'Reporte de Producción #' . $produccion->id_produccion
        ];
        
        // Cargar la vista y generar el PDF
        $pdf = Pdf::loadView('pdf.producciones', $data);
        
        // Establecer el nombre del archivo
        $filename = 'Produccion_' . $produccion->id_produccion . '_' . Carbon::now()->format('Ymd_His') . '.pdf';
        
        // Retornar el PDF para su descarga o visualización
        return $pdf->stream($filename);
    }
}
