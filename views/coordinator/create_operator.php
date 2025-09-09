<?php $title = 'Crear Operador'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-user-plus me-2"></i>Crear Nuevo Operador</h1>
            <a href="/coordinator/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-edit me-2"></i>Información del Operador</h5>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="invalid-feedback">
                                        El nombre es requerido
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido *</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                    <div class="invalid-feedback">
                                        El apellido es requerido
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cedula" class="form-label">Cédula *</label>
                                    <input type="text" class="form-control" id="cedula" name="cedula" required>
                                    <div class="invalid-feedback">
                                        La cédula es requerida
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="3001234567">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico *</label>
                                    <input type="email" class="form-control" id="correo" name="correo" required>
                                    <div class="invalid-feedback">
                                        El correo electrónico es requerido y debe ser válido
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contrasena" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" required minlength="6">
                                    <div class="invalid-feedback">
                                        La contraseña debe tener al menos 6 caracteres
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña *</label>
                                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                                    <div class="invalid-feedback">
                                        Las contraseñas deben coincidir
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6><i class="fas fa-cog me-2"></i>Permisos y Asignaciones</h6>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Asambleas Asignadas</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="asamblea1">
                                        <label class="form-check-label" for="asamblea1">
                                            Asamblea Ordinaria Marzo - Los Álamos
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="2" id="asamblea2">
                                        <label class="form-check-label" for="asamblea2">
                                            Asamblea Extraordinaria - Torre del Sol
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="3" id="asamblea3">
                                        <label class="form-check-label" for="asamblea3">
                                            Asamblea Villa Verde - Abril
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Permisos Especiales</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="edit_voters" id="perm_edit_voters">
                                        <label class="form-check-label" for="perm_edit_voters">
                                            Editar información de votantes
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="validate_attendance" id="perm_validate" checked>
                                        <label class="form-check-label" for="perm_validate">
                                            Validar asistencia (por defecto)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="generate_reports" id="perm_reports">
                                        <label class="form-check-label" for="perm_reports">
                                            Generar reportes básicos
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="/coordinator/dashboard" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Crear Operador
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle me-2"></i>Información</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <p><strong>Funciones del Operador:</strong></p>
                            <ul>
                                <li>Validar asistencia de votantes</li>
                                <li>Modificar datos de votantes existentes</li>
                                <li>Generar reportes básicos</li>
                                <li>Verificar documentos de identidad</li>
                            </ul>
                            
                            <p><strong>Requisitos de Contraseña:</strong></p>
                            <ul>
                                <li>Mínimo 6 caracteres</li>
                                <li>Se recomienda usar números y letras</li>
                                <li>El operador puede cambiarla después</li>
                            </ul>
                        </small>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-users me-2"></i>Operadores Existentes</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Carlos Sánchez</h6>
                                        <p class="mb-1 text-muted small">operador@ejemplo.com</p>
                                    </div>
                                    <span class="badge bg-success">Activo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Validación personalizada para confirmar contraseña
document.getElementById('confirmar_contrasena').addEventListener('input', function() {
    const password = document.getElementById('contrasena').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../views/layouts/footer.php'; ?>