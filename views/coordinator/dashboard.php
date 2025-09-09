<?php $title = 'Dashboard - Coordinador'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>

    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard - Coordinador</h1>
        </div>
        
        <!-- Resumen de actividades -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Mis Asambleas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Próximas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">1</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Operadores</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">2</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-cog fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Próximas Asambleas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($upcomingAssemblies) && !empty($upcomingAssemblies)): ?>
                            <?php foreach ($upcomingAssemblies as $assembly): ?>
                                <div class="card mb-3 border-left-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($assembly['titulo']); ?></h6>
                                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($assembly['conjunto_nombre']); ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $assembly['estado'] === 'activa' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($assembly['estado']); ?>
                                                </span>
                                                <div class="mt-2">
                                                    <a href="/coordinator/assembly-management?id=<?php echo $assembly['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-cog me-1"></i>Gestionar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <p>No hay asambleas próximas programadas</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-plus me-2"></i>Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/coordinator/create-operator" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Crear Operador
                            </a>
                            <a href="/coordinator/my-assemblies" class="btn btn-success">
                                <i class="fas fa-list me-2"></i>Ver Mis Asambleas
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle me-2"></i>Información</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <p><strong>Responsabilidades:</strong></p>
                            <ul>
                                <li>Coordinar asambleas asignadas</li>
                                <li>Crear y gestionar operadores</li>
                                <li>Supervisar el proceso de votación</li>
                                <li>Generar reportes de participación</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../views/layouts/footer.php'; ?>
