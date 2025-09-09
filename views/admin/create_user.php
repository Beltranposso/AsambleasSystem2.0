<?php 
$title = 'Crear Usuario - Administrador'; 
$userRole = 'administrador'; 
require_once __DIR__ . '/../../core/helpers.php';
?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="<?= url('/admin/usuarios') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Usuarios
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-circle me-2"></i>Información del Usuario
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/usuarios/guardar') ?>" method="POST" id="createUserForm">
                            <?= csrf_field() ?>
                            
                            <div class="row">
                                <!-- Información Personal -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-user me-1"></i>Datos Personales
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">
                                            Nombre <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?= old('nombre') ?>" required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa el nombre.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="apellido" class="form-label">
                                            Apellido <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" 
                                               value="<?= old('apellido') ?>" required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa el apellido.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="cedula" class="form-label">
                                            Cédula <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="cedula" name="cedula" 
                                               value="<?= old('cedula') ?>" required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa la cédula.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                                               value="<?= old('telefono') ?>">
                                    </div>
                                </div>
                                
                                <!-- Información de Cuenta -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-key me-1"></i>Datos de Cuenta
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= old('email') ?>" required>
                                        <div class="invalid-feedback">
                                            Por favor ingresa un email válido.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            Contraseña <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                                <i class="fas fa-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Mínimo 8 caracteres.</div>
                                        <div class="invalid-feedback">
                                            La contraseña debe tener al menos 8 caracteres.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">
                                            Confirmar Contraseña <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div class="invalid-feedback">
                                            Las contraseñas no coinciden.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="rol" class="form-label">
                                            Rol <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <option value="">Seleccionar rol...</option>
                                            <option value="administrador" <?= old('rol') === 'administrador' ? 'selected' : '' ?>>
                                                Administrador
                                            </option>
                                            <option value="coordinador" <?= old('rol') === 'coordinador' ? 'selected' : '' ?>>
                                                Coordinador
                                            </option>
                                            <option value="operador" <?= old('rol') === 'operador' ? 'selected' : '' ?>>
                                                Operador
                                            </option>
                                            <option value="votante" <?= old('rol') === 'votante' ? 'selected' : '' ?>>
                                                Votante
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor selecciona un rol.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                                   <?= old('activo') ? 'checked' : 'checked' ?>>
                                            <label class="form-check-label" for="activo">
                                                Usuario activo
                                            </label>
                                        </div>
                                        <div class="form-text">Los usuarios inactivos no pueden acceder al sistema.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="<?= url('/admin/usuarios') ?>" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i>Cancelar
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary me-2" onclick="generatePassword()">
                                                <i class="fas fa-random me-1"></i>Generar Contraseña
                                            </button>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-save me-1"></i>Crear Usuario
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Panel de ayuda -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="fas fa-info-circle me-2"></i>Información de Roles
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-danger">
                                <i class="fas fa-crown me-1"></i>Administrador
                            </h6>
                            <small class="text-muted">
                                Acceso completo al sistema. Puede gestionar usuarios, asambleas y configuraciones.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-success">
                                <i class="fas fa-user-tie me-1"></i>Coordinador
                            </h6>
                            <small class="text-muted">
                                Coordina asambleas, gestiona participantes y controla votaciones.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-warning">
                                <i class="fas fa-user-cog me-1"></i>Operador
                            </h6>
                            <small class="text-muted">
                                Registra asistencia, verifica usuarios y gestiona coeficientes.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-info">
                                <i class="fas fa-user me-1"></i>Votante
                            </h6>
                            <small class="text-muted">
                                Participa en asambleas y puede ejercer su derecho al voto.
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="m-0">
                            <i class="fas fa-shield-alt me-2"></i>Seguridad
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Contraseña mínimo 8 caracteres</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Email único en el sistema</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <small>Cédula única por usuario</small>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-info text-info me-2"></i>
                                <small>Los usuarios pueden cambiar su contraseña después</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php 
$pageScripts = '
<script>
// Validación del formulario
document.getElementById("createUserForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    if (validateForm()) {
        // Deshabilitar botón de envío para evitar envíos duplicados
        const submitBtn = document.getElementById("submitBtn");
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = \'<i class="fas fa-spinner fa-spin me-1"></i>Creando Usuario...\';
        
        // Enviar formulario
        this.submit();
    }
});

