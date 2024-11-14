@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header modificado --}}
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">Gestor de Tareas</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('projects.index') }}"
                       class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Regresar a Proyectos
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900" id="projectTitle">Cargando proyecto...</h2>
                <p class="mt-1 text-gray-500" id="projectDescription"></p>
            </div>
            <button onclick="showCreateTaskModal()"
                class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Tarea
            </button>
        </div>

        {{-- Tasks List --}}
        <div id="tasksList" class="space-y-4">
            {{-- El contenido se cargará dinámicamente via JavaScript --}}
        </div>

        {{-- Empty State Template --}}
        <template id="emptyState">
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No hay tareas aún</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Comienza creando una nueva tarea para este proyecto
                </p>
                <button onclick="showCreateTaskModal()"
                    class="mt-6 inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Crear primera tarea
                </button>
            </div>
        </template>

        {{-- Task Card Template --}}
        <template id="taskTemplate">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg mb-2 task-title"></h3>
                        <p class="text-gray-600 mb-2 task-description"></p>
                        <p class="text-sm text-gray-500 task-due-date"></p>
                    </div>
                    <span class="px-3 py-1 text-sm rounded task-status"></span>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="edit-task px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Editar
                    </button>
                    <button class="delete-task px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
            </div>
        </template>

        {{-- Modal para crear/editar tarea --}}
        <div id="createTaskModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Nueva Tarea</h3>
                    <button onclick="hideCreateTaskModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="createTaskForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" id="taskTitle" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea id="taskDescription"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha de vencimiento</label>
                        <input type="date" id="taskDueDate" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select id="taskStatus" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="pendiente">Pendiente</option>
                            <option value="en progreso">En Progreso</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="hideCreateTaskModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Crear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
const projectId = "{{ $projectId }}";

