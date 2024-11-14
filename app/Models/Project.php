<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status',
        'priority',
        'deadline'
    ];

    protected $casts = [
        'deadline' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relaciones optimizadas
    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('due_date', 'asc');
    }

    // Scopes útiles
    public function scopeactive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Métodos de utilidad
    public function isOverdue()
    {
        return $this->deadline && now()->greaterThan($this->deadline);
    }

    public function getCompletionPercentage()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return 0;

        $completedTasks = $this->tasks()->where('status', 'completada')->count();
        return round(($completedTasks / $totalTasks) * 100);
    }

    // Método para obtener proyectos con caché simplificado
    public static function getCachedForUser($userId, $perPage = 10)
    {
        $cacheKey = "user_projects_{$userId}_page_" . request('page', 1);

        return Cache::remember($cacheKey, 3600, function () use ($userId, $perPage) {
            return static::forUser($userId)
                ->with(['tasks' => function ($query) {
                    $query->select('id', 'project_id', 'status', 'due_date');
                }])
                ->latest()
                ->paginate($perPage);
        });
    }

    // Cache events
    public static function boot()
    {
        parent::boot();

        static::created(function ($project) {
            Cache::forget("user_projects_{$project->user_id}_page_1");
        });

        static::updated(function ($project) {
            Cache::forget("user_projects_{$project->user_id}_page_1");
        });

        static::deleted(function ($project) {
            Cache::forget("user_projects_{$project->user_id}_page_1");
        });
    }
}
?>
