<?php $title = 'Métricas del Sistema'; $userRole = 'administrador'; ?>
<?php include '../views/layouts/header.php'; ?>


<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-chart-bar me-2"></i>Métricas del Sistema</h1>
            <button class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i>Exportar Reporte
            </button>
        </div>
        
        <!-- Métricas generales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Usuarios</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">125</div>
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
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Participación Promedio</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">78%</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Votaciones Realizadas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">45</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-vote-yea fa-2x text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Conjuntos Activos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos y estadísticas -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Participación por Mes</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <!-- Aquí iría un gráfico dinámico -->
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <div class="text-center">
                                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                                    <p>Gráfico de participación mensual</p>
                                    <small>Se implementará con Chart.js o similar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-pie-chart me-2"></i>Distribución por Rol</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Votantes</span>
                                <span>85%</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" style="width: 85%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Coordinadores</span>
                                <span>10%</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" style="width: 10%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Operadores</span>
                                <span>4%</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" style="width: 4%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Administradores</span>
                                <span>1%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: 1%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Alertas</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <small>
                                <i class="fas fa-clock me-1"></i>
                                3 asambleas próximas requieren coordinador
                            </small>
                        </div>
                        <div class="alert alert-info" role="alert">
                            <small>
                                <i class="fas fa-users me-1"></i>
                                15 nuevos votantes registrados esta semana
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de actividad reciente -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Actividad Reciente</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Actividad</th>
                                        <th>Usuario</th>
                                        <th>Detalle</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>23/07/2024 14:30</td>
                                        <td>Votación creada</td>
                                        <td>Admin Sistema</td>
                                        <td>Presupuesto 2024 - Los Álamos</td>
                                        <td><span class="badge bg-success">Activa</span></td>
                                    </tr>
                                    <tr>
                                        <td>23/07/2024 12:15</td>
                                        <td>Usuario registrado</td>
                                        <td>Ana García</td>
                                        <td>Nuevo votante - Apartamento 305</td>
                                        <td><span class="badge bg-info">Completado</span></td>
                                    </tr>
                                    <tr>
                                        <td>22/07/2024 16:45</td>
                                        <td>Asamblea finalizada</td>
                                        <td>Coordinador López</td>
                                        <td>Asamblea Ordinaria - Torre del Sol</td>
                                        <td><span class="badge bg-secondary">Finalizada</span></td>
                                    </tr>
                                    <tr>
                                        <td>22/07/2024 10:20</td>
                                        <td>Operador asignado</td>
                                        <td>Carlos Méndez</td>
                                        <td>Asignado a Asamblea Villa Verde</td>
                                        <td><span class="badge bg-primary">Asignado</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../views/layouts/footer.php'; ?>