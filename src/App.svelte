<script>
  import { onMount } from 'svelte';
  import { Router, Route } from 'svelte-routing';
  import { auth } from './stores/auth';
  
  // Pages
  import Landing from './pages/Landing.svelte';
  import Login from './pages/Login.svelte';
  import Registro from './pages/Registro.svelte';
  import Dashboard from './pages/Dashboard.svelte';
  
  // Dashboard views
  import DashboardResumen from './pages/dashboard/Resumen.svelte';
  import DashboardMiJardin from './pages/dashboard/MiJardin.svelte';
  import DashboardReportes from './pages/dashboard/Reportes.svelte';
  import DashboardHistorial from './pages/dashboard/Historial.svelte';
  import DashboardPerfil from './pages/dashboard/Perfil.svelte';
  
  // Admin views
  import AdminClientes from './pages/dashboard/admin/Clientes.svelte';
  import AdminAgenda from './pages/dashboard/admin/Agenda.svelte';
  
  export let url = "";
  
  // Verificar sesión al cargar la app
  onMount(async () => {
    await auth.checkAuth();
  });
</script>

<Router {url}>
  <Route path="/" component={Landing} />
  <Route path="/login" component={Login} />
  <Route path="/registro" component={Registro} />
  <Route path="/dashboard/resumen">
    <Dashboard>
      <DashboardResumen />
    </Dashboard>
  </Route>
  <Route path="/dashboard/mi-jardin">
    <Dashboard>
      <DashboardMiJardin />
    </Dashboard>
  </Route>
  <Route path="/dashboard/reportes">
    <Dashboard>
      <DashboardReportes />
    </Dashboard>
  </Route>
  <Route path="/dashboard/historial">
    <Dashboard>
      <DashboardHistorial />
    </Dashboard>
  </Route>
  <Route path="/dashboard/perfil">
    <Dashboard>
      <DashboardPerfil />
    </Dashboard>
  </Route>
  <Route path="/dashboard/clientes">
    <Dashboard>
      <AdminClientes />
    </Dashboard>
  </Route>
  <Route path="/dashboard/agenda">
    <Dashboard>
      <AdminAgenda />
    </Dashboard>
  </Route>
  <Route path="/dashboard">
    <Dashboard>
      <DashboardResumen />
    </Dashboard>
  </Route>
  
  <!-- Redirección por defecto según rol se maneja en Login -->
</Router>