// Cargar datos del proyecto y sus tareas
async function loadProjectAndTasks() {
    try {
        const response = await axios.get(`/api/projects/${projectId}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });

        const project = response.data.data;
        document.getElementById('projectTitle').textContent = project.name;
        document.getElementById('projectDescription').textContent = project.description || 'Sin descripción';

        // Renderizar tareas
        renderTasks(project.tasks);
    } catch (error) {
        console.error('Error al cargar proyecto:', error);
    }
}

// Función para renderizar tareas
function renderTasks(tasks) {
    const tasksList = document.getElementById('tasksList');
    const taskTemplate = document.getElementById('taskTemplate');
    const emptyState = document.getElementById('emptyState');

    if (!tasks || tasks.length === 0) {
        tasksList.innerHTML = emptyState.innerHTML;
        return;
    }

    tasksList.innerHTML = '';
    tasks.forEach(task => {
        const taskCard = taskTemplate.content.cloneNode(true);
        const taskDiv = taskCard.querySelector('div');

        // Add data attribute for task ID
        taskDiv.dataset.taskId = task.id;

        taskCard.querySelector('.task-title').textContent = task.title;
        taskCard.querySelector('.task-description').textContent = task.description || 'Sin descripción';
        taskCard.querySelector('.task-due-date').textContent = `Vencimiento: ${formatDate(task.due_date)}`;

        const statusElement = taskCard.querySelector('.task-status');
        statusElement.textContent = task.status;
        statusElement.className = `px-3 py-1 text-sm rounded ${getStatusClass(task.status)}`;

        // Add event listeners with correct task reference
        taskCard.querySelector('.edit-task').addEventListener('click', () => showEditTaskModal(task));
        taskCard.querySelector('.delete-task').addEventListener('click', () => deleteTask(task.id));

        tasksList.appendChild(taskCard);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    return date.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getStatusClass(status) {
    switch(status) {
        case 'pendiente':
            return 'bg-yellow-100 text-yellow-800';
        case 'en progreso':
            return 'bg-blue-100 text-blue-800';
        case 'completada':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Modal functions
function showCreateTaskModal() {
    document.getElementById('createTaskModal').classList.remove('hidden');
}

function hideCreateTaskModal() {
    document.getElementById('createTaskModal').classList.add('hidden');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', loadProjectAndTasks);

const createTask = async (data) => {
    try {
        const token = localStorage.getItem('token');
        console.log('Enviando petición a:', `/api/projects/${projectId}/tasks`);
        console.log('Con datos:', data);

        const response = await axios.post(`/api/projects/${projectId}/tasks`, data, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        return response.data;
    } catch (error) {
        console.error('Error completo:', error);
        throw error;
    }
};

// Funciones para manejo del modal de edición
function showEditTaskModal(task) {
    const modal = document.getElementById('createTaskModal');
    const form = document.getElementById('createTaskForm');
    const title = modal.querySelector('h3');
    const submitButton = form.querySelector('button[type="submit"]');

    // Actualizar título y botón
    title.textContent = 'Editar Tarea';
    submitButton.textContent = 'Guardar Cambios';

    // Poblar el formulario
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskDescription').value = task.description || '';
    document.getElementById('taskDueDate').value = task.due_date;
    document.getElementById('taskStatus').value = task.status;

    // Cambiar el handler del formulario
    form.onsubmit = (e) => handleEditTask(e, task.id);

    modal.classList.remove('hidden');
}

// Handler para editar tarea
async function handleEditTask(e, taskId) {
    e.preventDefault();

    try {
        const response = await axios.put(
            `/api/projects/${projectId}/tasks/${taskId}`,
            {
                title: document.getElementById('taskTitle').value,
                description: document.getElementById('taskDescription').value,
                due_date: document.getElementById('taskDueDate').value,
                status: document.getElementById('taskStatus').value
            },
            {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            }
        );

        if (response.data.status === 'success') {
            hideCreateTaskModal();
            await loadProjectAndTasks();
        }
    } catch (error) {
        console.error('Error al actualizar la tarea:', error);
        alert('Error al actualizar la tarea: ' + (error.response?.data?.message || error.message));
    }
}

// Función para eliminar tarea
async function deleteTask(taskId) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
        return;
    }

    try {
        const response = await axios.delete(
            `/api/projects/${projectId}/tasks/${taskId}`,
            {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            }
        );

        if (response.data.status === 'success') {
            await loadProjectAndTasks();
        }
    } catch (error) {
        console.error('Error al eliminar la tarea:', error);
        alert('Error al eliminar la tarea: ' + (error.response?.data?.message || error.message));
    }
}

// Modificar la función renderTasks para incluir los botones de edición y eliminación
function renderTasks(tasks) {
    const tasksList = document.getElementById('tasksList');
    const taskTemplate = document.getElementById('taskTemplate');
    const emptyState = document.getElementById('emptyState');

    if (!tasks || tasks.length === 0) {
        tasksList.innerHTML = emptyState.innerHTML;
        return;
    }

    tasksList.innerHTML = '';
    tasks.forEach(task => {
        const taskCard = taskTemplate.content.cloneNode(true);

        taskCard.querySelector('.task-title').textContent = task.title;
        taskCard.querySelector('.task-description').textContent = task.description || 'Sin descripción';
        taskCard.querySelector('.task-due-date').textContent = `Vencimiento: ${formatDate(task.due_date)}`;

        const statusElement = taskCard.querySelector('.task-status');
        statusElement.textContent = task.status;
        statusElement.className = `px-3 py-1 text-sm rounded ${getStatusClass(task.status)}`;

        // Agregar event listeners para edición y eliminación
        taskCard.querySelector('.edit-task').onclick = () => showEditTaskModal(task);
        taskCard.querySelector('.delete-task').onclick = () => deleteTask(task.id);

        tasksList.appendChild(taskCard);
    });
}

// Función modificada showCreateTaskModal
function showCreateTaskModal() {
    const modal = document.getElementById('createTaskModal');
    const form = document.getElementById('createTaskForm');
    const title = modal.querySelector('h3');
    const submitButton = form.querySelector('button[type="submit"]');

    // Reset form state
    title.textContent = 'Nueva Tarea';
    submitButton.textContent = 'Crear';
    form.reset();

    // Clear previous handlers and set new one
    const oldHandler = form.onsubmit;
    if (oldHandler) {
        form.removeEventListener('submit', oldHandler);
    }
    form.onsubmit = createTaskHandler;

    modal.classList.remove('hidden');
}

// Handler para crear tarea
async function createTaskHandler(e) {
    e.preventDefault();

    try {
        const taskData = {
            title: document.getElementById('taskTitle').value,
            description: document.getElementById('taskDescription').value,
            due_date: document.getElementById('taskDueDate').value,
            status: document.getElementById('taskStatus').value
        };

        await createTask(taskData);
        hideCreateTaskModal();
        loadProjectAndTasks();
        this.reset();

    } catch (error) {
        console.error('Error al crear la tarea:', error);
        alert('Error al crear la tarea: ' + (error.response?.data?.message || error.message));
    }
}

</script>
@endpush
@endsection
