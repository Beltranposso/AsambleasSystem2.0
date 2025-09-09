<?php
// Incluir helpers globales una sola vez
require_once __DIR__ . '/../../core/helpers.php';

// Definir rutas organizadas por rol
$sidebarRoutes = [
    'administrador' => [
        'Dashboard' => '/dashboard/admin',
        'Asambleas' => '/admin/asambleas',
        'Usuarios' => '/admin/usuarios',
        'Coordinadores' => '/admin/coordinadores',
        'Conjuntos' => '/admin/conjuntos',
        'Votaciones' => '/admin/votaciones',
        'Reportes' => '/admin/reportes',
        'Configuración' => '/admin/configuracion'
    ],
    'coordinador' => [
        'Dashboard' => '/dashboard/coordinador',
        'Mis Asambleas' => '/coordinador/asambleas',
        'Control Asistencia' => '/coordinador/asistencia',
        'Participantes' => '/coordinador/participantes',
        'Control Quórum' => '/coordinador/quorum',
        'Votaciones' => '/coordinador/votaciones',
        'Reportes' => '/coordinador/reportes/participacion'
    ],
    'operador' => [
        'Dashboard' => '/dashboard/operador',
        'Mis Asambleas' => '/operador/asambleas',
        'Registro Asistencia' => '/operador/registro-asistencia',
        'Verificar Usuarios' => '/operador/verificar-usuarios',
        'Coeficientes' => '/operador/coeficientes',
        'Estado Pagos' => '/operador/estado-pagos',
        'Reportes' => '/operador/reportes/asistencia'
    ],
    'votante' => [
        'Dashboard' => '/dashboard/votante',
        'Mi Perfil' => '/votante/perfil',
        'Asambleas' => '/votante/asambleas',
        'Mi Historial' => '/votante/historial',
        'Mis Votaciones' => '/votante/mis-votos'
    ]
];

// Iconos para cada opción del menú
$menuIcons = [
    'Dashboard' => 'fas fa-tachometer-alt',
    'Asambleas' => 'fas fa-users',
    'Mis Asambleas' => 'fas fa-calendar-alt',
    'Usuarios' => 'fas fa-user-friends',
    'Coordinadores' => 'fas fa-user-tie',
    'Conjuntos' => 'fas fa-building',
    'Votaciones' => 'fas fa-vote-yea',
    'Reportes' => 'fas fa-chart-bar',
    'Configuración' => 'fas fa-cog',
    'Control Asistencia' => 'fas fa-clipboard-check',
    'Participantes' => 'fas fa-user-check',
    'Control Quórum' => 'fas fa-chart-pie',
    'Registro Asistencia' => 'fas fa-user-check',
    'Verificar Usuarios' => 'fas fa-search',
    'Coeficientes' => 'fas fa-calculator',
    'Estado Pagos' => 'fas fa-credit-card',
    'Mi Perfil' => 'fas fa-user',
    'Mi Historial' => 'fas fa-history',
    'Mis Votaciones' => 'fas fa-vote-yea'
];
?>

<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if (isset($userRole) && isset($sidebarRoutes[$userRole])): ?>
                <?php foreach ($sidebarRoutes[$userRole] as $label => $route): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url($route) ?>">
                            <i class="<?= $menuIcons[$label] ?? 'fas fa-circle' ?> me-2"></i>
                            <?= $label ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <!-- Separador -->
                <hr class="my-3">
                
                <!-- Opciones comunes para todos los roles -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/perfil') ?>">
                        <i class="fas fa-user-cog me-2"></i>Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/configuracion') ?>">
                        <i class="fas fa-cog me-2"></i>Configuración
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?= url('/logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/login') ?>">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>