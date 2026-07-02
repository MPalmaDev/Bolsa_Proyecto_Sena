<?php

namespace App\Http\Controllers;

use App\Events\EvidenciaEvent;
use App\Events\PostulacionEvent;
use App\Http\Requests\ActualizarPerfilRequest;
use App\Http\Requests\EnviarEvidenciaRequest;
use App\Models\Aprendiz;
use App\Models\Etapa;
use App\Models\Evidencia;
use App\Models\Postulacion;
use App\Models\Proyecto;
use App\Models\User;
use App\Services\FileProcessingService;
use App\Services\PostulacionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AprendizController extends Controller
{
    public function __construct(private PostulacionService $postulacionService) {}

    public function dashboard(): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'Perfil de aprendiz no encontrado.');
        }

        $totalPostulaciones = $aprendiz->postulaciones()->count();
        $postulacionesAprobadas = $aprendiz->postulaciones()
            ->where('estado', 'aceptada')
            ->count();
        $proyectosDisponibles = Proyecto::activos()->count();

        $proyectosRecientes = Proyecto::with(['empresa', 'instructor.usuario'])
            ->activos()
            ->recientes()
            ->limit(6)
            ->get();

        $proyectosAprobadosQuery = DB::table('postulaciones')
            ->where('aprendiz_id', $aprendiz?->id ?? 0)
            ->where('estado', 'aceptada');

        $proyectosAprobados = (clone $proyectosAprobadosQuery)->pluck('proyecto_id')->toArray();

        $proyectosAsociados = Proyecto::whereIn('id', $proyectosAprobados)->get();
        $proximoCierre = $proyectosAsociados->filter(function ($p) {
            $fechaFin = Carbon::parse($p->fecha_publicacion)->addDays($p->duracion_estimada_dias ?? 0);
            return $fechaFin->isFuture();
        })->sortBy(function ($p) {
            return Carbon::parse($p->fecha_publicacion)->addDays($p->duracion_estimada_dias ?? 0);
        })->first();

        return view('aprendiz.dashboard', compact(
            'aprendiz',
            'totalPostulaciones',
            'postulacionesAprobadas',
            'proyectosDisponibles',
            'proyectosRecientes',
            'proyectosAprobados',
            'proximoCierre'
        ));
    }

    public function proyectosAjax(Request $request): \Illuminate\Http\JsonResponse
    {
        $usrId = session('usr_id');
        $aprendiz = \App\Models\Aprendiz::where('usuario_id', $usrId)->first();

        $query = \App\Models\Proyecto::with('empresa')->activos();

        if ($request->filled('buscar')) {
            $query->busqueda($request->buscar);
        }
        if ($request->filled('categoria')) {
            $query->porCategoria($request->categoria);
        }

        $proyectos = $query->recientes()->paginate(9);

        $postulados = [];
        if ($aprendiz) {
            $postulados = \Illuminate\Support\Facades\DB::table('postulaciones')
                ->where('aprendiz_id', $aprendiz->id)
                ->pluck('proyecto_id')
                ->toArray();
        }

        $html = '';
        $total = $proyectos->total();

        if ($proyectos->isEmpty()) {
            $searchTerm = $request->filled('buscar') ? e($request->buscar) : ($request->filled('categoria') ? e($request->categoria) : '');
            $html = '<div style="padding:5rem 2rem;text-align:center;background:white;border-radius:24px;border:1px dashed rgba(62,180,137,0.2);">
                <div style="width:100px;height:100px;background:rgba(62,180,137,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                    <i class="fas fa-search" style="font-size:40px;color:#3eb489;"></i>
                </div>
                <h3 style="font-size:22px;font-weight:800;color:var(--text);margin-bottom:8px;">Sin resultados</h3>
                <p style="color:var(--text-light);max-width:400px;margin:0 auto 24px;">No encontramos proyectos que coincidan con tu búsqueda.</p>
                <a href="'.route('aprendiz.proyectos').'" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#3eb489;color:white;border-radius:12px;font-weight:700;text-decoration:none;">
                    <i class="fas fa-rotate-left"></i> Limpiar filtros
                </a>
            </div>';
        } else {
            $html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:24px;">';
            foreach ($proyectos as $p) {
                $yaPostulado = in_array($p->id, $postulados);
                $badge = $p->tipo_publicacion_badge;
                $badgeHtml = $badge ? '<div style="position:absolute;top:16px;right:16px;background:'.$badge['bg'].';color:white;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:flex;align-items:center;gap:6px;"><i class="fas '.$badge['icon'].'"></i> '.$badge['label'].'</div>' : '';
                $html .= '<div style="background:white;border-radius:24px;overflow:hidden;border:1px solid rgba(62,180,137,0.1);transition:all 0.3s;">
                    <div style="height:200px;position:relative;">
                        <img src="'.e($p->imagen_url).'" loading="lazy" alt="" style="width:100%;height:100%;object-fit:cover;">
                        <div style="position:absolute;top:16px;left:16px;background:linear-gradient(135deg,#3eb489,#2d9d74);color:white;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;">'.e($p->categoria).'</div>
                        '.$badgeHtml.'
                    </div>
                    <div style="padding:28px;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                            <div style="width:32px;height:32px;background:rgba(62,180,137,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-building" style="color:#3eb489;font-size:12px;"></i></div>
                            <span style="font-size:13px;font-weight:700;color:var(--text-light);">'.e($p->nombre).'</span>
                        </div>
                        <h3 style="font-size:20px;font-weight:800;color:var(--text);line-height:1.4;margin-bottom:20px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">'.e($p->titulo).'</h3>
                        <div style="display:flex;gap:20px;margin-bottom:24px;padding:14px;background:rgba(62,180,137,0.03);border-radius:14px;border:1px solid rgba(62,180,137,0.08);">
                            <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-light);font-weight:600;"><i class="fas fa-clock" style="color:#f59e0b;"></i> '.e($p->duracion_estimada_dias).' días</div>
                            <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-light);font-weight:600;"><i class="fas fa-users" style="color:#3eb489;"></i> '.($p->postulados_count ?? 0).' postulados</div>
                        </div>
                        <div style="margin-top:auto;">';
                if ($yaPostulado) {
                    $html .= '<div style="background:linear-gradient(135deg,#3eb489,#2d9d74);color:white;padding:14px;border-radius:16px;text-align:center;font-size:14px;font-weight:700;box-shadow:0 8px 20px rgba(62,180,137,0.3);"><i class="fas fa-check-circle" style="margin-right:8px;"></i> ¡Ya te has postulado!</div>';
                } else {
                    $html .= '<button type="button" class="btn-postular-ajax" data-id="'.$p->id.'" style="width:100%;padding:14px;background:linear-gradient(135deg,#3eb489,#2d9d74);color:white;border:none;border-radius:14px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 8px 20px rgba(62,180,137,0.3);transition:all 0.3s;">Postularme <i class="fas fa-paper-plane"></i></button>';
                }
                $html .= '</div></div></div>';
            }
            $html .= '</div>';
            $html .= '<div style="margin-top:50px;display:flex;justify-content:center;">'. $proyectos->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') .'</div>';
        }

        return response()->json([
            'success' => true,
            'html' => $html,
            'total' => $total,
            'count' => $proyectos->count(),
            'hasMore' => $proyectos->hasMorePages()
        ]);
    }

    public function proyectos(Request $request): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de aprendiz.');
        }

        $query = Proyecto::with('empresa')->activos();

        if ($request->filled('buscar')) {
            $query->busqueda($request->buscar);
        }

        if ($request->filled('categoria')) {
            $query->porCategoria($request->categoria);
        }

        $proyectos = $query->recientes()->paginate(9);

        $postulados = [];
        if ($aprendiz) {
            $postulados = DB::table('postulaciones')
                ->where('aprendiz_id', $aprendiz->id)
                ->pluck('proyecto_id')
                ->toArray();
        }

        $categorias = array_keys(config('programas'));

        return view('aprendiz.proyectos', compact('proyectos', 'postulados', 'categorias'));
    }

    public function postulacion(Request $request, int $id): RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = DB::table('aprendices')->where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return back()->with('error', 'Perfil de aprendiz no encontrado.');
        }

        [$exito, $mensaje] = $this->postulacionService->crear($aprendiz->id, $id);

        if (!$exito) {
            return back()->with('error', $mensaje);
        }

        return back()->with('success', 'Postulación enviada. Revisa tu correo.');
    }

    public function postular(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            $usrId = session('usr_id');
            $aprendiz = \App\Models\Aprendiz::where('usuario_id', $usrId)->first();

            if (!$aprendiz) {
                return response()->json(['success' => false, 'message' => 'Perfil de aprendiz no encontrado.'], 404);
            }

            [$exito, $mensaje] = $this->postulacionService->crear($aprendiz->id, $id);

            if ($exito) {
                $proyecto = Proyecto::find($id);
                $usuario = User::find($usrId);
                if ($usuario && $proyecto) {
                    event(new PostulacionEvent(
                        $usuario,
                        'nueva',
                        [
                            'message' => "Nueva postulación: {$proyecto->titulo}",
                            'proyecto' => $proyecto->titulo,
                            'usuario' => $aprendiz->nombres.' '.$aprendiz->apellidos,
                            'url' => route('admin.proyectos'),
                        ]
                    ));
                }
            }

            return response()->json([
                'success' => $exito,
                'message' => $exito ? 'Postulación enviada. Revisa tu correo.' : $mensaje
            ]);
        }

        return $this->postulacion($request, $id);
    }

    public function misPostulaciones(): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de aprendiz.');
        }

        $postulaciones = Postulacion::where('aprendiz_id', $aprendiz->id)
            ->with(['proyecto.empresa'])
            ->orderByDesc('fecha_postulacion')
            ->paginate(10);

        return view('aprendiz.postulaciones', compact('postulaciones'));
    }

    public function historial(Request $request): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de aprendiz.');
        }

        $query = Postulacion::where('aprendiz_id', $aprendiz->id)
            ->with(['proyecto.empresa', 'proyecto.instructor', 'proyecto.etapas.evidencias', 'proyecto.evidencias'])
            ->orderByDesc('fecha_postulacion');

        // ── Filtros ──
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('categoria')) {
            $query->whereHas('proyecto', fn($q) => $q->where('categoria', $request->categoria));
        }
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->whereHas('proyecto', fn($q) => $q->where('titulo', 'like', "%{$busqueda}%")
                ->orWhereHas('empresa', fn($q2) => $q2->where('nombre', 'like', "%{$busqueda}%")));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_postulacion', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_postulacion', '<=', $request->fecha_hasta);
        }

        // ── Ordenamiento ──
        $sort = $request->get('sort', 'reciente');
        match ($sort) {
            'antiguo' => $query->orderBy('fecha_postulacion'),
            'estado' => $query->orderBy('estado')->orderByDesc('fecha_postulacion'),
            default => $query->orderByDesc('fecha_postulacion'),
        };

        $total = Postulacion::where('aprendiz_id', $aprendiz->id)->count();
        $aprobadas = Postulacion::where('aprendiz_id', $aprendiz->id)->where('estado', 'aceptada')->count();
        $pendientes = Postulacion::where('aprendiz_id', $aprendiz->id)->where('estado', 'pendiente')->count();
        $rechazadas = Postulacion::where('aprendiz_id', $aprendiz->id)->where('estado', 'rechazada')->count();
        $tasaAceptacion = $total > 0 ? round(($aprobadas / $total) * 100) : 0;

        $categorias = Proyecto::whereHas('postulaciones', fn($q) => $q->where('aprendiz_id', $aprendiz->id))
            ->select('categoria')->distinct()->pluck('categoria');

        $proyectos = $query->paginate($this->getPerPage($request, 10, 5, 30))
            ->through(function ($postulacion) {
                $proyecto = $postulacion->proyecto;
                $diasTranscurridos = $postulacion->fecha_postulacion
                    ? now()->diffInDays($postulacion->fecha_postulacion)
                    : 0;

                $fechaFin = $proyecto->fecha_publicacion
                    ? Carbon::parse($proyecto->fecha_publicacion)->addDays($proyecto->duracion_estimada_dias)
                    : null;
                $totalEtapas = $proyecto->etapas->count();
                $etapasConEvidencias = $proyecto->etapas->filter(fn($e) => $e->evidencias->count() > 0)->count();
                $progreso = $totalEtapas > 0
                    ? round(($etapasConEvidencias / $totalEtapas) * 100)
                    : 0;

                return (object) [
                    'id' => $postulacion->id,
                    'estado' => $postulacion->estado,
                    'fecha_postulacion' => $postulacion->fecha_postulacion,
                    'dias_transcurridos' => $diasTranscurridos,
                    'pro_id' => $proyecto->id,
                    'titulo' => $proyecto->titulo,
                    'descripcion' => $proyecto->descripcion,
                    'categoria' => $proyecto->categoria,
                    'pro_estado' => $proyecto->estado,
                    'fecha_publi' => $proyecto->fecha_publicacion,
                    'fecha_finalizacion' => $fechaFin,
                    'duracion_dias' => $proyecto->duracion_estimada_dias,
                    'imagen_url' => $proyecto->imagen_url,
                    'nombre' => $proyecto->empresa->nombre ?? 'No asignada',
                    'instructor_nombre' => $proyecto->instructor
                        ? $proyecto->instructor->nombres.' '.$proyecto->instructor->apellidos
                        : 'No asignado',
                    'oferta' => $proyecto->oferta,
                    'oferta_otro' => $proyecto->oferta_otro,
                    'ubicacion' => $proyecto->ubicacion,
                    'habilidades' => $proyecto->habilidades_requeridas,
                    'requisitos' => $proyecto->requisitos_especificos,
                    'total_etapas' => $totalEtapas,
                    'etapas_completadas' => $etapasConEvidencias,
                    'progreso' => $progreso,
                ];
            });

        return view('aprendiz.historial', compact(
            'proyectos', 'total', 'aprobadas', 'pendientes', 'rechazadas',
            'tasaAceptacion', 'categorias'
        ));
    }

    public function misEntregas(Request $request): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de aprendiz.');
        }

        $proyectos = Proyecto::whereHas('postulaciones', function ($q) use ($aprendiz) {
            $q->where('aprendiz_id', $aprendiz->id)->where('estado', 'aceptada');
        })->with('empresa')
            ->paginate($this->getPerPage($request, 10, 5, 30))
            ->through(function ($p) {
                $p->emp_nombre = $p->empresa->nombre ?? 'No asignada';
                return $p;
            });

        $entregas = collect([]);
        $evidencias = $aprendiz->evidencias()
            ->with(['etapa', 'proyecto'])
            ->orderBy('proyecto_id')
            ->get();

        return view('aprendiz.mis-entregas', compact('proyectos', 'entregas', 'evidencias'));
    }

    public function verDetalleProyecto(int $proId): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de aprendiz.');
        }

        $proyecto = Proyecto::with(['empresa', 'instructor'])->findOrFail($proId);
        $proyecto->emp_nombre = $proyecto->empresa->nombre ?? 'No asignada';
        $proyecto->instructor_nombre = $proyecto->instructor
            ? $proyecto->instructor->nombres.' '.$proyecto->instructor->apellidos
            : 'No asignado';

        $postulacion = Postulacion::where('aprendiz_id', $aprendiz->id)
            ->where('proyecto_id', $proId)
            ->first();

        if ($postulacion && $postulacion->estado === 'aceptada') {
            $etapas = $proyecto->etapasOrdenadas();

            $evidencias = $aprendiz->evidencias()
                ->where('evidencias.proyecto_id', $proId)
                ->with('etapa')
                ->join('etapas', 'evidencias.etapa_id', '=', 'etapas.id')
                ->select(
                    'evidencias.*',
                    'etapas.nombre as eta_nombre',
                    'etapas.orden as eta_orden'
                )
                ->orderBy('etapas.orden')
                ->orderByDesc('evidencias.fecha_envio')
                ->get();

            return view('aprendiz.detalle-proyecto', compact('proyecto', 'etapas', 'evidencias', 'aprendiz'));
        }

        [$puedePostular, $mensaje] = $this->postulacionService->puedePostular($aprendiz->id, $proId);

        return view('aprendiz.detalle-proyecto-publico', compact(
            'proyecto',
            'aprendiz',
            'postulacion',
            'puedePostular',
            'mensaje'
        ));
    }

    public function enviarEvidencia(EnviarEvidenciaRequest $request, int $proId, int $etaId): RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->firstOrFail();

        $postulacion = DB::table('postulaciones')
            ->where('aprendiz_id', $aprendiz->id)
            ->where('proyecto_id', $proId)
            ->where('estado', 'aceptada')
            ->firstOrFail();

        $etapa = Etapa::where('id', $etaId)
            ->where('proyecto_id', $proId)
            ->firstOrFail();

        $existing = Evidencia::where('aprendiz_id', $aprendiz->id)
            ->where('etapa_id', $etaId)
            ->where('proyecto_id', $proId)
            ->whereIn('estado', ['aceptada', 'rechazada'])
            ->first();

        if ($existing) {
            return back()->with('error', 'No puedes enviar más evidencias para esta etapa porque ya fue evaluada.');
        }

        $archivoUrl = null;
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');

            $mime = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedMimes = [
                'application/pdf' => 'pdf',
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => 'png',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            ];

            $allowedExts = array_unique(array_merge(...array_map(fn($v) => (array) $v, array_values($allowedMimes))));
            if (!in_array($extension, $allowedExts)) {
                return back()->with('error', 'Extensión no permitida: .'.$extension);
            }

            if (!isset($allowedMimes[$mime])) {
                return back()->with('error', 'Tipo de archivo no permitido.');
            }

            $validExts = is_array($allowedMimes[$mime]) ? $allowedMimes[$mime] : [$allowedMimes[$mime]];
            if (!in_array($extension, $validExts)) {
                return back()->with('error', 'El archivo no coincide con su formato.');
            }

            $fileService = new FileProcessingService();
            $path = $fileService->processUpload($file, 'evidencias', [
                'watermark' => true,
                'scan_virus' => config('app.env') === 'production',
            ]);

            if (!$path) {
                return back()->with('error', 'Error al procesar el archivo.');
            }

            $archivoUrl = $path;
        }

        DB::table('evidencias')->insert([
            'aprendiz_id' => $aprendiz->id,
            'etapa_id' => $etaId,
            'proyecto_id' => $proId,
            'ruta_archivo' => $archivoUrl,
            'fecha_envio' => now(),
            'estado' => 'pendiente',
            'comentario_instructor' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usuario = User::find($usrId);
        $proyecto = Proyecto::find($proId);
        if ($usuario && $proyecto) {
            event(new EvidenciaEvent(
                $usuario,
                'subida',
                [
                    'message' => "Nueva evidencia enviada: {$etapa->nombre}",
                    'proyecto' => $proyecto->titulo,
                    'etapa' => $etapa->nombre,
                    'url' => route('instructor.evidencias.ver', $proId),
                ]
            ));
        }

        return back()->with('success', 'Evidencia enviada.');
    }

    public function perfil(): View|RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->first();

        if (!$aprendiz) {
            return redirect()->route('login')->with('error', 'No se encontro tu perfil de aprendiz.');
        }

        $usuario = User::findOrFail($usrId);

        return view('aprendiz.perfil', compact('aprendiz', 'usuario'));
    }

    public function actualizarPerfil(ActualizarPerfilRequest $request): RedirectResponse
    {
        $usrId = session('usr_id');
        $aprendiz = Aprendiz::where('usuario_id', $usrId)->firstOrFail();

        $aprendiz->update([
            'nombres' => $request->nombre,
            'apellidos' => $request->apellido,
        ]);

        if ($request->filled('password')) {
            $usuario = User::findOrFail($usrId);
            $usuario->update([
                'contrasena' => Hash::make($request->password),
            ]);
        }

        session(['nombre' => $request->nombre, 'apellido' => $request->apellido]);

        return back()->with('success', 'Perfil actualizado.');
    }
}