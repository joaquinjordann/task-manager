<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Tareas</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
    <script src="{{ asset('js/services/auth.js') }}" type="module"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="{{ url('/') }}" class="font-bold text-xl text-gray-800">
                    Gestor de Tareas
                </a>

                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/projects') }}"
                        class="text-gray-700 hover:text-gray-900 transition">
                            Proyectos
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                Cerrar Sesión
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                            Iniciar Sesión
                        </a>
                        <a href="{{ route('register') }}"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                            Registrarse
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="max-w-6xl mx-auto px-4">
            @yield('content')
        </div>
    </main>

    <script>
        // Configuración global de Axios
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.headers.common['Content-Type'] = 'application/json';
    </script>
    @stack('scripts')
</body>
</html>
