import { writable } from 'svelte/store';

/**
 * Store para notificar que la lista de reportes cambió (crear/editar).
 * Resumen y otras vistas pueden suscribirse para refrescar el "último reporte".
 */
function createReportesRefresh() {
  const { subscribe, set, update } = writable(0);
  return {
    subscribe,
    trigger: () => update((n) => n + 1),
    set,
  };
}

export const reportesRefresh = createReportesRefresh();
