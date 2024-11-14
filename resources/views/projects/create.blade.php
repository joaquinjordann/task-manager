@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Nuevo Proyecto
                    </h1>
                    <a href="/projects" class="text-indigo-600 hover:text-indigo-700">
                        Volver a Proyectos
                    </a>
                </div>

                <form id="createProjectForm" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nombre del Proyecto
                        </label>
                        <input type="text" id="name" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Ingresa el nombre del proyecto">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Descripción
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Describe el proyecto (opcional)"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="/projects"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Crear Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Manejar envío del formulario
document.getElementById('createProjectForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
        const response = await axios.post(
            '/api/projects',
            {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value
            },
            {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            }
        );

        if (response.data.status === 'success') {
            window.location.href = '/projects';
        }
    } catch (error) {
        console.error('Error al crear el proyecto:', error);
        alert('Error al crear el proyecto. Por favor, verifica los datos ingresados.');
    }
});
</script>
@endpush
@endsection