function validateForm() {
    let isValid = true;
    
    // Limpiar errores previos
    clearAllErrors();
    
    // Validar campos requeridos
    const requiredFields = [
        { id: "nombre", message: "El nombre es requerido" },
        { id: "apellido", message: "El apellido es requerido" },
        { id: "cedula", message: "La cédula es requerida" },
        { id: "email", message: "El email es requerido" },
        { id: "password", message: "La contraseña es requerida" },
        { id: "rol", message: "El rol es requerido" }
    ];
    
    requiredFields.forEach(function(field) {
        const input = document.getElementById(field.id);
        if (!input.value.trim()) {
            showFieldError(input, field.message);
            isValid = false;
        }
    });
    
    // Validar email
    const email = document.getElementById("email");
    if (email.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            showFieldError(email, "Ingresa un email válido");
            isValid = false;
        }
    }
    
    // Validar cédula (solo números)
    const cedula = document.getElementById("cedula");
    if (cedula.value.trim()) {
        const cedulaRegex = /^[0-9]+$/;
        if (!cedulaRegex.test(cedula.value) || cedula.value.length < 6) {
            showFieldError(cedula, "La cédula debe contener solo números y tener mínimo 6 dígitos");
            isValid = false;
        }
    }
    
    // Validar contraseña
    const password = document.getElementById("password");
    if (password.value.trim()) {
        if (password.value.length < 8) {
            showFieldError(password, "La contraseña debe tener al menos 8 caracteres");
            isValid = false;
        }
    }
    
    // Validar confirmación de contraseña
    const confirmPassword = document.getElementById("confirm_password");
    if (password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, "Las contraseñas no coinciden");
        isValid = false;
    }
    
    // Validar teléfono si se proporciona
    const telefono = document.getElementById("telefono");
    if (telefono.value.trim()) {
        const telefonoRegex = /^[0-9\s\-\+\(\)]+$/;
        if (!telefonoRegex.test(telefono.value)) {
            showFieldError(telefono, "Formato de teléfono inválido");
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(input, message) {
    input.classList.add("is-invalid");
    
    // Buscar el div de feedback o crearlo
    let feedback = input.parentNode.querySelector(".invalid-feedback");
    if (!feedback) {
        feedback = document.createElement("div");
        feedback.className = "invalid-feedback";
        input.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
    feedback.style.display = "block";
}

function clearFieldError(input) {
    input.classList.remove("is-invalid");
    const feedback = input.parentNode.querySelector(".invalid-feedback");
    if (feedback) {
        feedback.style.display = "none";
    }
}

function clearAllErrors() {
    const inputs = document.querySelectorAll(".form-control, .form-select");
    inputs.forEach(clearFieldError);
}

function togglePassword() {
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.getElementById("toggleIcon");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.className = "fas fa-eye-slash";
    } else {
        passwordInput.type = "password";
        toggleIcon.className = "fas fa-eye";
    }
}

function generatePassword() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    // Asignar la contraseña generada
    document.getElementById("password").value = password;
    document.getElementById("confirm_password").value = password;
    
    // Limpiar errores de contraseña si los hay
    clearFieldError(document.getElementById("password"));
    clearFieldError(document.getElementById("confirm_password"));
    
    // Mostrar la contraseña generada en un modal o alert más elegante
    if (confirm("Contraseña generada: " + password + "\\n\\n¿Deseas copiarla al portapapeles?")) {
        // Copiar al portapapeles si es posible
        if (navigator.clipboard) {
            navigator.clipboard.writeText(password).then(function() {
                alert("Contraseña copiada al portapapeles");
            }).catch(function() {
                alert("No se pudo copiar automáticamente. Anota la contraseña: " + password);
            });
        } else {
            // Fallback para navegadores que no soportan clipboard API
            const textArea = document.createElement("textarea");
            textArea.value = password;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand("copy");
                alert("Contraseña copiada al portapapeles");
            } catch (err) {
                alert("No se pudo copiar automáticamente. Anota la contraseña: " + password);
            }
            document.body.removeChild(textArea);
        }
    }
}

// Validación en tiempo real
document.getElementById("email").addEventListener("blur", function() {
    if (this.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(this.value)) {
            showFieldError(this, "Formato de email inválido");
        } else {
            clearFieldError(this);
            // Aquí podrías hacer una validación AJAX para verificar si el email ya existe
            checkEmailExists(this.value);
        }
    }
});

document.getElementById("cedula").addEventListener("blur", function() {
    if (this.value.trim()) {
        const cedulaRegex = /^[0-9]+$/;
        if (!cedulaRegex.test(this.value) || this.value.length < 6) {
            showFieldError(this, "La cédula debe contener solo números y tener mínimo 6 dígitos");
        } else {
            clearFieldError(this);
            // Validación AJAX para verificar si la cédula ya existe
            checkCedulaExists(this.value);
        }
    }
});

document.getElementById("confirm_password").addEventListener("input", function() {
    const password = document.getElementById("password").value;
    if (this.value && this.value !== password) {
        showFieldError(this, "Las contraseñas no coinciden");
    } else {
        clearFieldError(this);
    }
});

// Funciones AJAX para validar duplicados
function checkEmailExists(email) {
    fetch("' . url('/admin/usuarios/check-email') . '", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            email: email,
            csrf_token: "' . csrf_token() . '"
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            showFieldError(document.getElementById("email"), "Este email ya está registrado");
        }
    })
    .catch(error => {
        console.log("Error verificando email:", error);
    });
}

function checkCedulaExists(cedula) {
    fetch("' . url('/admin/usuarios/check-cedula') . '", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            cedula: cedula,
            csrf_token: "' . csrf_token() . '"
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            showFieldError(document.getElementById("cedula"), "Esta cédula ya está registrada");
        }
    })
    .catch(error => {
        console.log("Error verificando cédula:", error);
    });
}

// Inicialización cuando se carga la página
document.addEventListener("DOMContentLoaded", function() {
    // Focus automático en el primer campo
    document.getElementById("nombre").focus();
    
    // Validación en tiempo real de contraseña
    document.getElementById("password").addEventListener("input", function() {
        const password = this.value;
        const confirmPassword = document.getElementById("confirm_password");
        
        if (password.length > 0 && password.length < 8) {
            showFieldError(this, "Mínimo 8 caracteres");
        } else {
            clearFieldError(this);
        }
        
        // Verificar confirmación de contraseña
        if (confirmPassword.value && confirmPassword.value !== password) {
            showFieldError(confirmPassword, "Las contraseñas no coinciden");
        } else if (confirmPassword.value === password) {
            clearFieldError(confirmPassword);
        }
    });
});
</script>
';
?>

<?php include '../layouts/footer.php'; ?>