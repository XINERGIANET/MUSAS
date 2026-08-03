<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\Category;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function save_job(Request $request)
    {
        // Recibe el JSON del body
        $json = $request->getContent();
        $data = json_decode($json, true);

        $id = $data['idVenta'];
        $sede = $data['sede'];


        // Crea el nombre del archivo (puedes personalizarlo)
        $filename = 'job_' . "{$id}" . '.json';

        // Guarda el archivo en storage/app/impresiones
        \Storage::disk('public')->put("impresiones/{$sede}/" . $filename, $json);

        return response()->json(['status' => true, 'message' => 'Archivo guardado', 'file' => $filename]);
    }

    public function get_jobs(Request $request)
    {
        $sede = $request->sede;
        $files = \Storage::disk('public')->files("impresiones/{$sede}/");
        $jobs = [];

        foreach ($files as $file) {
            if (substr($file, -5) === '.json') {
                $content = \Storage::disk('public')->get($file);
                $jobs[] = json_decode($content, true);
            }
        }

        if (empty($jobs)) {
            return response()->json([
                'status' => false
            ]);
        }

        return response()->json([
            'status' => true,
            'jobs' => $jobs
        ]);
    }

    public function delete_job(Request $request)
    {
        $sede = $request->sede;
        $id = $request->id;

        if (!$sede || !$id) {
            return response()->json(['status' => false, 'message' => 'Faltan parámetros'], 400);
        }

        $filename = "impresiones/{$sede}/job_{$id}.json";

        if (\Storage::disk('public')->exists($filename)) {
            \Storage::disk('public')->delete($filename);
            return response()->json(['status' => true, 'message' => 'Job eliminado']);
        } else {
            return response()->json(['status' => false, 'message' => 'Job no encontrado'], 404);
        }
    }

}