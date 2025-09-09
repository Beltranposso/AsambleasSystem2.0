<?php 
$title = 'Dashboard - Administrador'; 
$userRole = 'administrador'; 

// Incluir helpers globales
require_once __DIR__ . '../../../core/helpers.php';
?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard - Administrador</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>Exportar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Métricas principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2" style="border-left: 4px solid #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Asambleas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalAssemblies ?? 0; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2" style="border-left: 4px solid #1cc88a;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Asambleas Activas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeAssemblies ?? 0; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2" style="border-left: 4px solid #36b9cc;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Votantes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalVoters ?? 0; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-vote-yea fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2" style="border-left: 4px solid #f6c23e;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Coordinadores</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCoordinators ?? 0; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones rápidas y Asambleas recientes -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-plus me-2"></i>Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= url('/admin/asambleas/crear') ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Crear Nueva Asamblea
                            </a>
                            <a href="<?= url('/admin/usuarios/crear') ?>" class="btn btn-success">
                                <i class="fas fa-user-plus me-2"></i>Crear Usuario
                            </a>
                            <a href="<?= url('/admin/coordinadores') ?>" class="btn btn-info">
                                <i class="fas fa-user-tie me-2"></i>Gestionar Coordinadores
                            </a>
                            <a href="<?= url('/admin/reportes') ?>" class="btn btn-warning">
                                <i class="fas fa-chart-bar me-2"></i>Ver Reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clock me-2"></i>Asambleas Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentAssemblies)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentAssemblies as $assembly): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($assembly['titulo']) ?></h6>
                                            <p class="mb-1 text-muted small">
                                                <i class="fas fa-building me-1"></i>
                                                <?= htmlspecialchars($assembly['conjunto_nombre'] ?? 'Sin conjunto') ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?= 
                                                $assembly['estado'] === 'activa' ? 'success' : 
                                                ($assembly['estado'] === 'programada' ? 'primary' : 'secondary') 
                                            ?>">
                                                <?= ucfirst($assembly['estado']) ?>
                                            </span>
                                            <div class="mt-1">
                                                <a href="<?= url('/admin/asambleas/editar/' . $assembly['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="<?= url('/admin/asambleas') ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Ver Todas
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">No hay asambleas registradas</p>
                                <a href="<?= url('/admin/asambleas/crear') ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Crear Primera Asamblea
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos y estadísticas adicionales -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-area me-2"></i>Actividad del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <!-- Aquí iría un gráfico con Chart.js o similar -->
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">Gráfico de actividad del sistema</p>
                                <small class="text-muted">Próximamente: Estadísticas de participación y votaciones</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-exclamation-triangle me-2"></i>Alertas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($alerts)): ?>
                            <?php foreach ($alerts as $alert): ?>
                                <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show" role="alert">
                                    <i class="fas fa-<?= $alert['icon'] ?> me-2"></i>
                                    <?= htmlspecialchars($alert['message']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="text-muted mb-0">Todo funcionando correctamente</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tasks me-2"></i>Enlaces Rápidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="<?= url('/admin/usuarios') ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i>Gestión de Usuarios
                            </a>
                            <a href="<?= url('/admin/conjuntos') ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-building me-2"></i>Conjuntos Residenciales
                            </a>
                            <a href="<?= url('/admin/votaciones') ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-vote-yea me-2"></i>Votaciones Activas
                            </a>
                            <a href="<?= url('/admin/configuracion') ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-cog me-2"></i>Configuración
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../views/layouts/footer.php'; ?>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.text-gray-800 { color: #5a5c69 !important; }
.text-gray-300 { color: #dddfeb !important; }
.chart-area { position: relative; height: 10rem; }
</style>