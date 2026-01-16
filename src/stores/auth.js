import { writable } from 'svelte/store';
import { authAPI } from '../services/api.js';

// Real authentication store - Conectado con backend
function createAuthStore() {
  const { subscribe, set, update } = writable({
    isAuthenticated: false,
    user: null,
    role: null, // 'admin' or 'cliente'
    isChecking: true // Nuevo: indica si está verificando sesión
  });

  return {
    subscribe,
    login: async (email, password) => {
      try {
        // Llamar al backend real
        const response = await authAPI.login(email, password);
        
        if (response.success && response.user) {
          set({
            isAuthenticated: true,
            user: {
              id: response.user.id,
              name: response.user.name,
              email: response.user.email,
              phone: response.user.phone,
              address: response.user.address,
              avatar: null
            },
            role: response.user.role,
            isChecking: false
          });
          return { success: true, role: response.user.role };
        }
        
        return { success: false, error: 'Error en el login' };
      } catch (error) {
        console.error('Login error:', error);
        return { success: false, error: error.message || 'Credenciales inválidas' };
      }
    },
    logout: async () => {
      try {
        await authAPI.logout();
      } catch (error) {
        console.error('Logout error:', error);
      }
      set({
        isAuthenticated: false,
        user: null,
        role: null,
        isChecking: false
      });
    },
    // Verificar sesión al cargar la app
    checkAuth: async () => {
      console.log('🟢 auth.checkAuth(): Iniciando...');
      update(state => ({ ...state, isChecking: true }));
      try {
        console.log('🟢 auth.checkAuth(): Llamando authAPI.getCurrentUser()...');
        const response = await authAPI.getCurrentUser();
        console.log('🟢 auth.checkAuth(): response =', response);
        if (response.success && response.user) {
          console.log('🟢 auth.checkAuth(): Usuario autenticado =', response.user);
          set({
            isAuthenticated: true,
            user: {
              id: response.user.id,
              name: response.user.name,
              email: response.user.email,
              phone: response.user.phone,
              address: response.user.address,
              avatar: null
            },
            role: response.user.role,
            isChecking: false
          });
          return true;
        }
      } catch (error) {
        console.log('❌ auth.checkAuth(): Error =', error);
      }
      console.log('🟢 auth.checkAuth(): No hay sesión activa');
      set({
        isAuthenticated: false,
        user: null,
        role: null,
        isChecking: false
      });
      return false;
    }
  };
}

export const auth = createAuthStore();
