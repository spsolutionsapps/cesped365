// API Service - Conectado con CodeIgniter 4 Backend

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';

// Helper para hacer requests
async function request(endpoint, options = {}) {
  const config = {
    credentials: 'include', // IMPORTANTE: Enviar cookies de sesión
    headers: {
      ...options.headers,
    },
    ...options,
  };

  // Para form data (usado por CodeIgniter)
  if (options.body && !(options.body instanceof FormData)) {
    // Si es JSON, convertir a URLSearchParams para CodeIgniter
    if (typeof options.body === 'object') {
      const formData = new URLSearchParams();
      Object.keys(options.body).forEach(key => {
        const value = options.body[key];
        // Solo agregar si el valor no es null, undefined o cadena vacía
        if (value !== null && value !== undefined && value !== '') {
          formData.append(key, value);
        }
      });
      config.body = formData;
      config.headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
  }

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
    
    // Log para debugging
    console.log(`API Request: ${config.method || 'GET'} ${API_BASE_URL}${endpoint}`);
    console.log('Response status:', response.status);
    
    // Verificar si la respuesta tiene contenido
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      // Si no es JSON, leer como texto para debugging
      const text = await response.text();
      console.error('Response no es JSON:', text.substring(0, 200));
      throw new Error(`El servidor no devolvió JSON. Status: ${response.status}. Response: ${text.substring(0, 100)}`);
    }
    
    const data = await response.json();
    
    // Manejar respuestas de error del backend
    if (!response.ok || !data.success) {
      throw new Error(data.message || `Error en la petición (${response.status})`);
    }
    
    return data;
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}

// Auth endpoints
export const authAPI = {
  // POST /api/login
  login: async (email, password) => {
    return await request('/login', {
      method: 'POST',
      body: { email, password }
    });
  },
  
  // POST /api/logout
  logout: async () => {
    return await request('/logout', { method: 'POST' });
  },
  
  // GET /api/me
  getCurrentUser: async () => {
    return await request('/me');
  }
};

// Reportes endpoints
export const reportesAPI = {
  // GET /api/reportes
  getAll: async () => {
    return await request('/reportes');
  },
  
  // GET /api/reportes/:id
  getById: async (id) => {
    return await request(`/reportes/${id}`);
  },
  
  // POST /api/reportes (admin only)
  create: async (reporteData) => {
    return await request('/reportes', {
      method: 'POST',
      body: reporteData
    });
  },

  // POST /api/reportes/:id/imagen (admin only)
  uploadImage: async (reporteId, imageFile) => {
    const formData = new FormData();
    formData.append('image', imageFile);
    
    return await request(`/reportes/${reporteId}/imagen`, {
      method: 'POST',
      body: formData
    });
  },

  // DELETE /api/reportes/:id (admin only)
  delete: async (id) => {
    return await request(`/reportes/${id}`, {
      method: 'DELETE'
    });
  }
};

// Clientes endpoints (admin only)
export const clientesAPI = {
  // GET /api/clientes
  getAll: async () => {
    return await request('/clientes');
  },
  
  // GET /api/clientes/:id
  getById: async (id) => {
    return await request(`/clientes/${id}`);
  },
  
  // PUT /api/clientes/:id
  update: async (id, clienteData) => {
    return await request(`/clientes/${id}`, {
      method: 'PUT',
      body: clienteData
    });
  },
  
  // POST /api/clientes
  create: async (clienteData) => {
    return await request('/clientes', {
      method: 'POST',
      body: clienteData
    });
  },
  
  // DELETE /api/clientes/:id
  delete: async (id) => {
    return await request(`/clientes/${id}`, {
      method: 'DELETE'
    });
  }
};

// Dashboard endpoint
export const dashboardAPI = {
  // GET /api/dashboard
  getDashboard: async () => {
    return await request('/dashboard');
  }
};

// Historial endpoint
export const historialAPI = {
  // GET /api/historial
  getHistorial: async () => {
    return await request('/historial');
  }
};

// Suscripciones endpoints
export const suscripcionesAPI = {
  // GET /api/subscriptions/my-subscription
  getMiSuscripcion: async () => {
    return await request('/subscriptions/my-subscription');
  },
  
  // GET /api/subscriptions/plans
  getPlanes: async () => {
    return await request('/subscriptions/plans');
  }
};

// Jardines endpoints
export const jardinesAPI = {
  // GET /api/jardines
  getAll: async () => {
    return await request('/jardines');
  }
};
