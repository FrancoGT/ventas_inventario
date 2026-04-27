// Token CSRF global para todas las peticiones AJAX
const CSRF_TOKEN = document.querySelector('meta[name="' + Object.keys(document.querySelector('meta[name]').dataset)[0] + '"]')?.content || '';

// Configurar AJAX global con CSRF
$.ajaxSetup({
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    beforeSend: function(xhr, settings) {
        if (settings.type === 'POST') {
            const csrfToken = $('meta[name]').attr('content');
            const csrfName = $('meta[name]').attr('name');
            
            if (settings.data instanceof FormData) {
                settings.data.append(csrfName, csrfToken);
            } else if (typeof settings.data === 'string') {
                settings.data += '&' + csrfName + '=' + csrfToken;
            } else {
                settings.data = settings.data || {};
                settings.data[csrfName] = csrfToken;
            }
        }
    }
});

// Interceptar respuestas 401 (sesión expirada)
$(document).ajaxError(function(event, xhr) {
    if (xhr.status === 401) {
        Swal.fire({
            icon: 'warning',
            title: 'Sesión expirada',
            text: 'Debe iniciar sesión nuevamente.',
            confirmButtonText: 'Ir al login'
        }).then(() => {
            window.location.href = '/login';
        });
    }
});

// Toggle Sidebar
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const topbar = document.getElementById('topbar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (menuToggle) {
    menuToggle.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            // Móvil: mostrar/ocultar sidebar
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        } else {
            // Desktop: colapsar sidebar
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('full-width');
            topbar.classList.toggle('full-width');
        }
    });
}

// Cerrar sidebar al hacer click en overlay (móvil)
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
}

// User Dropdown
const userMenuBtn = document.getElementById('userMenuBtn');
const userDropdownMenu = document.getElementById('userDropdownMenu');

if (userMenuBtn) {
    userMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdownMenu.classList.toggle('show');
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!userMenuBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
            userDropdownMenu.classList.remove('show');
        }
    });
}

// Ajustar layout en resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
        if (sidebar) sidebar.classList.remove('show');
    }
});