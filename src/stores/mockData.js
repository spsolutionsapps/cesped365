// Mock data para desarrollo

export const mockReportes = [
  {
    id: 1,
    fecha: '2026-01-10',
    estadoGeneral: 'Bueno',
    cespedParejo: true,
    colorOk: true,
    manchas: false,
    zonasDesgastadas: false,
    malezasVisibles: false,
    crecimientoCm: 2.5,
    notaJardinero: 'El césped está en excelente estado. Se realizó corte regular y fertilización. Recomendamos mantener el riego actual.',
    imagenes: [
      '/assets/img/jardin-1.jpg',
      '/assets/img/jardin-2.jpg'
    ],
    jardinero: 'Carlos Rodríguez'
  },
  {
    id: 2,
    fecha: '2025-12-15',
    estadoGeneral: 'Regular',
    cespedParejo: true,
    colorOk: false,
    manchas: true,
    zonasDesgastadas: false,
    malezasVisibles: true,
    crecimientoCm: 3.2,
    notaJardinero: 'Se detectaron algunas manchas amarillas en la zona norte. Se aplicó tratamiento para malezas. Recomendamos aumentar el riego.',
    imagenes: [
      '/assets/img/jardin-3.jpg'
    ],
    jardinero: 'María González'
  },
  {
    id: 3,
    fecha: '2025-11-20',
    estadoGeneral: 'Bueno',
    cespedParejo: true,
    colorOk: true,
    manchas: false,
    zonasDesgastadas: true,
    malezasVisibles: false,
    crecimientoCm: 2.8,
    notaJardinero: 'Estado general bueno. Se identificó una pequeña zona desgastada cerca del árbol. Se realizó resembrado.',
    imagenes: [
      '/assets/img/jardin-4.jpg',
      '/assets/img/jardin-5.jpg'
    ],
    jardinero: 'Carlos Rodríguez'
  }
];

export const mockHistorial = [
  {
    id: 1,
    fecha: '2026-01-10',
    tipo: 'Mantenimiento Regular',
    estadoGeneral: 'Bueno',
    jardinero: 'Carlos Rodríguez'
  },
  {
    id: 2,
    fecha: '2025-12-15',
    tipo: 'Mantenimiento + Tratamiento',
    estadoGeneral: 'Regular',
    jardinero: 'María González'
  },
  {
    id: 3,
    fecha: '2025-11-20',
    tipo: 'Mantenimiento + Resembrado',
    estadoGeneral: 'Bueno',
    jardinero: 'Carlos Rodríguez'
  },
  {
    id: 4,
    fecha: '2025-10-25',
    tipo: 'Mantenimiento Regular',
    estadoGeneral: 'Bueno',
    jardinero: 'María González'
  },
  {
    id: 5,
    fecha: '2025-09-30',
    tipo: 'Mantenimiento Regular',
    estadoGeneral: 'Bueno',
    jardinero: 'Carlos Rodríguez'
  }
];

export const mockClientes = [
  {
    id: 1,
    nombre: 'Juan Pérez',
    email: 'juan.perez@example.com',
    telefono: '+54 11 1234-5678',
    direccion: 'Av. Siempre Viva 123',
    plan: 'Premium',
    estado: 'Activo',
    ultimaVisita: '2026-01-10',
    proximaVisita: '2026-01-17'
  },
  {
    id: 2,
    nombre: 'María García',
    email: 'maria.garcia@example.com',
    telefono: '+54 11 2345-6789',
    direccion: 'Calle Falsa 456',
    plan: 'Básico',
    estado: 'Activo',
    ultimaVisita: '2026-01-08',
    proximaVisita: '2026-01-22'
  },
  {
    id: 3,
    nombre: 'Roberto López',
    email: 'roberto.lopez@example.com',
    telefono: '+54 11 3456-7890',
    direccion: 'Av. Libertador 789',
    plan: 'Premium',
    estado: 'Activo',
    ultimaVisita: '2026-01-09',
    proximaVisita: '2026-01-16'
  },
  {
    id: 4,
    nombre: 'Ana Martínez',
    email: 'ana.martinez@example.com',
    telefono: '+54 11 4567-8901',
    direccion: 'Calle Mayor 321',
    plan: 'Estándar',
    estado: 'Pendiente',
    ultimaVisita: '2025-12-20',
    proximaVisita: '2026-01-15'
  }
];

export const mockEstadisticas = {
  totalClientes: 24,
  clientesActivos: 21,
  visitasEsteMes: 18,
  proximasVisitas: 6,
  reportesPendientes: 2
};
