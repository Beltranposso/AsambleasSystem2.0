<?php 
$title = 'Editar Conjunto - Administrador'; 
$userRole = 'administrador'; 
require_once __DIR__ . '/../../core/helpers.php';

// Recuperar datos del formulario si hay errores, sino usar los del conjunto
$formData = $_SESSION['form_data'] ?? $conjunto ?? [];
unset($_SESSION['form_data']);
?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-edit me-2"></i>Editar Conjunto: <?= htmlspecialchars($conjunto['nombre'] ?? 'N/A') ?>
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <a href="<?= url('/admin/conjuntos') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver a Conjuntos
                    </a>
                    <button type="button" class="btn btn-outline-info dropdown-toggle dropdown-toggle-split" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button class="dropdown-item" onclick="showConjuntoStats(<?= $conjunto['id'] ?>)">
                                <i class="fas fa-chart-pie me-2"></i>Ver Estadísticas
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" onclick="showConjuntoUsers(<?= $conjunto['id'] ?>, '<?= htmlspecialchars($conjunto['nombre']) ?>')">
                                <i class="fas fa-users me-2"></i>Gestionar Residentes
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= url('/admin/asambleas?conjunto=' . $conjunto['id']) ?>">
                                <i class="fas fa-gavel me-2"></i>Ver Asambleas
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Alertas y mensajes -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Información del Conjunto
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/conjuntos/guardar') ?>" method="POST" id="conjuntoForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $conjunto['id'] ?>">
                            
                            <!-- Información básica -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        Nombre del Conjunto <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($formData['nombre'] ?? '') ?>" 
                                           placeholder="Ej: Conjunto Residencial Los Pinos" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nit" class="form-label">NIT</label>
                                    <input type="text" class="form-control" id="nit" name="nit" 
                                           value="<?= htmlspecialchars($formData['nit'] ?? '') ?>" 
                                           placeholder="Ej: 900123456-7">
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Solo números y guiones</small>
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="direccion" class="form-label">
                                        Dirección <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" 
                                           value="<?= htmlspecialchars($formData['direccion'] ?? '') ?>" 
                                           placeholder="Ej: Calle 123 # 45-67, Barrio Centro" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="ciudad" class="form-label">
                                        Ciudad <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                           value="<?= htmlspecialchars($formData['ciudad'] ?? '') ?>" 
                                           placeholder="Ej: Bogotá" required>
                                </div>
                            </div>

                            <!-- Contacto -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?= htmlspecialchars($formData['telefono'] ?? '') ?>" 
                                           placeholder="Ej: (601) 234-5678">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email de Contacto</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>" 
                                           placeholder="Ej: administracion@conjunto.com">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Información técnica -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_unidades" class="form-label">
                                        Total de Unidades <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="total_unidades" name="total_unidades" 
                                               value="<?= htmlspecialchars($formData['total_unidades'] ?? '') ?>" 
                                               min="1" max="9999" placeholder="Ej: 150" required>
                                        <?php if (($conjunto['total_residentes'] ?? 0) > 0): ?>
                                            <span class="input-group-text" data-bs-toggle="tooltip" 
                                                  title="Actualmente hay <?= $conjunto['total_residentes'] ?> residentes registrados">
                                                <i class="fas fa-info-circle text-info"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">Número total de apartamentos/casas</small>
                                    <?php if (($conjunto['total_residentes'] ?? 0) > 0): ?>
                                        <br><small class="text-info">
                                            <i class="fas fa-users me-1"></i>
                                            <?= $conjunto['total_residentes'] ?> residentes registrados actualmente
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr class="my-4">
                            
                            <!-- Información adicional -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Información del Sistema
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="small text-muted mb-1">
                                                <strong>Fecha de Registro:</strong>
                                                <?= date('d/m/Y H:i', strtotime($conjunto['created_at'] ?? 'now')) ?>
                                            </p>
                                            <p class="small text-muted mb-1">
                                                <strong>Última Actualización:</strong>
                                                <?= date('d/m/Y H:i', strtotime($conjunto['updated_at'] ?? $conjunto['created_at'] ?? 'now')) ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="small text-muted mb-1">
                                                <strong>Total Asambleas:</strong>
                                                <span class="badge bg-info"><?= $conjunto['total_asambleas'] ?? 0 ?></span>
                                            </p>
                                            <p class="small text-muted mb-1">
                                                <strong>Residentes Registrados:</strong>
                                                <span class="badge bg-success"><?= $conjunto['total_residentes'] ?? 0 ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Los campos marcados con <span class="text-danger">*</span> son obligatorios
                                    </span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-secondary me-2" onclick="history.back()">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-1"></i>Actualizar Conjunto
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sección de acciones peligrosas -->
                <div class="card shadow mt-4 border-danger">
                    <div class="card-header bg-danger text-white py-3">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Zona de Peligro
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Las siguientes acciones son irreversibles. Por favor, proceda con precaución.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="resetConjuntoData(<?= $conjunto['id'] ?>)"
                                            <?= ($conjunto['total_residentes'] ?? 0) == 0 ? 'disabled' : '' ?>>
                                        <i class="fas fa-undo me-1"></i>Reiniciar Datos
                                    </button>
                                </div>
                                <small class="text-muted">Elimina todos los residentes y asambleas</small>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteConjunto(<?= $conjunto['id'] ?>)"
                                            <?= (($conjunto['total_residentes'] ?? 0) > 0 || ($conjunto['total_asambleas'] ?? 0) > 0) ? 'disabled' : '' ?>>
                                        <i class="fas fa-trash me-1"></i>Eliminar Conjunto
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <?php if (($conjunto['total_residentes'] ?? 0) > 0 || ($conjunto['total_asambleas'] ?? 0) > 0): ?>
                                        No se puede eliminar (tiene residentes o asambleas)
                                    <?php else: ?>
                                        Elimina completamente el conjunto
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de estadísticas -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-chart-bar me-2"></i>Estadísticas Actuales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="text-success h4 mb-1"><?= $conjunto['total_unidades'] ?? 0 ?></div>
                                <small class="text-muted">Unidades Totales</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-info h4 mb-1"><?= $conjunto['total_residentes'] ?? 0 ?></div>
                                <small class="text-muted">Residentes</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-primary h4 mb-1"><?= $conjunto['total_asambleas'] ?? 0 ?></div>
                                <small class="text-muted">Asambleas</small>
                            </div>
                            <div class="col-6 mb-3">
                                <?php 
                                $ocupacion = ($conjunto['total_unidades'] ?? 0) > 0 ? 
                                    round((($conjunto['total_residentes'] ?? 0) / $conjunto['total_unidades']) * 100, 1) : 0;
                                ?>
                                <div class="text-warning h4 mb-1"><?= $ocupacion ?>%</div>
                                <small class="text-muted">Ocupación</small>
                            </div>
                        </div>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" style="width: <?= $ocupacion ?>%"></div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="button" class="btn btn-sm btn-outline-success" 
                                    onclick="showConjuntoStats(<?= $conjunto['id'] ?>)">
                                <i class="fas fa-chart-pie me-1"></i>Ver Estadísticas Detalladas
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-users me-2"></i>Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm" 
                                    onclick="showCreateUserModal(<?= $conjunto['id'] ?>, '<?= htmlspecialchars($conjunto['nombre']) ?>')">
                                <i class="fas fa-user-plus me-1"></i>Agregar Residente
                            </button>
                            
                            <button type="button" class="btn btn-outline-info btn-sm" 
                                    onclick="showConjuntoUsers(<?= $conjunto['id'] ?>, '<?= htmlspecialchars($conjunto['nombre']) ?>')">
                                <i class="fas fa-list me-1"></i>Ver Todos los Residentes
                            </button>
                            
                            <hr class="my-2">
                            
                            <a href="<?= url('/admin/asambleas/crear?conjunto=' . $conjunto['id']) ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-gavel me-1"></i>Nueva Asamblea
                            </a>
                            
                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                    onclick="generateConjuntoReport(<?= $conjunto['id'] ?>)">
                                <i class="fas fa-file-pdf me-1"></i>Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-history me-2"></i>Historial Reciente
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-sm text-muted">
                            <!-- Aquí podrías mostrar actividad reciente del conjunto -->
                            <div class="mb-2">
                                <i class="fas fa-circle text-success me-2" style="font-size: 8px;"></i>
                                Conjunto actualizado por última vez el <?= date('d/m/Y', strtotime($conjunto['updated_at'] ?? $conjunto['created_at'] ?? 'now')) ?>
                            </div>
                            
                            <?php if (($conjunto['total_asambleas'] ?? 0) > 0): ?>
                                <div class="mb-2">
                                    <i class="fas fa-circle text-info me-2" style="font-size: 8px;"></i>
                                    <?= $conjunto['total_asambleas'] ?> asamblea(s) registrada(s)
                                </div>
                            <?php endif; ?>
                            
                            <?php if (($conjunto['total_residentes'] ?? 0) > 0): ?>
                                <div class="mb-2">
                                    <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                    <?= $conjunto['total_residentes'] ?> residente(s) activo(s)
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('conjuntoForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalId = <?= $conjunto['id'] ?>;
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Limpiar validaciones previas
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Validar nombre
        const nombre = document.getElementById('nombre');
        if (nombre.value.trim().length < 3) {
            nombre.classList.add('is-invalid');
            nombre.nextElementSibling.textContent = 'El nombre debe tener al menos 3 caracteres';
            isValid = false;
        }
        
        // Validar dirección
        const direccion = document.getElementById('direccion');
        if (direccion.value.trim().length < 5) {
            direccion.classList.add('is-invalid');
            direccion.nextElementSibling.textContent = 'La dirección debe tener al menos 5 caracteres';
            isValid = false;
        }
        
        // Validar ciudad
        const ciudad = document.getElementById('ciudad');
        if (ciudad.value.trim().length < 2) {
            ciudad.classList.add('is-invalid');
            ciudad.nextElementSibling.textContent = 'La ciudad debe tener al menos 2 caracteres';
            isValid = false;
        }
        
        // Validar email si se proporciona
        const email = document.getElementById('email');
        if (email.value && !isValidEmail(email.value)) {
            email.classList.add('is-invalid');
            email.nextElementSibling.textContent = 'El formato del email no es válido';
            isValid = false;
        }
        
        // Validar NIT si se proporciona
        const nit = document.getElementById('nit');
        if (nit.value && !isValidNIT(nit.value)) {
            nit.classList.add('is-invalid');
            nit.nextElementSibling.textContent = 'El NIT solo debe contener números y guiones';
            isValid = false;
        }
        
        // Validar total de unidades
        const totalUnidades = document.getElementById('total_unidades');
        const currentResidents = <?= $conjunto['total_residentes'] ?? 0 ?>;
        
        if (totalUnidades.value < 1 || totalUnidades.value > 9999) {
            totalUnidades.classList.add('is-invalid');
            totalUnidades.nextElementSibling.textContent = 'El total de unidades debe estar entre 1 y 9999';
            isValid = false;
        } else if (totalUnidades.value < currentResidents) {
            totalUnidades.classList.add('is-invalid');
            totalUnidades.nextElementSibling.textContent = `No puede ser menor a ${currentResidents} (residentes actuales)`;
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            showAlert('error', 'Por favor corrija los errores en el formulario');
            return;
        }
        
        // Deshabilitar botón para evitar envíos múltiples
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
    });
    
    // Validación en tiempo real del nombre
    document.getElementById('nombre').addEventListener('blur', function() {
        const nombre = this.value.trim();
        if (nombre.length >= 3) {
            checkConjuntoName(nombre, originalId);
        }
    });
    
    // Validación en tiempo real del NIT
    document.getElementById('nit').addEventListener('blur', function() {
        const nit = this.value.trim();
        if (nit.length > 0) {
            checkConjuntoNIT(nit, originalId);
        }
    });
    
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Función para validar email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Función para validar NIT
function isValidNIT(nit) {
    const re = /^[0-9\-]+$/;
    return re.test(nit);
}

