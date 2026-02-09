// API Service - Conectado con CodeIgniter 4 Backend

// Obtener URL base de la API
// En producción, usar la variable de entorno o detectar automáticamente
export function getApiBaseUrl() {
  // Si hay variable de entorno, usarla
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL;
  }
  
  // En desarrollo local: same-origin para que Vite proxy reescriba /api/login → /login
  if (typeof window !== 'undefined') {
    const isProduction = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
    if (!isProduction) {
      return `${window.location.origin}/api`;
    }
    // En producción, usar el dominio actual + /api
    return `${window.location.protocol}//${window.location.host}/api`;
  }
  
  // Fallback desarrollo local (SSR o pre-hydration)
  return 'http://localhost:3000/api';
}

const API_BASE_URL = getApiBaseUrl();

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
        // Enviar todos los valores, incluso null (como cadena vacía)
        if (value === null || value === undefined) {
          formData.append(key, '');
        } else if (typeof value === 'boolean') {
          formData.append(key, value ? '1' : '0');
        } else {
          formData.append(key, value);
        }
      });
      config.body = formData;
      config.headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
  }

  try {
    // Asegurar que el endpoint comience con /
    const normalizedEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
    const fullUrl = `${API_BASE_URL}${normalizedEndpoint}`;
    
    // Log para debugging
    console.log(`API Request: ${config.method || 'GET'} ${fullUrl}`);
    
    const response = await fetch(fullUrl, config);
    
    console.log('Response status:', response.status);
    
    const text = await response.text();
    const contentType = response.headers.get('content-type') || '';
    
    let data;
    if (contentType.includes('application/json') && text.trim()) {
      try {
        data = JSON.parse(text);
      } catch (parseErr) {
        console.error('JSON parse error. Raw response:', text.substring(0, 300));
        throw new Error(`Respuesta inválida del servidor (no es JSON válido). Status: ${response.status}`);
      }
    } else if (!contentType.includes('application/json')) {
      console.error('Response no es JSON:', text.substring(0, 200));
      throw new Error(`El servidor no devolvió JSON. Status: ${response.status}. ${text.substring(0, 80)}`);
    } else {
      data = {};
    }
    
    // Manejar respuestas de error del backend (CodeIgniter usa data.messages.error)
    if (!response.ok || !data.success) {
      const msg = data.messages?.error ?? data.message ?? `Error en la petición (${response.status})`;
      const err = new Error(typeof msg === 'string' ? msg : (msg.message || JSON.stringify(msg)));
      if (data.errors && typeof data.errors === 'object') {
        err.errors = data.errors;
      }
      throw err;
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
  },
  
  // PUT /api/me
  updateProfile: async (profileData) => {
    const response = await fetch(`${API_BASE_URL}/me`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(profileData)
    });
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`El servidor no devolvió JSON. Status: ${response.status}. Response: ${text.substring(0, 100)}`);
    }
    
    const data = await response.json();
    
    if (!response.ok || !data.success) {
      const error = new Error(data.message || `Error en la petición (${response.status})`);
      if (data.errors) {
        error.errors = data.errors;
      }
      throw error;
    }
    
    return data;
  },
  
  // POST /api/me/password (form-urlencoded para compatibilidad con CodeIgniter)
  updatePassword: async (passwordData) => {
    const body = new URLSearchParams();
    body.append('current_password', passwordData.current_password || '');
    body.append('new_password', passwordData.new_password || '');
    body.append('confirm_password', passwordData.confirm_password || '');
    
    const response = await fetch(`${API_BASE_URL}/me/password`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      credentials: 'include',
      body: body.toString()
    });
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`El servidor no devolvió JSON. Status: ${response.status}. Response: ${text.substring(0, 100)}`);
    }
    
    const data = await response.json();
    
    if (!response.ok || !data.success) {
      const error = new Error(data.message || `Error en la petición (${response.status})`);
      if (data.errors) {
        error.errors = data.errors;
      }
      throw error;
    }
    
    return data;
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

  // PATCH /api/reportes/:id/rating - Cliente evalúa el servicio (estrellas 1-5 + comentario)
  submitRating: async (id, { rating, feedback }) => {
    return await request(`/reportes/${id}/rating`, {
      method: 'PATCH',
      body: { rating, feedback: feedback ?? '' }
    });
  },
  
  // POST /api/reportes (admin only)
  create: async (reporteData) => {
    return await request('/reportes', {
      method: 'POST',
      body: reporteData
    });
  },

  // PUT /api/reportes/:id (admin only)
  update: async (id, reporteData) => {
    return await request(`/reportes/${id}`, {
      method: 'PUT',
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
  },
  // GET /api/dashboard/ganancias-por-dia?year=2025&month=2 (solo admin)
  getGananciasPorDia: async (year, month) => {
    return await request(`/dashboard/ganancias-por-dia?year=${year}&month=${month}`);
  },
  // POST /api/dashboard/ganancia (solo admin) - agrega monto a la ganancia del mes actual
  addGanancia: async (monto) => {
    return await request('/dashboard/ganancia', {
      method: 'POST',
      body: { monto }
    });
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

// Pagos endpoints
export const paymentAPI = {
  // POST /api/payment/create-preference
  createPreference: async (planId) => {
    return await request('/payment/create-preference', {
      method: 'POST',
      body: { plan_id: planId }
    });
  },

  // POST /api/payment/create-subscription (Mercado Pago Preapproval)
  createSubscription: async (planId) => {
    return await request('/payment/create-subscription', {
      method: 'POST',
      body: { plan_id: planId }
    });
  },

  // POST /api/payment/cancel-subscription (Mercado Pago Preapproval)
  cancelSubscription: async () => {
    return await request('/payment/cancel-subscription', {
      method: 'POST'
    });
  },
};

// Jardines endpoints
export const jardinesAPI = {
  // GET /api/jardines
  getAll: async () => {
    return await request('/jardines');
  }
};

// Visitas programadas endpoints
export const scheduledVisitsAPI = {
  // GET /api/scheduled-visits
  getAll: async () => {
    return await request('/scheduled-visits');
  },

  // GET /api/scheduled-visits/availability?from=YYYY-MM-DD&to=YYYY-MM-DD
  getAvailability: async (from, to) => {
    return await request(`/scheduled-visits/availability?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
  },
  
  // GET /api/scheduled-visits/:id
  getById: async (id) => {
    return await request(`/scheduled-visits/${id}`);
  },
  
  // POST /api/scheduled-visits
  create: async (visitData) => {
    const response = await fetch(`${API_BASE_URL}/scheduled-visits`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(visitData)
    });
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`El servidor no devolvió JSON. Status: ${response.status}. Response: ${text.substring(0, 100)}`);
    }
    
    const data = await response.json();
    
    if (!response.ok || !data.success) {
      const error = new Error(data.message || `Error en la petición (${response.status})`);
      if (data.errors) {
        error.errors = data.errors;
      }
      throw error;
    }
    
    return data;
  },
  
  // PUT /api/scheduled-visits/:id
  update: async (id, visitData) => {
    const response = await fetch(`${API_BASE_URL}/scheduled-visits/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(visitData)
    });
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`El servidor no devolvió JSON. Status: ${response.status}. Response: ${text.substring(0, 100)}`);
    }
    
    const data = await response.json();
    
    if (!response.ok || !data.success) {
      throw new Error(data.message || `Error en la petición (${response.status})`);
    }
    
    return data;
  },
  
  // DELETE /api/scheduled-visits/:id
  delete: async (id) => {
    return await request(`/scheduled-visits/${id}`, {
      method: 'DELETE'
    });
  }
};

// Días bloqueados (solo admin) - bloquear día para que nadie reserve turno
export const blockedDaysAPI = {
  getByRange: async (from, to) => {
    return await request(`/blocked-days?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
  },
  create: async (blocked_date, description = '') => {
    const response = await fetch(`${API_BASE_URL}/blocked-days`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ blocked_date, description })
    });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Error al bloquear el día');
    return data;
  },
  delete: async (id) => {
    return await request(`/blocked-days/${id}`, { method: 'DELETE' });
  }
};
