@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Nueva Tarea
                    </h1>
                    <a href="/projects/{{ $projectId }}" class="text-indigo-600 hover:text-indigo-700">
                        Volver al Proyecto
                    </a>
                </div>

                <form id="createTaskForm" class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Título
                        </label>
                        <input type="text" id="title" name="title" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Ingresa el título de la tarea">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Descripción
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Describe la tarea (opcional)"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">
                                Fecha de Vencimiento
                            </label>
                            <input type="date" id="due_date" name="due_date" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Estado
                            </label>
                            <select id="status" name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pendiente">Pendiente</option>
                                <option value="en progreso">En Progreso</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="/projects/{{ $projectId }}"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Crear Tarea
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const projectId = "{{ $projectId }}";

// Configurar fecha mínima como hoy
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('due_date').min = today;
});

// Manejar envío del formulario
document.getElementById('createTaskForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
        const response = await axios.post(
            `/api/projects/${projectId}/tasks`,
            {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                due_date: document.getElementById('due_date').value,
                status: document.getElementById('status').value
            },
            {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            }
        );

        if (response.data.status === 'success') {
            window.location.href = `/projects/${projectId}`;
        }
    } catch (error) {
        console.error('Error al crear la tarea:', error);
        alert('Error al crear la tarea. Por favor, verifica los datos ingresados.');
    }
});
</script>
@endpush
@endsection
