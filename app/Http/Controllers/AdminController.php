<?php

namespace App\Http\Controllers;

use App\Events\NuevoUsuarioEvent;
use App\Events\ProyectoEvent;
use App\Http\Requests\CambiarEstadoUsuarioRequest;
use App\Http\Requests\GestionarPostulacionRequest;
use App\Http\Requests\GestionarProyectoRequest;
use App\Mail\InstructorAsignado;
use App\Mail\InstructorDesasignado;
use App\Mail\RespuestaSoporte;
use App\Mail\ProyectoConOfertaAprobado;
use App\Models\Aprendiz;
use App\Models\AuditLog;
use App\Models\Empresa;
use App\Models\Evidencia;
use App\Models\Instructor;
use App\Models\MensajeSoporte;
use App\Models\Postulacion;
use App\Models\Proyecto;
use App\Models\PagoPublicacion;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\AppNotification;
use App\Http\Controllers\PagoController;
use App\Jobs\SendEmailJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function analytics(): View
    {
        return view('admin.analytics');
    }

    public function dashboard(): View
    {
        $stats = Cache::remember('admin_stats', now()->addMinutes(5), function () {
            return [
                'aprendices' => Aprendiz::count(),
                'instructores' => Instructor::count(),
                'empresas' => Empresa::count(),
                'proyectos' => Proyecto::count(),
                'pendientes' => Proyecto::where('estado', 'pendiente')->count(),
                'usuarios' => User::count(),
            ];
        });

        $proyectosRecientes = Cache::remember('admin_proyectos_recientes', now()->addMinutes(5), function () {
            return Proyecto::with('empresa')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        });

        $usuariosRecientes = Cache::remember('admin_usuarios_recientes', now()->addMinutes(5), function () {
            return User::orderByDesc('created_at')
                ->limit(5)
                ->get();
        });

        return view('admin.dashboard', compact('stats', 'proyectosRecientes', 'usuariosRecientes'));
    }

    public function usuarios(Request $request): View
    {
        $aprendices = Aprendiz::with('usuario')
            ->orderByDesc('id')
            ->paginate($this->getPerPage($request, 15, 5, 50), ['*'], 'aprendices');

        $instructores = Instructor::with('usuario')
            ->orderByDesc('id')
            ->paginate($this->getPerPage($request, 15, 5, 50), ['*'], 'instructores');

        $empresas = Empresa::orderByDesc('id')
            ->paginate($this->getPerPage($request, 15, 5, 50), ['*'], 'empresas');

        $totalAprendices = Aprendiz::count();
        $totalInstructores = Instructor::count();
        $aprendicesActivos = Aprendiz::where('activo', 1)->count();
        $instructoresActivos = Instructor::where('activo', 1)->count();
        $aprendicesProgramas = Aprendiz::select('programa_formacion')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('programa_formacion')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.usuarios', compact(
            'aprendices', 'instructores', 'empresas',
            'totalAprendices', 'totalInstructores',
            'aprendicesActivos', 'instructoresActivos',
            'aprendicesProgramas'
        ));
    }

    public function cambiarEstadoUsuario(CambiarEstadoUsuarioRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $usrId = session('usr_id');
        $nuevoEstado = $request->estado;
        $tipoUsuario = $request->tipo;

        if ($tipoUsuario === 'aprendiz') {
            $aprendiz = Aprendiz::with('usuario')->findOrFail($id);
            $anterior = ['activo' => $aprendiz->activo];
            $nombreTarget = $aprendiz->getFullNameAttribute();
            $aprendiz->update(['activo' => $nuevoEstado]);
            Cache::forget('admin_stats');
            $usrToNotify = $aprendiz->usuario;

            AuditLog::registrarCambio($usrId, 'cambiar_estado', 'usuarios', 'aprendices', $id,
                array_merge($anterior, ['nombre_objetivo' => $nombreTarget, 'tipo' => 'aprendiz']),
                ['activo' => $nuevoEstado, 'nombre_objetivo' => $nombreTarget, 'tipo' => 'aprendiz']);

            if ($nuevoEstado == 1 && $usrToNotify) {
                try {
                    $this->enviarCorreoBienvenida($usrToNotify->correo, $aprendiz->nombres ?? '', $aprendiz->apellidos ?? '');
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de bienvenida: '.$e->getMessage());
                }
            }
        } elseif ($tipoUsuario === 'instructor') {
            $instructor = Instructor::with('usuario')->findOrFail($id);
            $anterior = ['activo' => $instructor->activo];
            $nombreTarget = $instructor->nombres . ' ' . $instructor->apellidos;
            $instructor->update(['activo' => $nuevoEstado]);
            Cache::forget('admin_stats');
            $usrToNotify = $instructor->usuario;

            AuditLog::registrarCambio($usrId, 'cambiar_estado', 'usuarios', 'instructores', $id,
                array_merge($anterior, ['nombre_objetivo' => $nombreTarget, 'tipo' => 'instructor']),
                ['activo' => $nuevoEstado, 'nombre_objetivo' => $nombreTarget, 'tipo' => 'instructor']);

            if ($nuevoEstado == 1 && $usrToNotify) {
                try {
                    $this->enviarCorreoBienvenida($usrToNotify->correo, $instructor->nombres ?? '', $instructor->apellidos ?? '');
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de bienvenida: '.$e->getMessage());
                }
            }
        } else {
            $empresa = Empresa::with('usuario')->findOrFail($id);
            $anterior = ['activo' => $empresa->activo];
            $nombreTarget = $empresa->nombre;
            $empresa->update(['activo' => $nuevoEstado]);
            Cache::forget('admin_stats');
            $usrToNotify = $empresa->usuario;

            AuditLog::registrarCambio($usrId, 'cambiar_estado', 'empresas', 'empresas', $id,
                array_merge($anterior, ['nombre_objetivo' => $nombreTarget, 'tipo' => 'empresa']),
                ['activo' => $nuevoEstado, 'nombre_objetivo' => $nombreTarget, 'tipo' => 'empresa']);

            if ($nuevoEstado == 1 && $usrToNotify) {
                try {
                    $this->enviarCorreoBienvenida($usrToNotify->correo, $empresa->nombre, 'Empresa');
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de bienvenida: '.$e->getMessage());
                }
            }
        }

        try {
            if (isset($usrToNotify)) {
                $estadoTexto = $nuevoEstado == 1 ? 'activada' : 'desactivada';
                $usrToNotify->notify(new AppNotification(
                    'Cuenta '.$estadoTexto,
                    'Tu cuenta ha sido '.$estadoTexto.' por un administrador.',
                    $nuevoEstado == 1 ? 'fa-user-check' : 'fa-user-lock'
                ));
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar estado de usuario: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        $accion = $nuevoEstado == 1 ? 'activado' : 'desactivado';
        event(new NuevoUsuarioEvent($accion, [
            'message' => "Usuario {$accion}: {$nombreTarget} ({$tipoUsuario})",
            'usuario' => $nombreTarget,
            'rol' => $tipoUsuario,
            'estado' => $nuevoEstado,
            'url' => route('admin.usuarios'),
        ]));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado del usuario actualizado.',
                'activo' => (int) $nuevoEstado,
                'tipo' => $tipoUsuario,
                'id' => $id,
            ]);
        }

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function empresas(Request $request): View
    {
        $empresas = Empresa::orderByDesc('id')
            ->paginate($this->getPerPage($request, 15, 5, 50));

        $totalEmpresas = Empresa::count();
        $empresasActivas = Empresa::where('activo', 1)->count();
        $empresasConProyectos = Empresa::where('activo', 1)->whereHas('proyectos')->count();
        $empresasSinProyectos = Empresa::where('activo', 1)->whereDoesntHave('proyectos')->count();
        $topEmpresas = Empresa::withCount('proyectos')
            ->having('proyectos_count', '>', 0)
            ->orderByDesc('proyectos_count')
            ->limit(5)
            ->get();

        return view('admin.empresas', compact(
            'empresas', 'totalEmpresas', 'empresasActivas',
            'empresasConProyectos', 'empresasSinProyectos', 'topEmpresas'
        ));
    }

    public function cambiarEstadoEmpresa(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate(['estado' => 'required|in:0,1']);

        $usrId = session('usr_id');
        $empresa = Empresa::with('usuario')->findOrFail($id);

        $anterior = ['activo' => $empresa->activo];
        $empresa->update(['activo' => $request->estado]);
        Cache::forget('admin_stats');

        AuditLog::registrarCambio(
            $usrId,
            'cambiar_estado',
            'empresas',
            'empresas',
            $id,
            array_merge($anterior, ['nombre_objetivo' => $empresa->nombre, 'tipo' => 'empresa']),
            ['activo' => $request->estado, 'nombre_objetivo' => $empresa->nombre, 'tipo' => 'empresa']
        );

        try {
            if ($empresa->usuario) {
                $estadoTexto = $request->estado == 1 ? 'activada' : 'desactivada';
                $empresa->usuario->notify(new AppNotification(
                    'Empresa '.$estadoTexto,
                    'La cuenta de tu empresa ha sido '.$estadoTexto.' por un administrador.',
                    $request->estado == 1 ? 'fa-building-circle-check' : 'fa-building-circle-xmark'
                ));
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar estado de empresa: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado de empresa actualizado.',
                'activo' => (int) $request->estado,
                'empresaId' => $id,
            ]);
        }

        return back()->with('success', 'Estado de empresa actualizado.');
    }

    public function proyectos(Request $request): View
    {
        $validated = $request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => 'nullable|string|in:pendiente,aprobado,rechazado,cerrado,en_progreso',
            'categoria' => 'nullable|string|max:50',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'instructor_id' => 'nullable|integer|exists:usuarios,id',
        ]);

        $query = Proyecto::with(['empresa', 'instructor.usuario'])
            ->withCount([
                'postulaciones',
                'postulacionesAprobadas as postulaciones_aprobadas_count',
            ]);

        if (! empty($validated['buscar'])) {
            $buscar = addcslashes($validated['buscar'], '%_\\');
            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('titulo LIKE ?', ["%{$buscar}%"])
                    ->orWhereRaw('descripcion LIKE ?', ["%{$buscar}%"])
                    ->orWhereHas('empresa', function ($q) use ($buscar) {
                        $q->whereRaw('nombre LIKE ?', ["%{$buscar}%"]);
                    });
            });
        }

        if (! empty($validated['estado'])) {
            $query->where('estado', $validated['estado']);
        }

        if (! empty($validated['categoria'])) {
            $query->where('categoria', $validated['categoria']);
        }

        if (! empty($validated['fecha_inicio'])) {
            $query->whereDate('fecha_publicacion', '>=', $validated['fecha_inicio']);
        }

        if (! empty($validated['fecha_fin'])) {
            $query->whereDate('fecha_publicacion', '<=', $validated['fecha_fin']);
        }

        if (! empty($validated['instructor_id'])) {
            $query->where('instructor_usuario_id', $validated['instructor_id']);
        }

        $proyectosPaginados = $query->orderByDesc('id')->paginate($this->getPerPage($request, 15, 5, 50));

        $proyectos = $proyectosPaginados->getCollection()->map(function ($proyecto) {
            return (object) [
                'id' => $proyecto->id,
                'titulo' => $proyecto->titulo,
                'empresa_nit' => $proyecto->empresa_nit,
                'instructor_usuario_id' => $proyecto->instructor_usuario_id,
                'calidad_aprobada' => (bool) $proyecto->calidad_aprobada,
                'estado' => $proyecto->estado,
                'categoria' => $proyecto->categoria,
                'fecha_publicacion' => $proyecto->fecha_publicacion,
                'empresa_nombre' => $proyecto->empresa?->nombre,
                'instructor_nombre' => $proyecto->instructor?->nombres,
                'postulaciones_count' => $proyecto->postulaciones_count ?? 0,
                'postulaciones_aprobadas_count' => $proyecto->postulaciones_aprobadas_count ?? 0,
            ];
        });

        $proyectosCount = Proyecto::count();
        $proyectosPendientes = Proyecto::where('estado', 'pendiente')->count();
        $proyectosAprobados = Proyecto::where('estado', 'aprobado')->count();
        $proyectosEnProgreso = Proyecto::where('estado', 'en_progreso')->count();
        $proyectosCompletados = Proyecto::where('estado', 'completado')->count();
        $proyectosRechazados = Proyecto::where('estado', 'rechazado')->count();
        $proyectosCerrados = Proyecto::where('estado', 'cerrado')->count();
        $totalPostulaciones = Postulacion::count();

        $instructores = Instructor::with('usuario')->get();

        $categorias = collect(array_keys(config('programas')));

        return view('admin.proyectos', compact(
            'proyectos', 'proyectosPaginados', 'instructores', 'categorias',
            'proyectosCount', 'proyectosPendientes', 'proyectosAprobados',
            'proyectosEnProgreso', 'proyectosCompletados', 'proyectosRechazados',
            'proyectosCerrados', 'totalPostulaciones'
        ));
    }

    public function cambiarEstadoProyecto(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'estado' => 'required|in:aprobado,rechazado,pendiente,cerrado,en_progreso,completado',
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser uno de los valores permitidos.',
        ]);

        $proyecto = Proyecto::findOrFail($id);

        // Si el proyecto se inactiva, desasociar el instructor y notificarlo
        if ($request->estado === 'cerrado' || $request->estado === 'rechazado') {
            $proyectoActual = $proyecto->load('empresa');
            $instructorUsuarioId = $proyecto->instructor_usuario_id;
            $empresaUsr = $proyectoActual->empresa->usuario ?? null;

            $proyecto->update([
                'estado' => $request->estado,
                'instructor_usuario_id' => null,
                'calidad_aprobada' => false,
            ]);
            Cache::forget('admin_stats');
            Cache::forget('admin_proyectos_recientes');

            // Audit log detallado
            AuditLog::registrarCambio(
                session('usr_id'),
                'cambiar_estado',
                'proyectos',
                'proyectos',
                $id,
                ['estado' => $proyecto->getOriginal('estado'), 'nombre_objetivo' => $proyectoActual->titulo],
                ['estado' => $request->estado, 'nombre_objetivo' => $proyectoActual->titulo]
            );

            // Notificar a la empresa
            if ($empresaUsr) {
                $empresaUsr->notify(new AppNotification(
                    'Proyecto '.ucfirst($request->estado),
                    'Tu proyecto "'.Str::limit($proyectoActual->titulo, 30).'" ha sido '.$request->estado.'.',
                    $request->estado === 'cerrado' ? 'fa-ban' : 'fa-xmark'
                ));
            }

            // Notificar al instructor que fue desasignado
            if ($instructorUsuarioId) {
                try {
                    $instructorUsuario = User::where('id', $instructorUsuarioId)->with('instructor')->first();
                    if ($instructorUsuario && $instructorUsuario->instructor) {
                        $instructorNombre = $instructorUsuario->instructor->nombres.' '.$instructorUsuario->instructor->apellidos;
                        AuditLog::registrar(session('usr_id'), 'desasignar', 'proyectos', 'proyectos', $id, ['instructor_usuario_id' => $instructorUsuarioId, 'nombre_objetivo' => $proyectoActual->titulo], ['instructor_usuario_id' => null, 'nombre_objetivo' => $proyectoActual->titulo, 'instructor' => $instructorNombre], "Se ha destituido al instructor {$instructorNombre} del proyecto \"{$proyectoActual->titulo}\" debido al cierre o rechazo del proyecto.");

                        SendEmailJob::dispatch($instructorUsuario->correo, new InstructorDesasignado($instructorNombre, $proyectoActual->titulo, $proyectoActual->empresa->nombre));

                        $instructorUsuario->notify(new AppNotification(
                            'Desasignado de proyecto',
                            'Has sido removido del proyecto: '.Str::limit($proyectoActual->titulo, 30),
                            'fa-user-minus'
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error('Error al actualizar estado de usuario: '.$e->getCode());
                }
            }

            event(new ProyectoEvent($request->estado, [
                'message' => "Proyecto {$request->estado}: {$proyectoActual->titulo}",
                'proyecto' => $proyectoActual->titulo,
                'empresa' => $proyectoActual->empresa->nombre ?? '',
                'url' => route('admin.proyectos'),
            ]));
        } else {
            $proyecto->update([
                'estado' => $request->estado,
                'calidad_aprobada' => $request->estado === 'aprobado' ? true : $proyecto->calidad_aprobada,
            ]);
            Cache::forget('admin_stats');
            Cache::forget('admin_proyectos_recientes');

            // Audit log detallado
            AuditLog::registrarCambio(
                session('usr_id'),
                'cambiar_estado',
                'proyectos',
                'proyectos',
                $id,
                ['estado' => $proyecto->getOriginal('estado'), 'nombre_objetivo' => $proyecto->titulo],
                ['estado' => $request->estado, 'nombre_objetivo' => $proyecto->titulo]
            );
            $proyectoActual = $proyecto->load('empresa');
            $empresaUsr = $proyectoActual->empresa->usuario ?? null;
            if ($empresaUsr) {
                $empresaUsr->notify(new AppNotification(
                    'Estado de Proyecto: '.ucfirst($request->estado),
                    'El estado de tu proyecto "'.Str::limit($proyectoActual->titulo, 30).'" es: '.$request->estado,
                    'fa-info-circle'
                ));
            }

            // Notificar a todos los aprendices si el proyecto tiene una oferta
            if ($request->estado === 'aprobado' && !empty($proyectoActual->oferta)) {
                try {
                    $aprendices = Aprendiz::where('activo', true)->with('usuario')->get();
                    foreach ($aprendices as $aprendiz) {
                        $correo = optional($aprendiz->usuario)->correo;
                        if ($correo) {
                            SendEmailJob::dispatch($correo, new ProyectoConOfertaAprobado(
                                $aprendiz->nombres ?? 'Aprendiz',
                                $proyectoActual
                            ));
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al enviar correos de oferta a aprendices: ' . $e->getMessage());
                }
            }

            event(new ProyectoEvent($request->estado, [
                'message' => "Proyecto {$request->estado}: {$proyectoActual->titulo}",
                'proyecto' => $proyectoActual->titulo,
                'empresa' => $proyectoActual->empresa->nombre ?? '',
                'url' => route('admin.proyectos'),
            ]));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado del proyecto actualizado.',
                'estado' => $request->estado,
                'proyectoId' => $id,
            ]);
        }

        return back()->with('success', 'Estado del proyecto actualizado.');
    }

    public function asignarInstructor(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'instructor_usuario_id' => 'required|exists:usuarios,id',
        ]);

        $proyecto = Proyecto::findOrFail($id);

        if (!in_array($proyecto->estado, ['pendiente', 'aprobado'])) {
            return back()->with('error', 'No se puede asignar instructor a un proyecto en estado '.$proyecto->estado.'.');
        }

        $proyecto->update(['instructor_usuario_id' => $request->instructor_usuario_id]);
        Cache::forget('admin_stats');

        AuditLog::registrarCambio(
            session('usr_id'),
            'asignar',
            'proyectos',
            'proyectos',
            $id,
            ['instructor_usuario_id' => $proyecto->getOriginal('instructor_usuario_id'), 'nombre_objetivo' => $proyecto->titulo],
            ['instructor_usuario_id' => $request->instructor_usuario_id, 'nombre_objetivo' => $proyecto->titulo]
        );

        // Enviar correo de notificación al instructor asignado
        try {
            $proyecto->load('empresa');

            $instructorUsuario = User::where('id', $request->instructor_usuario_id)
                ->with('instructor')
                ->first();

            if ($proyecto && $instructorUsuario && $instructorUsuario->instructor) {
                $totalPostulaciones = Postulacion::where('proyecto_id', $id)
                    ->where('estado', 'pendiente')
                    ->count();

                SendEmailJob::dispatch($instructorUsuario->correo, new InstructorAsignado(
                    $instructorUsuario->instructor->nombres,
                    $proyecto,
                    $totalPostulaciones
                ));

                $instructorUsuario->notify(new AppNotification(
                    'Proyecto Asignado',
                    'Te han asignado liderar el proyecto: '.Str::limit($proyecto->titulo, 30),
                    'fa-chalkboard-user'
                ));
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de asignación de instructor: '.$e->getMessage());
        }

        $proyecto->load('empresa.usuario');
        $instructorNombre = optional($proyecto->instructor)->nombres ?? 'Instructor';
        event(new ProyectoEvent('asignado', [
            'message' => "Instructor {$instructorNombre} asignado al proyecto: {$proyecto->titulo}",
            'proyecto' => $proyecto->titulo,
            'empresa' => optional($proyecto->empresa)->nombre ?? 'Empresa',
            'instructor' => $instructorNombre,
            'url' => route('admin.proyectos.revisar', $proyecto->id),
        ]));

        return back()->with('success', 'Instructor asignado.');
    }

    public function mensajesSoporte(): View
    {
        $mensajes = MensajeSoporte::orderByDesc('created_at')->paginate(15);
        $totalMensajes = MensajeSoporte::count();
        $pendientes = MensajeSoporte::where('estado', 'pendiente')->count();
        $respondidos = MensajeSoporte::where('estado', 'respondido')->count();
        $mensajesPorMotivo = MensajeSoporte::select('motivo')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('motivo')
            ->orderByDesc('total')
            ->get();

        return view('admin.mensajes-soporte', compact(
            'mensajes', 'totalMensajes', 'pendientes', 'respondidos', 'mensajesPorMotivo'
        ));
    }

    public function responderMensajeSoporte(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'respuesta' => 'required|string',
        ]);

        $mensaje = MensajeSoporte::findOrFail($id);
        $mensaje->update([
            'respuesta' => $request->respuesta,
            'estado' => 'respondido',
        ]);

        AuditLog::registrar(session('usr_id'), 'editar', 'soporte', "Se respondió al mensaje de soporte #{$id} de {$mensaje->nombre}");

        try {
            SendEmailJob::dispatch($mensaje->email, new RespuestaSoporte(
                $mensaje->nombre,
                $mensaje->motivo,
                $mensaje->mensaje,
                $mensaje->respuesta
            ));
        } catch (\Exception $e) {
            Log::error('Error al enviar respuesta de soporte: ' . $e->getMessage());
            return back()->with('error', 'Respuesta guardada, pero error al enviar el correo.');
        }

        return back()->with('success', 'Respuesta enviada.');
    }

    public function pagos(Request $request): View
    {
        $query = PagoPublicacion::with(['proyecto', 'empresa', 'confirmador'])
            ->orderByDesc('created_at');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $pagos = $query->paginate(20);

        $resumen = [
            'total' => PagoPublicacion::count(),
            'pendientes' => PagoPublicacion::where('estado', 'pendiente')->count(),
            'confirmados' => PagoPublicacion::where('estado', 'confirmado')->count(),
            'rechazados' => PagoPublicacion::where('estado', 'rechazado')->count(),
            'ingresos' => PagoPublicacion::where('estado', 'confirmado')->sum('monto'),
        ];

        return view('admin.pagos', compact('pagos', 'resumen'));
    }

    public function confirmarPago(int $id): RedirectResponse
    {
        $pago = PagoPublicacion::with('proyecto')->findOrFail($id);

        if ($pago->estado !== 'pendiente') {
            return back()->with('error', 'Este pago ya fue procesado.');
        }

        app(PagoController::class)->activarPlan($pago->proyecto, $pago);

        AuditLog::registrar(session('usr_id'), 'confirmar', 'pagos_publicacion', 'pagos', $id, null, ['nombre_objetivo' => $pago->proyecto->titulo, 'tipo' => $pago->tipo, 'monto' => $pago->monto], "Pago {$pago->tipo} confirmado para el proyecto {$pago->proyecto->titulo} por \${$pago->monto} COP.");

        return back()->with('success', 'Pago confirmado. Plan de visibilidad activado.');
    }

    public function rechazarPago(int $id): RedirectResponse
    {
        $pago = PagoPublicacion::findOrFail($id);

        if ($pago->estado !== 'pendiente') {
            return back()->with('error', 'Este pago ya fue procesado.');
        }

        $pago->update(['estado' => 'rechazado', 'confirmado_por' => session('usr_id')]);

        $empresa = $pago->empresa;
        if ($empresa?->usuario) {
            $empresa->usuario->notify(new AppNotification(
                'Pago rechazado',
                "El pago para {$pago->proyecto->titulo} fue rechazado. Contacte al administrador.",
                'fa-credit-card',
                route('empresa.proyectos.detalle', $pago->proyecto_id)
            ));
        }

        AuditLog::registrar(session('usr_id'), 'rechazar', 'pagos_publicacion', 'pagos', $id, null, ['nombre_objetivo' => $pago->proyecto->titulo], "Pago rechazado para el proyecto {$pago->proyecto->titulo}.");

        return back()->with('success', 'Pago rechazado.');
    }

    public function revisarProyecto(int $id): View
    {
        $proyecto = Proyecto::with('empresa')->findOrFail($id);
        $calidad = $proyecto->calidadProyecto();

        $instructores = \App\Models\User::where('rol_id', 2)
            ->whereHas('instructor', fn($q) => $q->where('activo', true))
            ->with('instructor')
            ->get();

        return view('admin.revisar-proyecto', compact('proyecto', 'calidad', 'instructores'));
    }
}
