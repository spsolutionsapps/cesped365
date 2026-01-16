# 🚀 Inicio Rápido - Cesped365

## ⚡ En 3 Pasos

### 1. Instalar
```bash
npm install
```

### 2. Ejecutar
```bash
npm run dev
```

### 3. Abrir
```
http://localhost:3000
```

## 🔑 Credenciales de Prueba

### Administrador
```
Email: admin@cesped365.com
Password: admin123
```

### Cliente
```
Email: cliente@example.com
Password: cliente123
```

## 📱 Qué Puedes Hacer

### Como Cliente
1. Ve a `/login`
2. Usa las credenciales de cliente
3. Explora:
   - ✅ Resumen de tu jardín
   - ✅ Reportes mensuales
   - ✅ Historial de visitas
   - ✅ Tu perfil y suscripción

### Como Admin
1. Ve a `/login`
2. Usa las credenciales de admin
3. Explora:
   - ✅ Dashboard con estadísticas
   - ✅ Gestión de clientes
   - ✅ Todos los reportes
   - ✅ Historial completo

## 🎯 Rutas Disponibles

```
/                      → Landing Page
/login                 → Login
/dashboard/resumen     → Dashboard principal
/dashboard/reportes    → Reportes
/dashboard/historial   → Historial
/dashboard/perfil      → Perfil
/dashboard/clientes    → Clientes (solo admin)
```

## 📦 Comandos Útiles

```bash
# Desarrollo
npm run dev

# Build para producción
npm run build

# Preview de producción
npm run preview
```

## 🐛 Problemas Comunes

### Puerto ocupado
```bash
# Cambiar puerto en vite.config.js
server: {
  port: 3001  // Cambiar aquí
}
```

### Dependencias no instaladas
```bash
rm -rf node_modules package-lock.json
npm install
```

### Error de Tailwind
```bash
# Verificar que postcss.config.js existe
# Reinstalar dependencias
npm install -D tailwindcss postcss autoprefixer
```

## 📚 Más Información

- **Documentación completa**: Ver `README.md`
- **Integración con backend**: Ver `INTEGRACION_BACKEND.md`
- **Resumen del proyecto**: Ver `RESUMEN_PROYECTO.md`

## ✨ Características Destacadas

- 🎨 Diseño moderno y profesional
- 📱 100% responsive
- ⚡ Carga rápida con Vite
- 🔐 Sistema de roles
- 📊 Dashboards interactivos
- 🎯 UX intuitiva

## 🎉 ¡Listo!

Ya puedes explorar el sistema completo. Todos los datos son simulados (mock) y están listos para ser reemplazados por datos reales cuando conectes el backend.

---

**¿Dudas?** Revisa los archivos de documentación en la raíz del proyecto.
