/**
 * Sistema de Notificaciones Profesional
 * Compatible con Bootstrap 5 y con fallbacks para producción
 */
class NotificationSystem {
    constructor() {
        this.container = null;
        this.toastCounter = 0;
        this.initialized = false;
        this.init();
    }

    /**
     * Inicializa el sistema de notificaciones
     */
    init() {
        if (this.initialized) return;

        this.createContainer();
        this.checkBootstrapAvailability();
        this.initialized = true;

        // Forzar uso de fallback para asegurar estilos
        this.forceFallback = true;
    }

    /**
     * Crea el contenedor principal para las notificaciones
     */
    createContainer() {
        // Remover contenedor existente si hay
        const existingContainer = document.querySelector('.notification-container');
        if (existingContainer) {
            existingContainer.remove();
        }

        this.container = document.createElement('div');
        this.container.className = 'notification-container';
        this.container.style.setProperty('position', 'fixed', 'important');
        this.container.style.setProperty('top', '20px', 'important');
        this.container.style.setProperty('right', '20px', 'important');
        this.container.style.setProperty('z-index', '10600', 'important');
        this.container.style.setProperty('pointer-events', 'none', 'important');
        this.container.style.setProperty('font-family', "'Open Sans', sans-serif", 'important');

        document.body.appendChild(this.container);
    }

    /**
     * Verifica la disponibilidad de Bootstrap
     */
    checkBootstrapAvailability() {
        this.hasBootstrap = typeof bootstrap !== 'undefined' && bootstrap.Toast && !this.forceFallback;
    }

    /**
     * Muestra una notificación de éxito
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (opcional, default: 4000)
     */
    success(message, duration = 4000) {
        this.show(message, 'success', duration);
    }

    /**
     * Muestra una notificación de error
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (opcional, default: 5000)
     */
    error(message, duration = 5000) {
        this.show(message, 'error', duration);
    }

    /**
     * Muestra una notificación de advertencia
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (opcional, default: 4500)
     */
    warning(message, duration = 4500) {
        this.show(message, 'warning', duration);
    }

    /**
     * Muestra una notificación de información
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (opcional, default: 4000)
     */
    info(message, duration = 4000) {
        this.show(message, 'info', duration);
    }

    /**
     * Método principal para mostrar notificaciones
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo de notificación (success, error, warning, info)
     * @param {number} duration - Duración en ms
     */
    show(message, type = 'info', duration = 4000) {
        const notificationId = `notification-${++this.toastCounter}`;

        // Usar fallback por defecto para asegurar estilos
        this.showFallbackToast(notificationId, message, type, duration);
    }

