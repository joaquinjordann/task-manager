@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Mis Proyectos
            </h1>
            <button onclick="showCreateProjectModal()"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Proyecto
            </button>
        </div>

        {{-- Grid de Proyectos --}}
        <div id="projectsList" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Los proyectos se cargarán aquí dinámicamente --}}
        </div>

        {{-- Modal para crear proyecto --}}
        <div id="createProjectModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Crear Nuevo Proyecto</h3>
                    <button onclick="hideCreateProjectModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="createProjectForm" class="space-y-4">
                    <div>
                        <label for="projectName" class="block text-sm font-medium text-gray-700">Nombre del Proyecto</label>
                        <input type="text" id="projectName" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="projectDescription" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea id="projectDescription" name="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideCreateProjectModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                            Crear Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Editar Proyecto Modal -->
        <div id="editProjectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Editar Proyecto</h3>
                    <button onclick="hideEditProjectModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="editProjectForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre del Proyecto</label>
                        <input type="text" id="editProjectName" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea id="editProjectDescription"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="hideEditProjectModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showCreateProjectModal() {
    document.getElementById('createProjectModal').classList.remove('hidden');
}

function hideCreateProjectModal() {
    document.getElementById('createProjectModal').classList.add('hidden');
}

// Interceptor global para Axios
axios.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      // Asegurarnos de que se envían los headers correctos
      config.headers.Accept = 'application/json';
      config.headers['Content-Type'] = 'application/json';
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores de autenticación
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Token inválido o expirado
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Función modificada para cargar proyectos
async function loadProjects() {
    try {
        const projectsContainer = document.getElementById('projectsList');

        const response = await axios.get('/api/projects');

        if (!response.data.data || response.data.data.length === 0) {
            projectsContainer.classList.remove('grid', 'grid-cols-1', 'gap-6', 'sm:grid-cols-2', 'lg:grid-cols-3');
            projectsContainer.innerHTML = `
                <div class="w-full flex flex-col items-center justify-center min-h-[400px] bg-white rounded-lg shadow-lg">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-xl font-medium text-gray-900">No hay proyectos</h3>
                    <p class="mt-2 text-center text-sm text-gray-600 max-w-sm px-4">
                        Aún no has creado ningún proyecto. Comienza creando uno nuevo para organizar tus tareas.
                    </p>
                    <button onclick="showCreateProjectModal()"
                        class="mt-6 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear mi primer proyecto
                    </button>
                </div>
            `;
            return;
        }

        projectsContainer.classList.add('grid', 'grid-cols-1', 'gap-6', 'sm:grid-cols-2', 'lg:grid-cols-3');
        projectsContainer.innerHTML = response.data.data.map(project =>
            `<div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900">${project.name}</h3>
                    <p class="mt-1 text-sm text-gray-600">${project.description || 'Sin descripción'}</p>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            project.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                        }">
                            ${project.status}
                        </span>
                    </div>
                </div>
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex justify-between">
                        <a href="/projects/${project.id}"
                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            Ver detalles
                        </a>
                        <button onclick='showEditProjectModal(${JSON.stringify(project).replace(/'/g, "&apos;").replace(/"/g, "&quot;")})'
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            Editar
                        </button>
                        <button onclick="deleteProject(${project.id})"
                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>`
        ).join('');
    } catch (error) {
        console.error('Error completo:', error);
        const projectsContainer = document.getElementById('projectsList');
        projectsContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center min-h-[400px] bg-red-50 rounded-lg border border-red-200 p-8">
                <div class="rounded-full bg-red-100 p-4 mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-red-900 mb-2">Error al cargar los proyectos</h3>
                <p class="text-red-600 text-center mb-4">Por favor, intenta nuevamente. Si el problema persiste, cierra sesión y vuelve a iniciar.</p>
                <button onclick="reloadProjects()"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reintentar
                </button>
            </div>
        `;
    }
}

function reloadProjects() {
    loadProjects();
}

// Función auxiliar para manejar la eliminación de proyectos
async function deleteProject(projectId) {
    if (!confirm('¿Estás seguro de que deseas eliminar este proyecto?')) {
        return;
    }

    try {
        await axios.delete(`/api/projects/${projectId}`);
        loadProjects();
    } catch (error) {
        console.error('Error al eliminar el proyecto:', error);
        alert('Error al eliminar el proyecto. Por favor, intenta nuevamente.');
    }
}

// Crear proyecto
document.getElementById('createProjectForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        await axios.post('/api/projects', {
            name: document.getElementById('projectName').value,
            description: document.getElementById('projectDescription').value
        }, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        hideCreateProjectModal();
        loadProjects();

        // Limpiar el formulario
        document.getElementById('projectName').value = '';
        document.getElementById('projectDescription').value = '';

    } catch (error) {
        console.error('Error al crear el proyecto:', error);
        alert('Error al crear el proyecto. Por favor, intenta nuevamente.');
    }
});

// Variables y funciones para edición de proyectos
let currentProjectId = null;

function showEditProjectModal(project) {
    const modal = document.getElementById('editProjectModal');
    document.getElementById('editProjectName').value = project.name;
    document.getElementById('editProjectDescription').value = project.description || '';
    currentProjectId = project.id;
    modal.classList.remove('hidden');
}

function hideEditProjectModal() {
    document.getElementById('editProjectModal').classList.add('hidden');
    currentProjectId = null;
}

// Handler para editar proyecto
document.getElementById('editProjectForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
        const response = await axios.put(
            `/api/projects/${currentProjectId}`,
            {
                name: document.getElementById('editProjectName').value,
                description: document.getElementById('editProjectDescription').value
            },
            {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            }
        );

        if (response.data.status === 'success') {
            hideEditProjectModal();
            await loadProjects(); // Recargar la lista de proyectos
        }
    } catch (error) {
        console.error('Error al actualizar el proyecto:', error);
        alert('Error al actualizar el proyecto: ' + (error.response?.data?.message || error.message));
    }
});

// Modificar la función renderProjects para incluir el botón de editar
function renderProjects(projects) {
    const projectsList = document.getElementById('projectsList');

    if (!projects || projects.length === 0) {
        projectsList.innerHTML = `
            <div class="text-center p-8">
                <p class="text-gray-500">No hay proyectos aún</p>
            </div>
        `;
        return;
    }

    projectsList.innerHTML = projects.map(project => `
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-lg mb-2">${project.name}</h3>
                    <p class="text-gray-600">${project.description || 'Sin descripción'}</p>
                    <span class="inline-block mt-2 px-2 py-1 text-sm rounded ${
                        project.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    }">${project.status}</span>
                </div>
            </div>
            <div class="mt-4 flex justify-between">
                <div class="space-x-2">
                    <button onclick="showEditProjectModal(${JSON.stringify(project)})"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Editar
                    </button>
                    <button onclick="deleteProject(${project.id})"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Eliminar
                    </button>
                </div>
                <a href="/projects/${project.id}"
                    class="text-indigo-600 hover:text-indigo-900">
                    Ver detalles
                </a>
            </div>
        </div>
    `).join('');
}

// Cargar proyectos al iniciar
document.addEventListener('DOMContentLoaded', loadProjects);
</script>
@endpush
@endsection
