<?php $title = 'Crear Nueva Votación'; $userRole = 'coordinador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-plus-circle me-2"></i>Crear Nueva Votación
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="votaciones<?php echo isset($assembly) ? '?asamblea=' . $assembly['id'] : ''; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Votaciones
                </a>
            </div>
        </div>

        <?php if (isset($assembly)): ?>
            <!-- Información de la Asamblea -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información de la Asamblea
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?php echo htmlspecialchars($assembly['titulo']); ?></h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($assembly['conjunto_nombre']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-2"></i><?php echo date('d/m/Y H:i', strtotime($assembly['fecha_inicio'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-<?php echo $assembly['estado'] === 'activa' ? 'success' : 'warning'; ?> fs-6">
                                <?php echo ucfirst($assembly['estado']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Crear Votación -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Datos de la Votación
                    </h6>
                </div>
                <div class="card-body">
                    <!-- FORMULARIO SIMPLE Y DIRECTO -->
                    <form id="createVotingForm" method="POST" action="crear-votacion">
                        <!-- Campo oculto para assembly ID -->
                        <input type="hidden" name="asamblea_id" value="<?php echo $assembly['id']; ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label for="titulo" class="form-label fw-bold">
                                    Título de la Votación <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       name="titulo" 
                                       id="titulo" 
                                       required 
                                       maxlength="255"
                                       placeholder="Ej: Aprobación del presupuesto 2024">
                                <div class="form-text">Máximo 255 caracteres</div>
                            </div>
                            <div class="col-md-4">
                                <label for="tipo_votacion" class="form-label fw-bold">
                                    Tipo de Votación <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" name="tipo_votacion" id="tipo_votacion" required>
                                    <option value="ordinaria">Ordinaria</option>
                                    <option value="extraordinaria">Extraordinaria</option>
                                    <option value="unanimidad">Unanimidad</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-bold">Descripción</label>
                            <textarea class="form-control" 
                                      name="descripcion" 
                                      id="descripcion" 
                                      rows="3" 
                                      placeholder="Descripción detallada de lo que se va a votar (opcional)"></textarea>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="quorum_requerido" class="form-label fw-bold">
                                    Quórum Requerido (%)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       name="quorum_requerido" 
                                       id="quorum_requerido" 
                                       min="0" 
                                       max="100" 
                                       value="50" 
                                       step="0.01">
                                <div class="form-text">Porcentaje mínimo de participantes necesarios</div>
                            </div>
                            <div class="col-md-6">
                                <label for="mayoria_requerida" class="form-label fw-bold">
                                    Mayoría Requerida (%)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       name="mayoria_requerida" 
                                       id="mayoria_requerida" 
                                       min="0" 
                                       max="100" 
                                       value="50" 
                                       step="0.01">
                                <div class="form-text">Porcentaje necesario para aprobar</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Opciones de Votación <span class="text-danger">*</span>
                            </label>
                            <div id="opciones-container">
                                <!-- Opción 1 -->
                                <div class="input-group mb-3">
                                    <span class="input-group-text">1</span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="opciones[]" 
                                           placeholder="Primera opción" 
                                           required 
                                           maxlength="255">
                                    <button class="btn btn-outline-danger" 
                                            type="button" 
                                            onclick="removeOption(this)" 
                                            disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <!-- Opción 2 -->
                                <div class="input-group mb-3">
                                    <span class="input-group-text">2</span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="opciones[]" 
                                           placeholder="Segunda opción" 
                                           required 
                                           maxlength="255">
                                    <button class="btn btn-outline-danger" 
                                            type="button" 
                                            onclick="removeOption(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" 
                                    class="btn btn-outline-primary" 
                                    onclick="addOption()">
                                <i class="fas fa-plus me-2"></i>Agregar Opción
                            </button>
                            
                            <div class="form-text mt-2">
                                Mínimo 2 opciones, máximo 10. Cada opción máximo 255 caracteres.
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="votaciones<?php echo '?asamblea=' . $assembly['id']; ?>" 
                                       class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                    
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save me-2"></i>Crear Votación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- Sin Asamblea Seleccionada -->
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Asamblea no Encontrada</h5>
                    <p class="text-muted">
                        No se ha especificado una asamblea válida para crear la votación.
                    </p>
                    <a href="votaciones" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Votaciones
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
// JavaScript SUPER SIMPLE para la página de crear votación
// ====================================
// JAVASCRIPT CORREGIDO - CREAR VOTACIÓN
// ====================================
// Reemplazar TODO el JavaScript en crear_votacion.php

console.log('🚀 Página de crear votación cargada');

// Variables globales
let optionCounter = 2;
let formSubmitting = false; // Bandera para evitar envíos múltiples

// Función para agregar opciones
function addOption() {
    const container = document.getElementById('opciones-container');
    if (!container) {
        console.error('Container no encontrado');
        return;
    }
    
    const currentOptions = container.children.length;
    
    if (currentOptions >= 10) {
        alert('Máximo 10 opciones permitidas');
        return;
    }
    
    optionCounter++;
    
    const newOption = document.createElement('div');
    newOption.className = 'input-group mb-3';
    newOption.innerHTML = `
        <span class="input-group-text">${optionCounter}</span>
        <input type="text" 
               class="form-control" 
               name="opciones[]" 
               placeholder="Opción ${optionCounter}" 
               required 
               maxlength="255">
        <button class="btn btn-outline-danger" 
                type="button" 
                onclick="removeOption(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    
    container.appendChild(newOption);
    
    // Enfocar en el nuevo input
    const newInput = newOption.querySelector('input');
    if (newInput) {
        newInput.focus();
    }
    
    console.log('✅ Opción agregada:', optionCounter);
}

// Función para remover opciones
function removeOption(button) {
    const container = document.getElementById('opciones-container');
    if (!container) {
        console.error('Container no encontrado');
        return;
    }
    
    const currentOptions = container.children.length;
    
    if (currentOptions <= 2) {
        alert('Mínimo 2 opciones requeridas');
        return;
    }
    
    // Remover la opción
    button.parentElement.remove();
    
    // Reindexar las opciones restantes
    const options = container.querySelectorAll('.input-group');
    options.forEach((option, index) => {
        const span = option.querySelector('.input-group-text');
        const input = option.querySelector('input');
        const button = option.querySelector('button');
        
        if (span) span.textContent = index + 1;
        if (input) input.placeholder = `Opción ${index + 1}`;
        
        // Deshabilitar eliminar si solo quedan 2
        if (button && options.length === 2 && index === 0) {
            button.disabled = true;
        } else if (button) {
            button.disabled = false;
        }
    });
    
    console.log('✅ Opción removida, total:', options.length);
}

// Configurar tipo de votación
function setupVotingTypeHandler() {
    const tipoSelect = document.getElementById('tipo_votacion');
    if (!tipoSelect) return;
    
    tipoSelect.addEventListener('change', function() {
        const mayoriaInput = document.getElementById('mayoria_requerida');
        if (!mayoriaInput) return;
        
        switch(this.value) {
            case 'ordinaria':
                mayoriaInput.value = 50.01;
                break;
            case 'extraordinaria':
                mayoriaInput.value = 66.67;
                break;
            case 'unanimidad':
                mayoriaInput.value = 100;
                break;
        }
        
        console.log('🔄 Tipo de votación cambiado:', this.value);
    });
}

// VALIDACIONES SIMPLES - SIN BLOQUEO
function validateForm() {
    console.log('🔍 Validando formulario...');
    
    const titulo = document.getElementById('titulo');
    if (!titulo || !titulo.value.trim()) {
        alert('El título es obligatorio');
        if (titulo) titulo.focus();
        return false;
    }
    
    const opciones = document.querySelectorAll('input[name="opciones[]"]');
    const opcionesValidas = Array.from(opciones).filter(input => input.value.trim() !== '');
    
    if (opcionesValidas.length < 2) {
        alert('Se requieren mínimo 2 opciones');
        if (opciones[0]) opciones[0].focus();
        return false;
    }
    
    console.log('✅ Validación exitosa');
    return true;
}

// CONFIGURACIÓN DEL FORMULARIO - VERSION SIMPLE
function setupFormSubmission() {
    const form = document.getElementById('createVotingForm');
    if (!form) {
        console.error('❌ Formulario no encontrado');
        return;
    }
    
    console.log('📋 Configurando envío del formulario...');
    
    // IMPORTANTE: Usar onsubmit directo para evitar conflictos
    form.onsubmit = function(event) {
        console.log('🚨 SUBMIT INTERCEPTADO');
        
        // Evitar envíos múltiples
        if (formSubmitting) {
            console.log('⚠️ Formulario ya siendo enviado, ignorando...');
            event.preventDefault();
            return false;
        }
        
        // Validar formulario
        if (!validateForm()) {
            console.log('❌ Validación falló');
            event.preventDefault();
            return false;
        }
        
        // Marcar como enviando
        formSubmitting = true;
        
        console.log('✅ Formulario válido - Enviando...');
        
        // Cambiar botón para mostrar progreso
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
        }
        
        console.log('📤 Permitiendo envío normal del formulario');
        
        // PERMITIR EL ENVÍO NORMAL - NO PREVENIR
        return true;
    };
    
    console.log('✅ Event handler configurado correctamente');
}

// Función de test
function testForm() {
    console.log('🧪 Testing form...');
    
    const titulo = document.getElementById('titulo');
    const opciones = document.querySelectorAll('input[name="opciones[]"]');
    
    if (titulo) {
        titulo.value = 'Test Votación ' + Date.now();
        console.log('📝 Título llenado:', titulo.value);
    }
    
    if (opciones[0]) {
        opciones[0].value = 'Opción Sí';
        console.log('📝 Opción 1 llenada');
    }
    
    if (opciones[1]) {
        opciones[1].value = 'Opción No';
        console.log('📝 Opción 2 llenada');
    }
    
    console.log('✅ Test completado');
}

// Test de envío del botón
function testBotonSubmit() {
    console.log('🧪 Test del botón submit...');
    
    // Llenar formulario
    testForm();
    
    // Disparar click en el botón
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        console.log('🖱️ Haciendo clic en el botón...');
        submitButton.click();
    } else {
        console.error('❌ Botón submit no encontrado');
    }
}

// Reset del formulario si hay problemas
function resetForm() {
    formSubmitting = false;
    
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save me-2"></i>Crear Votación';
    }
    
    console.log('🔄 Formulario reseteado');
}

// Debug del estado del formulario
function debugFormState() {
    const form = document.getElementById('createVotingForm');
    
    console.log('📊 Estado del formulario:');
    console.log('  - Formulario encontrado:', !!form);
    console.log('  - formSubmitting:', formSubmitting);
    console.log('  - Action:', form ? form.action : 'N/A');
    console.log('  - Method:', form ? form.method : 'N/A');
    console.log('  - onsubmit definido:', !!form?.onsubmit);
    
    const submitButton = document.querySelector('button[type="submit"]');
    console.log('  - Botón submit encontrado:', !!submitButton);
    console.log('  - Botón deshabilitado:', submitButton ? submitButton.disabled : 'N/A');
    
    return {
        form: !!form,
        submitting: formSubmitting,
        button: !!submitButton,
        buttonDisabled: submitButton ? submitButton.disabled : null
    };
}

// Hacer funciones disponibles globalmente
window.testForm = testForm;
window.testBotonSubmit = testBotonSubmit;
window.resetForm = resetForm;
window.debugFormState = debugFormState;

// INICIALIZACIÓN SIMPLE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando página de crear votación...');
    
    // Solo configurar lo esencial
    setupVotingTypeHandler();
    setupFormSubmission();
    
    console.log('✅ Configuración completada');
    
    // Debug inicial
    setTimeout(() => {
        const state = debugFormState();
        console.log('📊 Estado inicial:', state);
    }, 1000);
});

// Auto-reset después de 30 segundos por si hay problemas
setTimeout(() => {
    if (formSubmitting) {
        console.log('⏰ Auto-reset después de 30 segundos');
        resetForm();
    }
}, 30000);
// ====================================
// SOLUCIÓN PARA CLICK DEL BOTÓN
// ====================================
// Agregar este código AL FINAL del JavaScript en crear_votacion.php

console.log('🔧 Configurando solución para click del botón...');

// Función para configurar el botón directamente
function setupButtonClickHandler() {
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (!submitButton) {
        console.error('❌ Botón submit no encontrado');
        return;
    }
    
    console.log('🖱️ Configurando click handler del botón...');
    
    // Agregar event listener al botón directamente
    submitButton.addEventListener('click', function(event) {
        console.log('🚨 CLICK EN BOTÓN INTERCEPTADO');
        
        // Prevenir el comportamiento por defecto
        event.preventDefault();
        
        // Evitar clics múltiples
        if (formSubmitting) {
            console.log('⚠️ Ya enviando formulario, ignorando click...');
            return;
        }
        
        // Validar formulario
        if (!validateForm()) {
            console.log('❌ Validación falló en click');
            return;
        }
        
        // Marcar como enviando
        formSubmitting = true;
        
        console.log('✅ Validación OK - Procesando envío...');
        
        // Cambiar botón
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
        
        // Enviar formulario manualmente
        const form = document.getElementById('createVotingForm');
        if (form) {
            console.log('📤 Enviando formulario manualmente...');
            form.submit();
        } else {
            console.error('❌ Formulario no encontrado para envío manual');
            resetForm();
        }
    });
    
    console.log('✅ Click handler configurado');
}

// Test específico del click handler
function testClickHandler() {
    console.log('🧪 Test del click handler...');
    
    // Llenar formulario
    testForm();
    
    // Disparar evento click manualmente
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        console.log('🖱️ Disparando evento click...');
        
        const clickEvent = new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window
        });
        
        submitButton.dispatchEvent(clickEvent);
    } else {
        console.error('❌ Botón no encontrado para test click');
    }
}

// Función para envío directo sin validaciones
function envioDirectoSinValidaciones() {
    console.log('🚀 ENVÍO DIRECTO SIN VALIDACIONES...');
    
    // Llenar formulario
    testForm();
    
    // Enviar inmediatamente
    const form = document.getElementById('createVotingForm');
    if (form) {
        console.log('📤 Enviando directo...');
        form.submit();
    } else {
        console.error('❌ Formulario no encontrado');
    }
}

// Función para debug completo del botón
function debugBoton() {
    const submitButton = document.querySelector('button[type="submit"]');
    
    console.log('🔍 DEBUG DEL BOTÓN:');
    console.log('  - Botón encontrado:', !!submitButton);
    
    if (submitButton) {
        console.log('  - Tipo:', submitButton.type);
        console.log('  - Deshabilitado:', submitButton.disabled);
        console.log('  - innerHTML:', submitButton.innerHTML);
        console.log('  - Form asociado:', submitButton.form ? 'SÍ' : 'NO');
        
        // Verificar event listeners
        const listeners = getEventListeners ? getEventListeners(submitButton) : null;
        console.log('  - Event listeners:', listeners ? Object.keys(listeners) : 'NO DETECTADO');
        
        // Verificar si está dentro del formulario
        const form = submitButton.closest('form');
        console.log('  - Dentro de form:', !!form);
        console.log('  - Form ID:', form ? form.id : 'N/A');
    }
    
    return submitButton;
}

// Hacer funciones disponibles globalmente
window.setupButtonClickHandler = setupButtonClickHandler;
window.testClickHandler = testClickHandler;
window.envioDirectoSinValidaciones = envioDirectoSinValidaciones;
window.debugBoton = debugBoton;

// Configurar automáticamente cuando se carga
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para que todo se configure
    setTimeout(() => {
        console.log('🔧 Configurando click handler automáticamente...');
        setupButtonClickHandler();
        
        // Debug del botón
        debugBoton();
    }, 2000);
});

console.log('🎯 Nuevas funciones disponibles:');
console.log('   - testClickHandler()');
console.log('   - envioDirectoSinValidaciones()');
console.log('   - debugBoton()');
console.log('   - setupButtonClickHandler()');
</script>

<?php include '../views/layouts/footer.php'; ?> 