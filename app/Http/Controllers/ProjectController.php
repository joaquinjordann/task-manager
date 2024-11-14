<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
        try {
            Log::info('Iniciando obtención de proyectos', [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $projects = Project::where('user_id', $user->id)
                ->with(['tasks' => function ($query) {
                    $query->select('id', 'project_id', 'title', 'status', 'due_date');
                }])
                ->latest()
                ->paginate($request->per_page ?? 10);

            Log::info('Proyectos obtenidos exitosamente', [
                'count' => $projects->count(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'data' => ProjectResource::collection($projects),
                'meta' => [
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en obtención de proyectos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id() ?? 'no_auth'
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los proyectos',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:1000']
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $user->id,
                'status' => 'active',
                'priority' => $request->priority ?? 'media',
                'deadline' => $request->deadline
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Proyecto creado exitosamente',
                'data' => new ProjectResource($project)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear proyecto', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el proyecto',
                'debug' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function show(Project $project)
    {
        try {
            if ($project->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => new ProjectResource($project->load([
                    'tasks' => function ($query) {
                        $query->orderBy('due_date', 'asc');
                    }
                ]))
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener proyecto', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el proyecto'
            ], 500);
        }
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:1000']
        ]);

        try {
            if ($project->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado'
                ], 403);
            }

            DB::beginTransaction();

            $project->update($request->only(['name', 'description']));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Proyecto actualizado exitosamente',
                'data' => new ProjectResource($project)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar proyecto', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el proyecto'
            ], 500);
        }
    }

    public function destroy(Project $project)
    {
        try {
            if ($project->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado'
                ], 403);
            }

            DB::beginTransaction();

            $project->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Proyecto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar proyecto', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el proyecto'
            ], 500);
        }
    }
}
?>
