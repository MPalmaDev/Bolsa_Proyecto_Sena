<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AprendizController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ExportController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\InfiniteScrollController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/nosotros', function () {
    return view('nosotros');
})->name('nosotros');

Route::get('/soporte', function () {
    return view('soporte');
})->name('soporte');

Route::get('/politica-de-datos', function () {
    return view('politica-datos');
})->name('politica.datos');

Route::get('/terminos-y-condiciones', function () {
    return view('terminos-condiciones');
})->name('terminos.condiciones');



Route::post('/soporte/enviar', [App\Http\Controllers\SoporteController::class, 'enviar'])->name('soporte.enviar');

/*
|--------------------------------------------------------------------------
| Rutas de autenticación
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify')->middleware('signed');

Route::get('/email/resend-verification', [AuthController::class, 'resendVerification'])->name('verification.resend');

// Recuperación de contraseña
Route::get('/olvide-contraseña', [AuthController::class, 'showOlvideContraseña'])->name('auth.olvide-contraseña');
//  SEGURIDAD: Rate limiting más restrictivo (3 por 5 min) para prevenir enumeración
Route::post('/enviar-recuperacion', [AuthController::class, 'enviarEnlaceRecuperacion'])->name('auth.enviar-recuperacion')->middleware('throttle:3,5');
//  SEGURIDAD: Nueva ruta sin email en query parameters
Route::get('/recuperar-contraseña/{token}', [AuthController::class, 'mostrarFormularioRestablecerContraseña'])->name('auth.mostrar-restablecer-seguro');
Route::post('/restablecer-contraseña', [AuthController::class, 'restablecerContraseña'])->name('auth.restablecer-contraseña')->middleware('throttle:5,1');

// Registros
Route::get('/registro/aprendiz', [AuthController::class, 'showRegistroAprendiz'])->name('registro.aprendiz');
Route::get('/registro/empresa', [AuthController::class, 'showRegistroEmpresa'])->name('registro.empresa');
Route::get('/registro/instructor', [AuthController::class, 'showRegistroInstructor'])->name('registro.instructor');
Route::post('/registro/aprendiz', [AuthController::class, 'registrarAprendiz'])->name('registro.aprendiz.post')->middleware('throttle:5,1');
Route::post('/registro/empresa', [AuthController::class, 'registrarEmpresa'])->name('registro.empresa.post')->middleware('throttle:5,1');
Route::post('/registro/instructor', [AuthController::class, 'registrarInstructor'])->name('registro.instructor.post')->middleware('throttle:5,1');



Route::middleware(['auth.custom'])->group(function () {
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/leer-todas', [NotificacionController::class, 'leerTodas'])->name('notificaciones.leer_todas');
    Route::post('/notificaciones/{id}/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');

    // Descarga de archivos
    Route::get('/descargar/evidencia/{id}', [FileController::class, 'evidencia'])->name('file.evidencia');
    Route::get('/descargar/etapa-documento/{id}', [FileController::class, 'etapaDocumento'])->name('file.etapa-documento');
    Route::get('/descargar/proyecto-estructura/{id}', [FileController::class, 'proyectoEstructura'])->name('file.proyecto-estructura');
    Route::get('/descargar/empresa-metodologia/{id}', [FileController::class, 'empresaMetodologia'])->name('file.empresa-metodologia');

    // Retiro de consentimiento de datos personales
    Route::post('/consentimiento/retirar', [AuthController::class, 'retirarConsentimiento'])->name('consentimiento.retirar');
});

/*
|--------------------------------------------------------------------------
| Rutas de Chat — Empresa (rol 3) e Instructor (rol 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom', 'rol:1,2,3'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/{conversation}', [ChatController::class, 'show'])->name('show');
    Route::post('/', [ChatController::class, 'store'])->name('store');
    Route::post('/{conversation}/messages', [ChatController::class, 'send'])->name('send');
    Route::post('/{conversation}/read', [ChatController::class, 'markRead'])->name('read');
    Route::get('/{conversation}/poll', [ChatController::class, 'poll'])->name('poll');
    Route::get('/unread/count', [ChatController::class, 'unreadCount'])->name('unread');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas — Aprendiz (rol 1)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom', 'rol:1'])->prefix('aprendiz')->name('aprendiz.')->group(function () {
    Route::get('/dashboard', [AprendizController::class, 'dashboard'])->name('dashboard');
    Route::get('/proyectos', [AprendizController::class, 'proyectos'])->name('proyectos');
    Route::get('/proyectos/ajax', [AprendizController::class, 'proyectosAjax'])->name('proyectos.ajax');
    // SEGURIDAD: Rate limiting en postulación (máx 10/minuto)
    Route::post('/proyectos/{id}/postular', [AprendizController::class, 'postular'])->name('postular')->middleware('throttle:10,1');
    Route::get('/mis-postulaciones', [AprendizController::class, 'misPostulaciones'])->name('postulaciones');
    Route::get('/proyectos/{id}/detalle', [AprendizController::class, 'verDetalleProyecto'])->name('proyecto.detalle');
    //  SEGURIDAD: Rate limiting más estricto en envío de evidencia (máx 5/minuto)
    Route::post('/proyectos/{proId}/etapas/{etaId}/evidencia', [AprendizController::class, 'enviarEvidencia'])->name('evidencia.enviar')->middleware('throttle:5,1');
    Route::get('/historial', [AprendizController::class, 'historial'])->name('historial');
    Route::get('/mis-entregas', [AprendizController::class, 'misEntregas'])->name('entregas');
    Route::get('/perfil', [AprendizController::class, 'perfil'])->name('perfil');
    Route::put('/perfil', [AprendizController::class, 'actualizarPerfil'])->name('perfil.update');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas — Empresa (rol 3)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom', 'rol:3'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', [EmpresaController::class, 'dashboard'])->name('dashboard');
    Route::get('/proyectos', [EmpresaController::class, 'proyectos'])->name('proyectos');
    Route::get('/proyectos/crear', [EmpresaController::class, 'crearProyecto'])->name('proyectos.crear');
    Route::post('/proyectos', [EmpresaController::class, 'guardarProyecto'])->name('proyectos.store');
    Route::get('/proyectos/{id}/detalle', [EmpresaController::class, 'verDetalle'])->name('proyectos.detalle')->middleware('ownership:proyecto,id');
    Route::get('/proyectos/{id}/editar', [EmpresaController::class, 'editarProyecto'])->name('proyectos.edit')->middleware('ownership:proyecto,id');
    Route::put('/proyectos/{id}', [EmpresaController::class, 'actualizarProyecto'])->name('proyectos.update')->middleware('ownership:proyecto,id');
    Route::delete('/proyectos/{id}', [EmpresaController::class, 'eliminarProyecto'])->name('proyectos.destroy')->middleware('ownership:proyecto,id');
    Route::get('/proyectos/{id}/postulantes', [EmpresaController::class, 'verPostulantes'])->name('proyectos.postulantes')->middleware('ownership:proyecto,id');
    Route::get('/proyectos/{id}/participantes', [EmpresaController::class, 'verParticipantes'])->name('proyectos.participantes')->middleware('ownership:proyecto,id');
    Route::get('/proyectos/{id}/reporte', [EmpresaController::class, 'verReporte'])->name('proyectos.reporte')->middleware('ownership:proyecto,id');
    // Rutas de monetización
    Route::get('/proyectos/{id}/seleccionar-plan', [App\Http\Controllers\PagoController::class, 'mostrarFormularioPago'])->name('proyectos.plan')->middleware('ownership:proyecto,id');
    Route::get('/proyectos/{id}/pago', [App\Http\Controllers\PagoController::class, 'mostrarPago'])->name('proyectos.pago')->middleware('ownership:proyecto,id');
    Route::post('/proyectos/{id}/pago', [App\Http\Controllers\PagoController::class, 'procesarPago'])->name('proyectos.pago.procesar')->middleware('ownership:proyecto,id');
    Route::get('/proyectos/{proyectoId}/nequi/simular/{pagoId}/{transaction_id}', [App\Http\Controllers\PagoController::class, 'mostrarSimulacionNequi'])->name('proyectos.nequi.simular')->middleware('ownership:proyecto,proyectoId');
    Route::post('/proyectos/nequi/simular/procesar', [App\Http\Controllers\NequiWebhookController::class, 'simulateApprove'])->name('proyectos.nequi.simular.procesar');
    Route::get('/perfil', [EmpresaController::class, 'perfil'])->name('perfil');
    Route::put('/perfil', [EmpresaController::class, 'actualizarPerfil'])->name('perfil.update');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas — Instructor (rol 2)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom', 'rol:2'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');
    Route::get('/proyectos', [InstructorController::class, 'proyectos'])->name('proyectos');
    Route::get('/proyectos/{id}', [InstructorController::class, 'detalleProyecto'])->name('proyecto.detalle')->middleware('ownership:proyecto,id');
    Route::get('/historial', [InstructorController::class, 'historial'])->name('historial');
    Route::get('/proyectos/{id}/reporte', [InstructorController::class, 'reporteSeguimiento'])->name('reporte')->middleware('ownership:proyecto,id');
    //  SEGURIDAD: Rate limiting en cambio de estado (máx 30/minuto)
    Route::post('/postulaciones/{id}/estado', [InstructorController::class, 'cambiarEstadoPostulacion'])->name('postulaciones.estado')->middleware('throttle:30,1')->middleware('ownership:postulacion,id');

    // RUTAS PARA ETAPAS
    Route::post('/proyectos/{id}/etapas', [InstructorController::class, 'crearEtapa'])->name('etapas.crear')->middleware('ownership:proyecto,id');
    Route::put('/etapas/{id}', [InstructorController::class, 'editarEtapa'])->name('etapas.editar')->middleware('ownership:etapa,id');
    Route::delete('/etapas/{id}', [InstructorController::class, 'eliminarEtapa'])->name('etapas.eliminar')->middleware('ownership:etapa,id');
    Route::post('/etapas/{id}/documento', [InstructorController::class, 'subirDocumentoEtapa'])->name('etapas.documento')->middleware('ownership:etapa,id');

    // RUTAS PARA IMAGEN DE PROYECTO
    Route::post('/proyectos/{id}/imagen', [InstructorController::class, 'subirImagenProyecto'])->name('proyectos.imagen')->middleware('ownership:proyecto,id');

    // RUTAS PARA ESTRUCTURA DEL MAPA DE RUTA
    Route::post('/proyectos/{id}/estructura', [InstructorController::class, 'subirEstructura'])->name('proyectos.estructura')->middleware('ownership:proyecto,id');
    Route::delete('/proyectos/{id}/estructura', [InstructorController::class, 'eliminarEstructura'])->name('proyectos.estructura.eliminar')->middleware('ownership:proyecto,id');

    //  SEGURIDAD: Rate limiting en cambios de estado (máx 10/minuto)
    Route::post('/proyectos/{id}/iniciar', [InstructorController::class, 'iniciarProyecto'])->name('proyectos.iniciar')->middleware('throttle:10,1')->middleware('ownership:proyecto,id');
    Route::post('/proyectos/{id}/completar', [InstructorController::class, 'marcarCompletado'])->name('proyectos.completar')->middleware('throttle:10,1')->middleware('ownership:proyecto,id');

    // RUTAS PARA EVIDENCIAS
    Route::get('/proyectos/{id}/evidencias', [InstructorController::class, 'verEvidencias'])->name('evidencias.ver')->middleware('ownership:proyecto,id');
    //  SEGURIDAD: Rate limiting en calificación (máx 20/minuto)
    Route::put('/evidencias/{id}', [InstructorController::class, 'calificarEvidencia'])->name('evidencias.calificar')->middleware('throttle:20,1')->middleware('ownership:evidencia,id');

    Route::get('/aprendices', [InstructorController::class, 'aprendices'])->name('aprendices');
    Route::get('/perfil', [InstructorController::class, 'perfil'])->name('perfil');
    Route::put('/perfil', [InstructorController::class, 'actualizarPerfil'])->name('perfil.update');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas — Administrador (rol 4)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth.custom', 'rol:4'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
    //  SEGURIDAD: Rate limiting en cambio de estado (máx 30/minuto)
    Route::post('/usuarios/{id}/estado', [AdminController::class, 'cambiarEstadoUsuario'])->name('usuarios.estado')->middleware('throttle:30,1');
    Route::get('/empresas', [AdminController::class, 'empresas'])->name('empresas');
    //  SEGURIDAD: Rate limiting en cambio de estado (máx 30/minuto)
    Route::post('/empresas/{id}/estado', [AdminController::class, 'cambiarEstadoEmpresa'])->name('empresas.estado')->middleware('throttle:30,1');
    Route::get('/proyectos', [AdminController::class, 'proyectos'])->name('proyectos');
    Route::get('/proyectos/{id}/revisar', [AdminController::class, 'revisarProyecto'])->name('proyectos.revisar');
    //  SEGURIDAD: Rate limiting en cambio de estado (máx 20/minuto)
    Route::post('/proyectos/{id}/estado', [AdminController::class, 'cambiarEstadoProyecto'])->name('proyectos.estado')->middleware('throttle:20,1');
    //  SEGURIDAD: Rate limiting en asignación (máx 20/minuto)
    Route::post('/proyectos/{id}/asignar', [AdminController::class, 'asignarInstructor'])->name('proyectos.asignar')->middleware('throttle:20,1');

    //  SEGURIDAD: Rate limiting en exportaciones (máx 10/minuto por seguridad)
    Route::get('/exportar/proyectos', [ExportController::class, 'proyectos'])->name('exportar.proyectos')->middleware('throttle:10,1');
    Route::get('/exportar/usuarios', [ExportController::class, 'usuarios'])->name('exportar.usuarios')->middleware('throttle:10,1');
    Route::get('/exportar/empresas', [ExportController::class, 'empresas'])->name('exportar.empresas')->middleware('throttle:10,1');
    Route::get('/exportar/aprendices', [ExportController::class, 'aprendices'])->name('exportar.aprendices')->middleware('throttle:10,1');
    Route::get('/exportar/instructores', [ExportController::class, 'instructores'])->name('exportar.instructores')->middleware('throttle:10,1');

    Route::get('/historial', [AuditLogController::class, 'index'])->name('historial');
    Route::get('/mensajes-soporte', [AdminController::class, 'mensajesSoporte'])->name('mensajes.soporte');
    Route::post('/mensajes-soporte/{id}/responder', [AdminController::class, 'responderMensajeSoporte'])->name('mensajes.soporte.responder');

    Route::get('/backup', [BackupController::class, 'index'])->name('backup');
    Route::post('/backup/crear', [BackupController::class, 'crear'])->name('backup.crear');
    Route::get('/backup/exportar', [BackupController::class, 'exportar'])->name('backup.exportar');
    Route::post('/backup/importar', [BackupController::class, 'importar'])->name('backup.importar');
    Route::get('/backup/descargar/{nombre}', [BackupController::class, 'descargar'])->name('backup.descargar');
    Route::delete('/backup/eliminar/{nombre}', [BackupController::class, 'eliminar'])->name('backup.eliminar');
    // Gestión de pagos
    Route::get('/pagos', [App\Http\Controllers\AdminController::class, 'pagos'])->name('pagos');
    Route::post('/pagos/{id}/confirmar', [App\Http\Controllers\AdminController::class, 'confirmarPago'])->name('pagos.confirmar');
    Route::post('/pagos/{id}/rechazar', [App\Http\Controllers\AdminController::class, 'rechazarPago'])->name('pagos.rechazar');
});

Route::middleware(['auth.custom', 'rol:4'])->get('/api/admin/stats', [StatsController::class, 'dashboard'])->name('api.admin.stats');
Route::middleware(['auth.custom', 'rol:4'])->get('/api/admin/stats/programas', [StatsController::class, 'programas'])->name('api.admin.stats.programas');
Route::middleware(['auth.custom', 'rol:4'])->get('/api/admin/analytics', [StatsController::class, 'analytics'])->name('api.admin.analytics');

// Webhook Nequi (sin autenticación — Nequi llama este endpoint)
Route::post('/nequi/webhook', [App\Http\Controllers\NequiWebhookController::class, 'handle'])->name('nequi.webhook');

//  SEGURIDAD: Rate limiting en APIs de infinite scroll (60/min)
Route::middleware(['auth.custom', 'throttle:60,1'])->group(function () {
    Route::get('/api/infinite/proyectos', [InfiniteScrollController::class, 'proyectos'])
        ->name('api.infinite.proyectos');

    Route::get('/api/infinite/aprendices', [InfiniteScrollController::class, 'aprendices'])
        ->name('api.infinite.aprendices');

    // Para empresa (rol 3) y admin (rol 4)
    Route::middleware('rol:3,4')->get('/api/infinite/proyectos-empresa', [InfiniteScrollController::class, 'proyectosEmpresa'])
        ->name('api.infinite.proyectos-empresa');
});