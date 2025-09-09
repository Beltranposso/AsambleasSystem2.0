<?php 
$title = 'Crear Conjunto - Administrador'; 
$userRole = 'administrador'; 
require_once __DIR__ . '/../../core/helpers.php';

// Recuperar datos del formulario si hay errores
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-building me-2"></i>Crear Nuevo Conjunto Residencial
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="<?= url('/admin/conjuntos') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Conjuntos
                </a>
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
                                    <label for="nit" class="form-label">NIT (Opcional)</label>
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
                                    <input type="number" class="form-control" id="total_unidades" name="total_unidades" 
                                           value="<?= htmlspecialchars($formData['total_unidades'] ?? '') ?>" 
                                           min="1" max="9999" placeholder="Ej: 150" required>
                                    <small class="text-muted">Número total de apartamentos/casas</small>
                                </div>
                            </div>

                            <hr class="my-4">
                            
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
                                 <button type="button" class="btn btn-primary" id="submitBtn" onclick="submitForm()">
    <i class="fas fa-save me-1"></i>Crear Conjunto
</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de ayuda -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-lightbulb me-2"></i>Consejos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-sm">
                            <div class="mb-3">
                                <div class="font-weight-bold text-success mb-1">
                                    <i class="fas fa-check-circle me-1"></i>Información requerida
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 ms-3">
                                    <li>• Nombre completo del conjunto</li>
                                    <li>• Dirección exacta</li>
                                    <li>• Ciudad donde se ubica</li>
                                    <li>• Número total de unidades</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <div class="font-weight-bold text-info mb-1">
                                    <i class="fas fa-info-circle me-1"></i>Información opcional
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 ms-3">
                                    <li>• NIT para documentos oficiales</li>
                                    <li>• Teléfono de contacto</li>
                                    <li>• Email de administración</li>
                                </ul>
                            </div>
                            
                            <div class="mb-0">
                                <div class="font-weight-bold text-warning mb-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Importante
                                </div>
                                <ul class="list-unstyled small text-muted mb-0 ms-3">
                                    <li>• El nombre debe ser único</li>
                                    <li>• El NIT debe ser válido si se proporciona</li>
                                    <li>• Las unidades determinarán el cálculo de quórum</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-route me-2"></i>Siguientes Pasos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-sm text-muted">
                            Después de crear el conjunto podrás:
                            <ol class="ms-3 mt-2">
                                <li>Registrar residentes y propietarios</li>
                                <li>Configurar coeficientes de participación</li>
                                <li>Crear coordinadores para el conjunto</li>
                                <li>Programar la primera asamblea</li>
                                <li>Gestionar votaciones y decisiones</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Eliminar el event listener del submit del form
    
    // Validación en tiempo real del nombre
    document.getElementById('nombre').addEventListener('blur', function() {
        const nombre = this.value.trim();
        if (nombre.length >= 3) {
            checkConjuntoName(nombre);
        }
    });
    
    // Validación en tiempo real del NIT
    document.getElementById('nit').addEventListener('blur', function() {
        const nit = this.value.trim();
        if (nit.length > 0) {
            checkConjuntoNIT(nit);
        }
    });
});

// Nueva función para manejar el submit del formulario con onclick
function submitForm() {
    const form = document.getElementById('conjuntoForm');
    const submitBtn = document.getElementById('submitBtn');
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
    if (totalUnidades.value < 1 || totalUnidades.value > 9999) {
        totalUnidades.classList.add('is-invalid');
        totalUnidades.nextElementSibling.textContent = 'El total de unidades debe estar entre 1 y 9999';
        isValid = false;
    }
    
    if (!isValid) {
        showAlert('error', 'Por favor corrija los errores en el formulario');
        return false;
    }
    
    // Deshabilitar botón para evitar envíos múltiples
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creando...';
    
    // Enviar el formulario
    form.submit();
}

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

// Función para verificar disponibilidad del nombre
function checkConjuntoName(nombre) {
    fetch('<?= url('/admin/conjuntos/check-name') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(response => response.json())
    .then(data => {
        const nombreInput = document.getElementById('nombre');
        if (data.exists) {
            nombreInput.classList.add('is-invalid');
            nombreInput.nextElementSibling.textContent = 'Ya existe un conjunto con este nombre';
        } else {
            nombreInput.classList.remove('is-invalid');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para verificar disponibilidad del NIT
function checkConjuntoNIT(nit) {
    fetch('<?= url('/admin/conjuntos/check-nit') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ nit: nit })
    })
    .then(response => response.json())
    .then(data => {
        const nitInput = document.getElementById('nit');
        if (data.exists) {
            nitInput.classList.add('is-invalid');
            nitInput.nextElementSibling.textContent = 'Ya existe un conjunto con este NIT';
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
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Formatear NIT automáticamente
document.getElementById('nit').addEventListener('input', function() {
    let value = this.value.replace(/[^0-9\-]/g, '');
    this.value = value;
});

// Formatear teléfono automáticamente
document.getElementById('telefono').addEventListener('input', function() {
    let value = this.value.replace(/[^0-9\(\)\-\s]/g, '');
    this.value = value;
});

// Capitalizar primera letra de ciudad
document.getElementById('ciudad').addEventListener('blur', function() {
    this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase();
});

// Capitalizar primera letra de cada palabra en nombre
document.getElementById('nombre').addEventListener('blur', function() {
    this.value = this.value.replace(/\w\S*/g, (txt) => 
        txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
    );
});
</script>

<?php include '../views/layouts/footer.php'; ?>