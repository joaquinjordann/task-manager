import { authService } from '../services/auth.js';

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errorMessage = document.getElementById('errorMessage');
        const submitButton = this.querySelector('button[type="submit"]');

        try {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2">Iniciando sesión...</span>
            `;

            const response = await authService.login(
                document.getElementById('email').value,
                document.getElementById('password').value
            );

            // Configurar CSRF token si es necesario
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            if (csrfToken) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
            }

            // Mostrar mensaje de éxito
            submitButton.innerHTML = `
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="ml-2">¡Sesión iniciada!</span>
            `;

            // Redirigir después de un breve delay
            setTimeout(() => {
                window.location.href = '/projects';
            }, 1000);

        } catch (error) {
            errorMessage.textContent = error.response?.data?.message || 'Error al iniciar sesión. Por favor, verifica tus credenciales.';
            errorMessage.classList.remove('hidden');

            submitButton.disabled = false;
            submitButton.innerHTML = `
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </span>
                Iniciar Sesión
            `;

            console.error('Error completo:', error);
        }
    });
});