// Función para verificar disponibilidad del nombre (excluyendo el actual)
function checkConjuntoName(nombre, excludeId) {
    fetch('<?= url('/admin/conjuntos/check-name') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ nombre: nombre, exclude_id: excludeId })
    })
    .then(response => response.json())
    .then(data => {
        const nombreInput = document.getElementById('nombre');
        if (data.exists) {
            nombreInput.classList.add('is-invalid');
            nombreInput.nextElementSibling.textContent = 'Ya existe otro conjunto con este nombre';
        } else {
            nombreInput.classList.remove('is-invalid');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para verificar disponibilidad del NIT (excluyendo el actual)
function checkConjuntoNIT(nit, excludeId) {
    fetch('<?= url('/admin/conjuntos/check-nit') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ nit: nit, exclude_id: excludeId })
    })
    .then(response => response.json())
    .then(data => {
        const nitInput = document.getElementById('nit');
        if (data.exists) {
            nitInput.classList.add('is-invalid');
            nitInput.nextElementSibling.textContent = 'Ya existe otro conjunto con este NIT';
        } else {
            nitInput.classList.remove('is-invalid');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para mostrar alertas
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas ${iconClass} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('main');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Función para reiniciar datos del conjunto
function resetConjuntoData(conjuntoId) {
    if (confirm('¿Está seguro de que quiere reiniciar todos los datos del conjunto?\n\nEsto eliminará:\n• Todos los residentes\n• Todas las asambleas\n• Todo el historial\n\nEsta acción NO se puede deshacer.')) {
        if (confirm('Esta acción es IRREVERSIBLE. ¿Confirma que desea continuar?')) {
            fetch(`<?= url('/admin/conjuntos/reset/') ?>${conjuntoId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    csrf_token: '<?= csrf_token() ?>',
                    confirmation: 'RESET_CONFIRMED'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Datos del conjunto reiniciados correctamente');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert('error', data.message || 'Error al reiniciar los datos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error al reiniciar los datos del conjunto');
            });
        }
    }
}

// Función para eliminar conjunto
function deleteConjunto(conjuntoId) {
    if (confirm('¿Está seguro de que quiere ELIMINAR COMPLETAMENTE este conjunto?\n\nEsta acción es IRREVERSIBLE y eliminará:\n• El conjunto y toda su información\n• Todos los residentes asociados\n• Todas las asambleas\n• Todo el historial')) {
        if (confirm('CONFIRMACIÓN FINAL: Esta acción NO se puede deshacer. ¿Desea continuar?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `<?= url('/admin/conjuntos/eliminar/') ?>${conjuntoId}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= csrf_token() ?>';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Función para mostrar estadísticas del conjunto
function showConjuntoStats(conjuntoId) {
    fetch(`<?= url('/admin/conjuntos/estadisticas/') ?>${conjuntoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = createStatsModal(data.stats);
                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                
                modal.addEventListener('hidden.bs.modal', () => {
                    modal.remove();
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error al cargar estadísticas');
        });
}

// Función para crear modal de estadísticas
function createStatsModal(stats) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-pie me-2"></i>Estadísticas de ${stats.nombre}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.total_unidades}</h3>
                                    <p>Unidades Totales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.total_residentes}</h3>
                                    <p>Residentes Registrados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.total_asambleas}</h3>
                                    <p>Asambleas Realizadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-warning text-white mb-3">
                                <div class="card-body text-center">
                                    <h3>${stats.ocupacion}%</h3>
                                    <p>Nivel de Ocupación</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Distribución por Tipo de Residente</h6>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: ${stats.propietarios_pct}%">
                                    Propietarios (${stats.propietarios})
                                </div>
                                <div class="progress-bar bg-info" style="width: ${stats.arrendatarios_pct}%">
                                    Arrendatarios (${stats.arrendatarios})
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Estado de Pagos</h6>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: ${stats.al_dia_pct}%">
                                    Al día (${stats.al_dia})
                                </div>
                                <div class="progress-bar bg-warning" style="width: ${stats.mora_pct}%">
                                    En mora (${stats.mora})
                                </div>
                                <div class="progress-bar bg-danger" style="width: ${stats.suspendido_pct}%">
                                    Suspendidos (${stats.suspendido})
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="generateConjuntoReport(${stats.id})">
                        <i class="fas fa-file-pdf me-1"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    `;
    return modal;
}

// Función para mostrar modal de crear usuario
function showCreateUserModal(conjuntoId, conjuntoName) {
    // Aquí se podría cargar un modal desde la vista de conjuntos principal
    window.location.href = `<?= url('/admin/conjuntos') ?>?action=create_user&conjunto_id=${conjuntoId}`;
}

// Función para mostrar usuarios del conjunto
function showConjuntoUsers(conjuntoId, conjuntoName) {
    // Aquí se podría cargar un modal desde la vista de conjuntos principal
    window.location.href = `<?= url('/admin/conjuntos') ?>?action=manage_users&conjunto_id=${conjuntoId}`;
}

// Función para generar reporte de conjunto
function generateConjuntoReport(conjuntoId) {
    showAlert('info', 'Generando reporte del conjunto...');
    
    setTimeout(() => {
        window.open(`<?= url('/admin/conjuntos/reporte/') ?>${conjuntoId}`, '_blank');
    }, 1000);
}

// Formatear campos automáticamente
document.getElementById('nit').addEventListener('input', function() {
    let value = this.value.replace(/[^0-9\-]/g, '');
    this.value = value;
});

document.getElementById('telefono').addEventListener('input', function() {
    let value = this.value.replace(/[^0-9\(\)\-\s]/g, '');
    this.value = value;
});

document.getElementById('ciudad').addEventListener('blur', function() {
    this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();
});

document.getElementById('nombre').addEventListener('blur', function() {
    this.value = this.value.replace(/\w\S*/g, (txt) => 
        txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
    );
});
</script>

<?php include '../views/layouts/footer.php'; ?>