    /**
     * Muestra toast usando Bootstrap
     */
    showBootstrapToast(id, message, type, duration) {
        try {
            const toastHtml = this.createToastHTML(id, message, type);

            // Crear elemento temporal
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = toastHtml;
            const toastElement = tempDiv.firstElementChild;

            // Agregar al contenedor
            this.container.appendChild(toastElement);

            // Crear instancia de Bootstrap Toast
            const toast = new bootstrap.Toast(toastElement, {
                delay: duration,
                autohide: true
            });

            // Mostrar toast
            toast.show();

            // Limpiar después de que se oculte
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });

        } catch (error) {
            console.warn('Error mostrando toast de Bootstrap, usando fallback:', error);
            this.showFallbackToast(id, message, type, duration);
        }
    }

    /**
     * Muestra toast usando sistema fallback (siempre se usa para asegurar estilos)
     */
    showFallbackToast(id, message, type, duration) {
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `fallback-toast fallback-${type}`;

        // Aplicar estilos de manera más directa usando setProperty para !important
        const styles = {
            'background': this.getBackgroundColor(type),
            'color': 'white',
            'padding': '16px 20px',
            'border-radius': '8px',
            'margin-bottom': '12px',
            'box-shadow': '0 4px 20px rgba(0,0,0,0.2)',
            'pointer-events': 'auto',
            'min-width': '320px',
            'max-width': '450px',
            'position': 'relative',
            'font-size': '15px',
            'line-height': '1.5',
            'font-family': "'Open Sans', sans-serif",
            'font-weight': '400',
            'opacity': '0',
            'transform': 'translateX(100%)',
            'transition': 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
            'border': 'none',
            'word-wrap': 'break-word',
            'z-index': '10601'
        };

        for (const [prop, value] of Object.entries(styles)) {
            toast.style.setProperty(prop, value, 'important');
        }

        toast.innerHTML = `
            <div style="
                display: flex !important;
                align-items: flex-start !important;
                justify-content: space-between !important;
                gap: 12px !important;
            ">
                <div style="
                    flex: 1 !important;
                    font-weight: 500 !important;
                    line-height: 1.4 !important;
                    color: white !important;
                ">
                    <i class="${this.getIconClass(type)}" style="
                        margin-right: 8px !important;
                        font-size: 16px !important;
                        opacity: 0.9 !important;
                        color: white !important;
                    "></i>
                    ${message}
                </div>
                <button onclick="event.stopPropagation(); this.parentElement.parentElement.remove()"
                        style="
                            background: none !important;
                            border: none !important;
                            color: white !important;
                            cursor: pointer !important;
                            padding: 4px !important;
                            margin: 0 !important;
                            border-radius: 50% !important;
                            width: 24px !important;
                            height: 24px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            opacity: 0.8 !important;
                            transition: opacity 0.2s !important;
                            flex-shrink: 0 !important;
                        "
                        onmouseover="this.style.opacity='1'"
                        onmouseout="this.style.opacity='0.8'">
                    ×
                </button>
            </div>
        `;

        this.container.appendChild(toast);

        // Forzar reflow para asegurar que las animaciones funcionen
        toast.offsetHeight;

        // Animación de entrada
        setTimeout(() => {
            toast.style.setProperty('opacity', '1', 'important');
            toast.style.setProperty('transform', 'translateX(0)', 'important');
        }, 50);

        // Auto-remover con animación de salida
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.setProperty('opacity', '0', 'important');
                toast.style.setProperty('transform', 'translateX(100%)', 'important');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 400);
            }
        }, duration);
    }

    /**
     * Crea HTML para toast de Bootstrap
     */
    createToastHTML(id, message, type) {
        const bgClass = `text-bg-${type}`;
        const iconClass = this.getIconClass(type);

        return `
            <div id="${id}" class="toast align-items-center ${bgClass} border-0"
                 role="alert" aria-live="assertive" aria-atomic="true"
                 style="pointer-events: auto; margin-bottom: 10px;">
                <div class="d-flex">
                    <div class="toast-body" style="flex: 1;">
                        <i class="${iconClass}" style="margin-right: 8px;"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        `;
    }

    /**
     * Obtiene el color de fondo para el tipo de notificación
     */
    getBackgroundColor(type) {
        const colors = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };
        return colors[type] || colors.info;
    }

    /**
     * Obtiene la clase del ícono para Bootstrap
     */
    getIconClass(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    /**
     * Método estático para obtener instancia global
     */
    static getInstance() {
        if (!window.notificationSystem) {
            window.notificationSystem = new NotificationSystem();
        }
        return window.notificationSystem;
    }

    /**
     * Método estático para mostrar notificación de éxito
     */
    static success(message, duration) {
        return this.getInstance().success(message, duration);
    }

    /**
     * Método estático para mostrar notificación de error
     */
    static error(message, duration) {
        return this.getInstance().error(message, duration);
    }

    /**
     * Método estático para mostrar notificación de advertencia
     */
    static warning(message, duration) {
        return this.getInstance().warning(message, duration);
    }

    /**
     * Método estático para mostrar notificación de información
     */
    static info(message, duration) {
        return this.getInstance().info(message, duration);
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        NotificationSystem.getInstance();
    });
} else {
    NotificationSystem.getInstance();
}

// Hacer disponible globalmente
window.NotificationSystem = NotificationSystem;