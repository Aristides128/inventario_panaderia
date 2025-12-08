<?php

namespace App\Http\Controllers;

use App\Models\Envios;
use App\Models\DetalleEnvio;
use App\Models\Productos;
use App\Models\Sucursales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EnvioPdfController extends Controller
{
    public function generarPdf($id)
    {
        // Buscar el envío con sus relaciones
        $envio = Envios::with(['Sucursales', 'detalleEnvios.producto'])->findOrFail($id);
        
        // Obtener la sucursal destino
        $sucursalDestino = Sucursales::find($envio->sucursal_destino_id);
        
        // Preparar los datos para el PDF
        $data = [
            'envio' => $envio,
            'sucursalOrigen' => $envio->Sucursales,
            'sucursalDestino' => $sucursalDestino,
            'detalles' => $envio->detalleEnvios,
            'fecha' => now()->format('d/m/Y H:i:s'),
        ];
        
        // Generar el PDF
        $pdf = Pdf::loadView('pdf.envio', $data);
        
        // Descargar el PDF
        return $pdf->download('envio_' . $envio->id_envio . '_' . now()->format('Ymd_His') . '.pdf');
    }
}
