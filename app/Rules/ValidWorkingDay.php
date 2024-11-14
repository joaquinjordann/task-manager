<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;
use App\Models\Task;

class ValidWorkingDay implements Rule
{
    public function passes($attribute, $value)
    {
        $date = Carbon::parse($value);

        // Verificar si es fin de semana
        if ($date->isWeekend()) {
            return false;
        }

        // Lista de días festivos
        $holidays = [
            '2024-01-01', // Año Nuevo
            '2024-12-25', // Navidad
        ];

        return !in_array($date->format('Y-m-d'), $holidays);
    }

    public function message()
    {
        return 'La fecha debe ser un día laborable (no festivo ni fin de semana).';
    }
}

class MaxTasksPerDay implements Rule
{
    protected $projectId;
    protected $maxTasks;

    public function __construct($projectId, $maxTasks = 5)
    {
        $this->projectId = $projectId;
        $this->maxTasks = $maxTasks;
    }

    public function passes($attribute, $value)
    {
        $date = Carbon::parse($value);

        $tasksCount = Task::where('project_id', $this->projectId)
            ->whereDate('due_date', $date)
            ->count();

        return $tasksCount < $this->maxTasks;
    }

    public function message()
    {
        return "No se pueden programar más de {$this->maxTasks} tareas para el mismo día.";
    }
}
?>
