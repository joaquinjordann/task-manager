<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;
use App\Models\Task;

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
