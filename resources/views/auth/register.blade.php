@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="flex flex-col items-center">
            <svg class="w-20 h-20 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Crea tu cuenta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                O
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    inicia sesión si ya tienes una
                </a>
            </p>
        </div>

        <form id="registerForm" class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg">
            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nombre completo
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" id="name" name="name" required
                            class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Juan Pérez">
                        <p class="mt-1 hidden text-sm text-red-600" id="nameError"></p>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Correo electrónico
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" required
                            class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="tu@email.com">
                        <p class="mt-1 hidden text-sm text-red-600" id="emailError"></p>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Contraseña
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="••••••••">
                        <p class="mt-1 text-sm text-gray-500" id="passwordRequirements">
                            La contraseña debe contener:
                            <span id="minLength" class="block text-red-500">• Mínimo 8 caracteres</span>
                            <span id="hasLower" class="block text-red-500">• Una letra minúscula</span>
                            <span id="hasUpper" class="block text-red-500">• Una letra mayúscula</span>
                            <span id="hasNumber" class="block text-red-500">• Un número</span>
                            <span id="hasSpecial" class="block text-red-500">• Un carácter especial (@$!%*#?&)</span>
                        </p>
                        <p class="mt-1 hidden text-sm text-red-600" id="passwordError"></p>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="terms" name="terms" required
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-900">
                        Acepto los <a href="#" class="text-indigo-600 hover:text-indigo-500">términos y condiciones</a>
                    </label>
                </div>

                <button type="submit" id="submitButton"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </span>
                    Crear cuenta
                </button>
            </div>

            <div id="errorMessage" class="hidden mt-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const submitButton = document.getElementById('submitButton');

    // Función para validar la contraseña en tiempo real
    function validatePassword(password) {
        const requirements = {
            minLength: password.length >= 8,
            hasLower: /[a-z]/.test(password),
            hasUpper: /[A-Z]/.test(password),
            hasNumber: /[0-9]/.test(password),
            hasSpecial: /[@$!%*#?&]/.test(password)
        };

        // Actualizar indicadores visuales
        document.getElementById('minLength').className =
            `block ${requirements.minLength ? 'text-green-500' : 'text-red-500'}`;
        document.getElementById('hasLower').className =
            `block ${requirements.hasLower ? 'text-green-500' : 'text-red-500'}`;
        document.getElementById('hasUpper').className =
            `block ${requirements.hasUpper ? 'text-green-500' : 'text-red-500'}`;
        document.getElementById('hasNumber').className =
            `block ${requirements.hasNumber ? 'text-green-500' : 'text-red-500'}`;
        document.getElementById('hasSpecial').className =
            `block ${requirements.hasSpecial ? 'text-green-500' : 'text-red-500'}`;

        return Object.values(requirements).every(Boolean);
    }

    // Validar contraseña mientras el usuario escribe
    passwordInput.addEventListener('input', function() {
        const isValid = validatePassword(this.value);
        submitButton.disabled = !isValid;
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const errorMessage = document.getElementById('errorMessage');
        const submitButton = this.querySelector('button[type="submit"]');

        if (!document.getElementById('terms').checked) {
            errorMessage.textContent = 'Debes aceptar los términos y condiciones para continuar.';
            errorMessage.classList.remove('hidden');
            return;
        }

        // Validar contraseña antes de enviar
        if (!validatePassword(passwordInput.value)) {
            errorMessage.textContent = 'La contraseña no cumple con todos los requisitos.';
            errorMessage.classList.remove('hidden');
            return;
        }

        try {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2">Creando cuenta...</span>
            `;

            const response = await axios.post('/api/register', {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: passwordInput.value
            }, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'  // Añadido este header
                }
            });

            if (response.data.status === 'success') {
                // Mostrar mensaje de éxito
                errorMessage.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-600');
                errorMessage.classList.add('bg-green-50', 'border-green-200', 'text-green-600');
                errorMessage.textContent = '¡Cuenta creada exitosamente! Redirigiendo al login...';

                // Redirigir después de un breve delay
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            }
        } catch (error) {
            // Restaurar el botón
            submitButton.disabled = false;
            submitButton.innerHTML = `
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </span>
                Crear cuenta
            `;

            console.error('Error completo:', error);

            // Mostrar error específico o general
            const errorData = error.response?.data;
            if (errorData?.errors) {
                // Limpiar errores previos
                document.querySelectorAll('.text-red-600').forEach(el => el.classList.add('hidden'));

                // Mostrar nuevos errores
                Object.entries(errorData.errors).forEach(([field, messages]) => {
                    const errorElement = document.getElementById(`${field}Error`);
                    if (errorElement) {
                        errorElement.textContent = messages[0];
                        errorElement.classList.remove('hidden');
                    }
                });

                errorMessage.textContent = 'Por favor, corrige los errores señalados.';
            } else {
                errorMessage.textContent = errorData?.message || 'Error al crear la cuenta. Por favor, intenta nuevamente.';
            }

            errorMessage.classList.remove('hidden');
            errorMessage.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
        }
    });

    // Limpiar errores cuando el usuario comienza a escribir
    const inputs = ['name', 'email', 'password'];
    inputs.forEach(field => {
        const input = document.getElementById(field);
        input.addEventListener('input', function() {
            const errorElement = document.getElementById(`${field}Error`);
            if (errorElement) {
                errorElement.classList.add('hidden');
            }
            this.classList.remove('border-red-500');
            this.classList.remove('focus:border-red-500');
            this.classList.remove('focus:ring-red-500');
            errorMessage.classList.add('hidden');
        });
    });
});
</script>
@endpush
@endsection
