<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario y obtener token
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->token = $response->json('authorization.token');
    }

    public function test_user_can_create_project()
    {
        $projectData = [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'priority' => 'alta',
            'deadline' => Carbon::now()->addDays(30)->format('Y-m-d')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Proyecto creado exitosamente'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'priority',
                        'deadline',
                        'created_at'
                    ]
                ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_cannot_create_project_with_invalid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/projects', [
            'name' => '', // Nombre vacío
            'priority' => 'invalid_priority' // Prioridad inválida
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'priority']);
    }

    public function test_user_can_list_own_projects()
    {
        // Crear varios proyectos para el usuario
        Project::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        // Crear proyectos de otro usuario
        Project::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/projects');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success'
                ])
                ->assertJsonCount(3, 'data');
    }

    public function test_user_can_update_own_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/projects/{$project->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Proyecto actualizado exitosamente'
                ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
            'description' => 'Updated Description'
        ]);
    }

    public function test_user_can_delete_own_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Proyecto eliminado exitosamente'
                ]);

        $this->assertSoftDeleted('projects', [
            'id' => $project->id
        ]);
    }
}

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $project;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $this->token = $response->json('authorization.token');
    }

    public function test_user_can_create_task()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'due_date' => now()->addDays(1)->format('Y-m-d'),
            'status' => 'pendiente',
            'priority' => 'alta',
            'estimated_hours' => 5
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/projects/{$this->project->id}/tasks", $taskData);

        $response->assertStatus(201)
                ->assertJson([
                    'status' => 'success'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'due_date',
                        'status',
                        'priority',
                        'estimated_hours'
                    ]
                ]);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $this->project->id,
            'title' => 'Test Task'
        ]);
    }

    public function test_user_cannot_create_task_on_weekend()
    {
        $weekendDate = Carbon::now()->next('Saturday')->format('Y-m-d');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/projects/{$this->project->id}/tasks", [
            'title' => 'Weekend Task',
            'description' => 'Test Description',
            'due_date' => $weekendDate,
            'status' => 'pendiente'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['due_date']);
    }

    public function test_user_cannot_exceed_daily_task_limit()
    {
        // Crear el máximo de tareas permitidas para un día
        $dueDate = now()->addDays(1)->format('Y-m-d');
        Task::factory()->count(5)->create([
            'project_id' => $this->project->id,
            'due_date' => $dueDate
        ]);

        // Intentar crear una tarea adicional
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/projects/{$this->project->id}/tasks", [
            'title' => 'Excess Task',
            'due_date' => $dueDate,
            'status' => 'pendiente'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['due_date']);
    }
}

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Usuario creado exitosamente. Por favor, inicia sesión.'
                ])
                ->assertJsonStructure([
                    'user' => [
                        'name',
                        'email'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_cannot_register_with_existing_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123!'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_password_complexity_requirements()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'simple' // Contraseña que no cumple los requisitos
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_token_refresh()
    {
        // Login inicial
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('authorization.token');

        // Intentar refrescar el token
        $refreshResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/refresh');

        $refreshResponse->assertStatus(200)
                       ->assertJsonStructure([
                           'status',
                           'authorization' => [
                               'token',
                               'type',
                               'expires_in'
                           ]
                       ]);
    }

    public function test_logout()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Sesión cerrada exitosamente'
                ]);

        // Verificar que el token ya no es válido
        $secondResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/projects');

        $secondResponse->assertStatus(401);
    }
}
?>
