<?php
namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Rules\ValidWorkingDay;
use App\Rules\MaxTasksPerDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /* Obtener todas las tareas de un proyecto */
    public function index(Request $request, Project $project)
    {
        try {
            if ($project->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $query = $project->tasks();

            // Filtros
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtro por fecha
            if ($request->has('due_from')) {
                $query->whereDate('due_date', '>=', $request->due_from);
            }
            if ($request->has('due_to')) {
                $query->whereDate('due_date', '<=', $request->due_to);
            }

            // Ordenamiento
            $sortField = $request->sort_by ?? 'due_date';
            $sortOrder = $request->sort_order ?? 'asc';
            $allowedSortFields = ['title', 'due_date', 'status', 'created_at'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortOrder);
            }

            // Paginación
            $perPage = $request->per_page ?? 10;
            $tasks = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $tasks,
                'meta' => [
                    'total' => $tasks->total(),
                    'per_page' => $tasks->perPage(),
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'from' => $tasks->firstItem(),
                    'to' => $tasks->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las tareas'
            ], 500);
        }
    }

    /* Actualizar una tarea existente */
    public function store(Request $request, Project $project)
    {
        try {
            // Verificar autorización
            if ($project->user_id !== Auth::id()) {
                Log::warning('Intento de creación no autorizada de tarea', [
                    'user_id' => Auth::id(),
                    'project_id' => $project->id
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado para crear tareas en este proyecto'
                ], 403);
            }

            // Validación
            $validator = Validator::make($request->all(), [
                'due_date' => [
                    'required',
                    'date',
                    'after_or_equal:today',
                    new ValidWorkingDay,
                    new MaxTasksPerDay($project->id, 5)
                ],
                [
                    'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a hoy',
                ],
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    'min:3'
                ],
                'description' => [
                    'nullable',
                    'string',
                    'max:1000'
                ],
                'status' => [
                    'required',
                    'string',
                    'in:pendiente,en progreso,completada'
                ],
                'priority' => [
                    'nullable',
                    'string',
                    'in:baja,media,alta'
                ],
                'estimated_hours' => [
                    'nullable',
                    'numeric',
                    'min:0.5',
                    'max:100'
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Crear la tarea con los datos validados
            $taskData = array_merge($validator->validated(), [
                'project_id' => $project->id,
                'status' => $request->status ?? 'pendiente',
                'priority' => $request->priority ?? 'media',
                'due_date' => Carbon::parse($request->due_date)->format('Y-m-d')
            ]);

            $task = Task::create($taskData);

            // Registrar la creación exitosa
            Log::info('Tarea creada exitosamente', [
                'task_id' => $task->id,
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'priority' => $task->priority
            ]);

            // Cargar las relaciones necesarias
            $task->load('project');

            return response()->json([
                'status' => 'success',
                'message' => 'Tarea creada exitosamente',
                'data' => $task
            ], 201);

        } catch (\Exception $e) {
            // Registrar el error
            Log::error('Error al crear tarea', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['password'])
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la tarea: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(Project $project, Task $task)
    {
        if ($project->user_id !== Auth::id() || $task->project_id !== $project->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        if ($project->user_id !== Auth::id() || $task->project_id !== $project->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => 'nullable|string',
            'due_date' => [
                'required',
                'date',
                'after_or_equal:today',
                new ValidWorkingDay,
                new MaxTasksPerDay($project->id, 5)
            ],
            [
                'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a hoy',
            ],
            'status' => 'required|in:pendiente,en progreso,completada'
        ], [
            'title.required' => 'El título es obligatorio',
            'title.min' => 'El título debe tener al menos 3 caracteres',
            'title.max' => 'El título no puede tener más de 255 caracteres',
            'due_date.required' => 'La fecha de vencimiento es obligatoria',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a hoy',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser pendiente, en progreso o completada'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $task->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    /* Eliminar una tarea */
    public function destroy(Project $project, Task $task)
    {
        try {
            if ($project->user_id !== Auth::id() || $task->project_id !== $project->id) {
                Log::warning('Intento de eliminación no autorizada de tarea', [
                    'user_id' => Auth::id(),
                    'project_id' => $project->id,
                    'task_id' => $task->id
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado para eliminar esta tarea'
                ], 403);
            }

            $task->delete();

            Log::info('Tarea eliminada exitosamente', [
                'task_id' => $task->id,
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tarea eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar tarea', [
                'task_id' => $task->id,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la tarea'
            ], 500);
        }
    }
}
?>
