<?php $title = 'Validar Asistencia'; $userRole = 'operador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-check-circle me-2"></i>Validar Asistencia</h1>
            <div>
                <button class="btn btn-success me-2">
                    <i class="fas fa-download me-1"></i>Exportar Lista
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scanQRModal">
                    <i class="fas fa-qrcode me-1"></i>Escanear QR
                </button>
            </div>
        </div>
        
        <!-- Filtros y búsqueda -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form class="row g-3">
                            <div class="col-md-4">
                                <label for="assembly_select" class="form-label">Asamblea</label>
                                <select class="form-select" id="assembly_select">
                                    <option value="">Seleccionar asamblea...</option>
                                    <option value="1" selected>Asamblea Ordinaria Marzo - Los Álamos</option>
                                    <option value="2">Asamblea Extraordinaria - Torre del Sol</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search_participant" class="form-label">Buscar Participante</label>
                                <input type="text" class="form-control" id="search_participant" placeholder="Nombre, cédula o apartamento">
                            </div>
                            <div class="col-md-4">
                                <label for="filter_status" class="form-label">Estado</label>
                                <select class="form-select" id="filter_status">
                                    <option value="">Todos</option>
                                    <option value="presente">Presente</option>
                                    <option value="ausente">Ausente</option>
                                    <option value="pendiente">Pendiente</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen de asistencia -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-success">
                    <div class="card-body text-center">
                        <h4 class="text-success">8</h4>
                        <small class="text-muted">Presentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-danger">
                    <div class="card-body text-center">
                        <h4 class="text-danger">3</h4>
                        <small class="text-muted">Ausentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning">
                    <div class="card-body text-center">
                        <h4 class="text-warning">5</h4>
                        <small class="text-muted">Pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info">
                    <div class="card-body text-center">
                        <h4 class="text-info">68%</h4>
                        <small class="text-muted">Participación</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de participantes -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-users me-2"></i>Lista de Participantes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Cédula</th>
                                        <th>Apartamento</th>
                                        <th>Coeficiente</th>
                                        <th>Estado Pagos</th>
                                        <th>Asistencia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>Ana Patricia González</strong>
                                                <br><small class="text-muted">Propietario</small>
                                            </div>
                                        </td>
                                        <td>45678901</td>
                                        <td>101</td>
                                        <td>2.50%</td>
                                        <td><span class="badge bg-success">Al día</span></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Presente
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" title="Cambiar estado">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>Luis Fernando Martínez</strong>
                                                <br><small class="text-muted">Propietario</small>
                                            </div>
                                        </td>
                                        <td>56789012</td>
                                        <td>102</td>
                                        <td>2.75%</td>
                                        <td><span class="badge bg-success">Al día</span></td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Pendiente
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="markAttendance(2, true)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="markAttendance(2, false)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>Carmen Rosa Jiménez</strong>
                                                <br><small class="text-muted">Propietario</small>
                                            </div>
                                        </td>
                                        <td>67890123</td>
                                        <td>201</td>
                                        <td>2.50%</td>
                                        <td><span class="badge bg-warning">Mora</span></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Ausente
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" title="Cambiar estado">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <nav aria-label="Navegación de participantes">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para escanear QR -->
<div class="modal fade" id="scanQRModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>Escanear Código QR
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="bg-light p-4 rounded">
                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Funcionalidad de cámara QR se implementará aquí</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="manual_code" class="form-label">O ingresa el código manualmente:</label>
                    <input type="text" class="form-control" id="manual_code" placeholder="Código de participante">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Validar Código</button>
            </div>
        </div>
    </div>
</div>

<script>
function markAttendance(participantId, isPresent) {
    // Función para marcar asistencia
    const status = isPresent ? 'presente' : 'ausente';
    const badge = isPresent ? 
        '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Presente</span>' :
        '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Ausente</span>';
    
    // Aquí se implementaría la llamada AJAX para actualizar la BD
    console.log(`Marcando participante ${participantId} como ${status}`);
    
    // Actualizar la interfaz (simulado)
    showAlert(`Participante marcado como ${status}`, isPresent ? 'success' : 'warning');
}
</script>

<?php include '../views/layouts/footer.php'; ?>