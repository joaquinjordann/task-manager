// Configuración global de Axios
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Interceptor para manejar tokens
axios.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
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
    async error => {
        if (error.response?.status === 401) {
            // Token expirado o inválido
            try {
                // Intentar refrescar el token
                const response = await axios.post('/api/refresh');
                if (response.data.authorization?.token) {
                    localStorage.setItem('token', response.data.authorization.token);
                    // Reintentar la request original con el nuevo token
                    error.config.headers.Authorization = `Bearer ${response.data.authorization.token}`;
                    return axios(error.config);
                }
            } catch (refreshError) {
                localStorage.removeItem('token');
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

// Función para iniciar sesión
export async function login(email, password) {
    try {
        const response = await axios.post('/api/login', { email, password });

        if (response.data.authorization?.token) {
            localStorage.setItem('token', response.data.authorization.token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.authorization.token}`;
        }

        return response.data;
    } catch (error) {
        console.error('Error de login:', error);
        throw error;
    }
}

// Funciones de autenticación
export const authService = {
    login,
    logout: async () => {
        try {
            await axios.post('/api/logout');
            localStorage.removeItem('token');
            axios.defaults.headers.common['Authorization'] = '';
            window.location.href = '/login';
        } catch (error) {
            console.error('Error durante el logout:', error);
            throw error;
        }
    },
    isAuthenticated: () => {
        return !!localStorage.getItem('token');
    }
};
