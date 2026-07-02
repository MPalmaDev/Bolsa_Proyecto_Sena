<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Empresa;
use App\Models\Aprendiz;
use App\Models\Instructor;

class HomeController extends Controller
{
    public function index()
    {
        $totalProyectos = Proyecto::whereIn('estado', ['aprobado', 'en_progreso'])->count();
        $totalEmpresas = Empresa::where('activo', 1)->count();
        $totalAprendices = Aprendiz::where('activo', true)->count();
        $totalInstructores = Instructor::where('activo', true)->count();

        $proyectosPatrocinados = Proyecto::with('empresa')
            ->whereIn('estado', ['aprobado', 'en_progreso'])
            ->where('tipo_publicacion', 'patrocinado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin_plan')
                    ->orWhere('fecha_fin_plan', '>=', now());
            })
            ->orderByDesc('fecha_publicacion')
            ->limit(6)
            ->get();

        return view('index', compact(
            'totalProyectos',
            'totalEmpresas',
            'totalAprendices',
            'totalInstructores',
            'proyectosPatrocinados'
        ));
    }
}