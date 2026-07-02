<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Proyecto extends Model
{
    use SoftDeletes;
    protected $table = 'proyectos';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'empresa_nit',
        'instructor_usuario_id',
        'titulo',
        'descripcion',
        'categoria',
        'estado',
        'calidad_aprobada',
        'imagen_url',
        'url_estructura',
        'requisitos_especificos',
        'habilidades_requeridas',
        'duracion_estimada_dias',
        'fecha_publicacion',
        'numero_postulantes',
        'ubicacion',
        'latitud',
        'longitud',
        'oferta',
        'oferta_otro',
        'tipo_publicacion',
        'fecha_inicio_plan',
        'fecha_fin_plan',
        'pago_id',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'deleted_at' => 'datetime',
        'calidad_aprobada' => 'boolean',
        'fecha_inicio_plan' => 'datetime',
        'fecha_fin_plan' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::forceDeleting(function (Proyecto $proyecto) {
            if ($proyecto->url_estructura) {
                Storage::disk('public')->delete($proyecto->url_estructura);
            }
        });
    }

    // ── RELACIONES ──
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_nit', 'nit');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_usuario_id', 'usuario_id');
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class, 'proyecto_id', 'id');
    }

    public function etapas(): HasMany
    {
        return $this->hasMany(Etapa::class, 'proyecto_id', 'id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class, 'proyecto_id', 'id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(PagoPublicacion::class, 'pago_id', 'id');
    }

    // ── SCOPES ──
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereIn('estado', ['aprobado', 'en_progreso']);
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeRechazados(Builder $query): Builder
    {
        return $query->where('estado', 'rechazado');
    }

    public function scopeCompletados(Builder $query): Builder
    {
        return $query->where('estado', 'completado');
    }

    public function scopeFinalizados(Builder $query): Builder
    {
        return $query->where('estado', 'cerrado');
    }

    public function scopeDondeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', 'aprobado'); // alias para semántica vieja
    }

    public function scopePorEmpresa(Builder $query, $empNit): Builder
    {
        return $query->where('empresa_nit', $empNit);
    }

    public function scopePorCategoria(Builder $query, $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeBusqueda(Builder $query, $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('titulo', 'like', "%{$termino}%")
                ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(tipo_publicacion, 'patrocinado', 'destacado', 'gratuito')")
            ->orderByDesc('fecha_publicacion');
    }

    public function scopePatrocinados(Builder $query): Builder
    {
        return $query->where('tipo_publicacion', 'patrocinado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin_plan')
                    ->orWhere('fecha_fin_plan', '>=', now());
            });
    }

    public function scopeVisibles(Builder $query): Builder
    {
        return $query->whereIn('estado', ['aprobado', 'en_progreso'])
            ->orderByRaw("FIELD(tipo_publicacion, 'patrocinado', 'destacado', 'gratuito')")
            ->orderByDesc('fecha_publicacion');
    }

    public function esPatrocinadoActivo(): bool
    {
        return $this->tipo_publicacion === 'patrocinado'
            && ($this->fecha_fin_plan === null || $this->fecha_fin_plan >= now());
    }

    public function esDestacadoActivo(): bool
    {
        return $this->tipo_publicacion === 'destacado'
            && ($this->fecha_fin_plan === null || $this->fecha_fin_plan >= now());
    }

    public function getTipoPublicacionBadgeAttribute(): ?array
    {
        if ($this->esPatrocinadoActivo()) {
            return ['label' => 'Patrocinado', 'icon' => 'fa-crown', 'bg' => 'linear-gradient(135deg,#f59e0b,#d97706)'];
        }
        if ($this->esDestacadoActivo()) {
            return ['label' => 'Destacado', 'icon' => 'fa-star', 'bg' => 'linear-gradient(135deg,#3b82f6,#2563eb)'];
        }
        return null;
    }


    // ── MÉTODOS ──

    public function isActivo(): bool
    {
        return in_array($this->estado, ['aprobado', 'en_progreso']);
    }

    public function isPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function isRechazado(): bool
    {
        return $this->estado === 'rechazado';
    }

    public function isCompletado(): bool
    {
        return $this->estado === 'completado';
    }

    /**
     * Obtener postulaciones aprobadas
     */
    public function postulacionesAprobadas()
    {
        return $this->postulaciones()->where('estado', 'aceptada');
    }

    /**
     * Obtener postulaciones pendientes
     */
    public function postulacionesPendientes()
    {
        return $this->postulaciones()->where('estado', 'pendiente');
    }

    /**
     * Obtener postulaciones rechazadas
     */
    public function postulacionesRechazadas()
    {
        return $this->postulaciones()->where('estado', 'rechazada');
    }

    /**
     * Contar postulantes aprobados
     */
    public function countPostulantesAprobados(): int
    {
        return $this->postulacionesAprobadas()->count();
    }

    /**
     * Contar postulantes pendientes
     */
    public function countPostulantesPendientes(): int
    {
        return $this->postulacionesPendientes()->count();
    }

    /**
     * Contar todas las postulaciones
     */
    public function countPostulaciones(): int
    {
        return $this->postulaciones()->count();
    }

    /**
     * Obtener atributo contador de postulantes
     */
    public function getPostulantesCountAttribute(): int
    {
        return $this->numero_postulantes ?: $this->countPostulaciones();
    }

    /**
     * Contar etapas totales
     */
    public function countEtapas(): int
    {
        return $this->etapas()->count();
    }

    /**
     * Obtener etapas ordenadas
     */
    public function etapasOrdenadas()
    {
        return $this->etapas()->orderBy('orden')->get();
    }

    /**
     * Verificar si está vencido (Usaba fecha_finalizacion, ahora estimamos por duracion o estado cerrado)
     */
    public function isVencido(): bool
    {
        if ($this->estado == 'cerrado') {
            return true;
        }
        if (! $this->fecha_publicacion || ! $this->duracion_estimada_dias) {
            return false;
        }

        $fechaFinEstimada = Carbon::parse($this->fecha_publicacion)->addDays($this->duracion_estimada_dias);

        return now()->isAfter($fechaFinEstimada);
    }

    /**
     * Accesor para fecha de finalización calculada automáticamente
     */
    public function getFechaFinalizacionAttribute(): ?string
    {
        if (! $this->fecha_publicacion || ! $this->duracion_estimada_dias) {
            return null;
        }

        return Carbon::parse($this->fecha_publicacion)
            ->addDays($this->duracion_estimada_dias)
            ->format('Y-m-d');
    }

    /**
     * Obtener el nombre de la empresa
     */
    public function getEmpresaNombreAttribute(): string
    {
        return $this->empresa?->nombre ?? 'Empresa no asignada';
    }

    /**
     * Accesor para la URL de la imagen del proyecto.
     * Lee del campo 'imagen_url' en la BD y devuelve una URL pública válida.
     */
    public function getImagenUrlAttribute(): string
    {
        $value = $this->attributes['imagen_url'] ?? null;

        if (empty($value)) {
            return asset('assets/default-project.jpg');
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }

    public function getEstructuraUrlAttribute(): string
    {
        if (empty($this->url_estructura)) {
            return '';
        }
        return asset('storage/' . $this->url_estructura);
    }

    public function hasEstructura(): bool
    {
        return !empty($this->url_estructura);
    }

    public function calidadProyecto(): array
    {
        $empresa = $this->empresa;
        $empresaActiva = $empresa && $empresa->activo == 1;

        $titulo = trim($this->titulo ?? '');
        $descripcion = trim($this->descripcion ?? '');
        $requisitos = trim($this->requisitos_especificos ?? '');
        $habilidades = trim($this->habilidades_requeridas ?? '');

        $tituloLongitud = strlen($titulo);
        $descripcionLongitud = strlen($descripcion);
        $requisitosLongitud = strlen($requisitos);
        $habilidadesLongitud = strlen($habilidades);

        $tituloPalabras = str_word_count($titulo);
        $descripcionPalabras = str_word_count($descripcion);
        $requisitosPalabras = str_word_count($requisitos);
        $habilidadesPalabras = str_word_count($habilidades);

        $tituloValido = $tituloLongitud >= 10 && $tituloLongitud <= 200;
        $descripcionValida = $descripcionLongitud >= 80 && $descripcionLongitud <= 5000 && $descripcionPalabras >= 20;
        $requisitosValidos = $requisitosLongitud >= 20 && $requisitosPalabras >= 3;
        $habilidadesValidas = $habilidadesLongitud >= 15 && $habilidadesPalabras >= 2;

        $tieneCoherencia = $requisitos && $habilidades
            && $requisitosPalabras >= 3 && $habilidadesPalabras >= 2;

        $duracionValida = $this->duracion_estimada_dias && $this->duracion_estimada_dias >= 7 && $this->duracion_estimada_dias <= 365;

        $categoriaValida = ! empty(trim($this->categoria ?? ''));

        $categoriasValidas = array_keys(config('programas'));
        $categoriaEnLista = in_array($this->categoria ?? '', $categoriasValidas);

        $detalles = [
            'empresa_activa' => [
                'ok' => $empresaActiva,
                'peso' => 20,
                'descripcion' => $empresa ? 'Empresa '.($empresaActiva ? 'verificada y activa' : 'inactiva') : 'Empresa no encontrada',
            ],
            'titulo' => [
                'ok' => $tituloValido,
                'peso' => 15,
                'descripcion' => $tituloLongitud < 10 ? 'Título muy corto (mín. 10 caracteres)' : ($tituloLongitud > 200 ? 'Título muy largo (máx. 200 caracteres)' : 'Título descriptivo'),
                'valor' => $tituloLongitud,
                'palabras' => $tituloPalabras,
            ],
            'descripcion' => [
                'ok' => $descripcionValida,
                'peso' => 25,
                'descripcion' => $descripcionPalabras < 20 ? 'Descripción muy corta (mín. 20 palabras)' : ($descripcionLongitud > 5000 ? 'Descripción muy larga' : 'Describe el objetivo del proyecto'),
                'valor' => $descripcionLongitud,
                'palabras' => $descripcionPalabras,
                'detalles' => [
                    'tiene_objetivo' => preg_match('/(objetivo|meta|fin|proposito|desarrollar|crear|implementar)/i', $descripcion) === 1,
                    'tiene_alcance' => preg_match('/(alcance|entregar|resultado|beneficio|impacto)/i', $descripcion) === 1,
                ],
            ],
            'requisitos' => [
                'ok' => $requisitosValidos,
                'peso' => 15,
                'descripcion' => $requisitosPalabras < 3 ? 'Requisitos muy cortos (mín. 3 palabras)' : 'Requisitos bien definidos',
                'valor' => $requisitosLongitud,
                'palabras' => $requisitosPalabras,
            ],
            'habilidades' => [
                'ok' => $habilidadesValidas,
                'peso' => 15,
                'descripcion' => $habilidadesPalabras < 2 ? 'Habilidades muy cortas (mín. 2 palabras)' : 'Habilidades definidas',
                'valor' => $habilidadesLongitud,
                'palabras' => $habilidadesPalabras,
            ],
            'coherencia' => [
                'ok' => $tieneCoherencia,
                'peso' => 5,
                'descripcion' => $tieneCoherencia ? 'Coherente' : 'Falta coherencia entre requisitos y habilidades',
            ],
            'categoria' => [
                'ok' => $categoriaValida,
                'peso' => 3,
                'descripcion' => $categoriaValida ? 'Categoría definida' : 'Categoría no definida',
            ],
            'duracion' => [
                'ok' => $duracionValida,
                'peso' => 2,
                'descripcion' => ! $this->duracion_estimada_dias ? 'Duración no especificada' : ($duracionValida ? "Duración: {$this->duracion_estimada_dias} días" : 'Duración fuera del rango'),
                'valor' => $this->duracion_estimada_dias,
            ],
            'ubicacion' => [
                'ok' => true,
                'peso' => 0,
                'descripcion' => ! is_null($this->latitud) && ! is_null($this->longitud) && $this->latitud != 0 ? 'Coordenadas definidas' : 'Ubicación no requerida',
                'opcional' => true,
            ],
            'imagen' => [
                'ok' => true,
                'peso' => 0,
                'descripcion' => ! empty($this->imagen_url) ? 'Imagen de identidad cargada' : 'Imagen no requerida',
                'opcional' => true,
            ],
        ];

        $puntosObtenidos = 0;
        $puntosTotales = 0;
        foreach ($detalles as $item) {
            $puntosTotales += $item['peso'];
            if ($item['ok']) {
                $puntosObtenidos += $item['peso'];
            }
        }

        $porcentaje = $puntosTotales > 0 ? ($puntosObtenidos / $puntosTotales) * 100 : 0;

        $fallos = array_filter($detalles, fn ($item) => ! $item['ok']);
        $warnings = [];
        $erroresCriticos = [];

        if ($empresa && $empresa->activo != 1) {
            $erroresCriticos[] = 'La empresa no está activa';
        }
        if (! $descripcionValida) {
            if ($descripcionPalabras < 20) {
                $erroresCriticos[] = 'La descripción debe explicar el objetivo y alcance del proyecto';
            }
        }
        if (! $tituloValido) {
            $erroresCriticos[] = 'El título debe ser más descriptivo';
        }
        if (! $requisitosValidos) {
            $warnings[] = 'Define mejor los requisitos técnicos';
        }
        if (! $habilidadesValidas) {
            $warnings[] = 'Especifica las habilidades requeridas';
        }

        return [
            'porcentaje' => round($porcentaje),
            'puntos_obtenidos' => $puntosObtenidos,
            'puntos_totales' => $puntosTotales,
            'detalles' => $detalles,
            'es_apto' => $porcentaje >= 75,
            'fallos' => array_keys($fallos),
            'warnings' => $warnings,
            'errores_criticos' => $erroresCriticos,
            'puede_publicarse' => $porcentaje >= 75 && $empresaActiva && $descripcionValida,
            'razon_rechazo' => ! $empresaActiva ? 'Empresa inactiva' : (! $descripcionValida ? 'La descripción no explica bien el proyecto' : ($porcentaje < 75 ? 'No cumple los requisitos mínimos de calidad' : null)),
        ];
    }
}
