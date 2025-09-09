<?php $title = 'Crear Asamblea'; $userRole = 'administrador'; ?>
<?php include '../views/layouts/header.php'; ?>

<div class="row">
    <?php include '../views/layouts/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="fas fa-plus me-2"></i>Crear Nueva Asamblea</h1>
            <a href="/Asambleas/public/admin/asambleas" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <!-- Mostrar mensajes de error o éxito -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form id="assemblyForm" action="/Asambleas/public/admin/asambleas/guardar" method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="titulo" class="form-label">Título de la Asamblea *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required 
                                           value="<?php echo isset($_SESSION['form_data']['titulo']) ? htmlspecialchars($_SESSION['form_data']['titulo']) : ''; ?>">
                                    <div class="invalid-feedback">
                                        El título es requerido
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                              placeholder="Descripción de la asamblea (opcional)"><?php echo isset($_SESSION['form_data']['descripcion']) ? htmlspecialchars($_SESSION['form_data']['descripcion']) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="conjunto_id" class="form-label">Conjunto *</label>
                                    <select class="form-select" id="conjunto_id" name="conjunto_id" required>
                                        <option value="">Seleccionar conjunto...</option>
                                        <?php if (isset($conjuntos) && is_array($conjuntos)): ?>
                                            <?php foreach ($conjuntos as $conjunto): ?>
                                                <option value="<?php echo $conjunto['id']; ?>" 
                                                        <?php echo (isset($_SESSION['form_data']['conjunto_id']) && $_SESSION['form_data']['conjunto_id'] == $conjunto['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($conjunto['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No hay conjuntos disponibles</option>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Debe seleccionar un conjunto
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_asamblea" class="form-label">Tipo de Asamblea *</label>
                                    <select class="form-select" id="tipo_asamblea" name="tipo_asamblea" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="ordinaria" <?php echo (isset($_SESSION['form_data']['tipo_asamblea']) && $_SESSION['form_data']['tipo_asamblea'] == 'ordinaria') ? 'selected' : ''; ?>>Ordinaria</option>
                                        <option value="extraordinaria" <?php echo (isset($_SESSION['form_data']['tipo_asamblea']) && $_SESSION['form_data']['tipo_asamblea'] == 'extraordinaria') ? 'selected' : ''; ?>>Extraordinaria</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Debe seleccionar el tipo de asamblea
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="coordinador_id" class="form-label">Coordinador *</label>
                                    <select class="form-select" id="coordinador_id" name="coordinador_id" required>
                                        <option value="">Seleccionar coordinador...</option>
                                        <?php if (isset($coordinadores) && is_array($coordinadores)): ?>
                                            <?php foreach ($coordinadores as $coordinador): ?>
                                                <option value="<?php echo $coordinador['id']; ?>"
                                                        <?php echo (isset($_SESSION['form_data']['coordinador_id']) && $_SESSION['form_data']['coordinador_id'] == $coordinador['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($coordinador['nombre'] . ' ' . $coordinador['apellido']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No hay coordinadores disponibles</option>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Debe seleccionar un coordinador
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="programada" <?php echo (isset($_SESSION['form_data']['estado']) && $_SESSION['form_data']['estado'] == 'programada') ? 'selected' : 'selected'; ?>>Programada</option>
                                        <option value="activa" <?php echo (isset($_SESSION['form_data']['estado']) && $_SESSION['form_data']['estado'] == 'activa') ? 'selected' : ''; ?>>Activa</option>
                                        <option value="suspendida" <?php echo (isset($_SESSION['form_data']['estado']) && $_SESSION['form_data']['estado'] == 'suspendida') ? 'selected' : ''; ?>>Suspendida</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha y Hora de Inicio *</label>
                                    <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required
                                           value="<?php echo isset($_SESSION['form_data']['fecha_inicio']) ? $_SESSION['form_data']['fecha_inicio'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        La fecha de inicio es requerida
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha y Hora de Finalización *</label>
                                    <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" required
                                           value="<?php echo isset($_SESSION['form_data']['fecha_fin']) ? $_SESSION['form_data']['fecha_fin'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        La fecha de finalización es requerida
                                    </div>
                                    <small class="text-muted">Debe ser posterior a la fecha de inicio</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quorum_minimo" class="form-label">Quórum Mínimo (%)</label>
                                    <input type="number" class="form-control" id="quorum_minimo" name="quorum_minimo" 
                                           min="0" max="100" value="<?php echo isset($_SESSION['form_data']['quorum_minimo']) ? $_SESSION['form_data']['quorum_minimo'] : '50'; ?>" step="0.01">
                                    <small class="text-muted">Porcentaje mínimo de asistencia requerido</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="link_reunion" class="form-label">Link de Reunión Virtual</label>
                                    <input type="url" class="form-control" id="link_reunion" name="link_reunion" 
                                           placeholder="https://meet.google.com/xxx-xxx-xxx"
                                           value="<?php echo isset($_SESSION['form_data']['link_reunion']) ? htmlspecialchars($_SESSION['form_data']['link_reunion']) : ''; ?>">
                                    <small class="text-muted">Opcional - para reuniones virtuales o híbridas</small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="/Asambleas/public/admin/asambleas" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="button" class="btn btn-primary" id="submitBtn" onclick="submitForm()">
                                    <i class="fas fa-save me-2"></i>
                                    <span class="btn-text">Crear Asamblea</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Procesando...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle me-2"></i>Información</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <p><strong>Tipos de Asamblea:</strong></p>
                            <ul>
                                <li><strong>Ordinaria:</strong> Asambleas regulares programadas según los estatutos</li>
                                <li><strong>Extraordinaria:</strong> Asambleas especiales para temas urgentes o específicos</li>
                            </ul>
                            
                            <p><strong>Quórum:</strong> Porcentaje mínimo de asistencia requerido para que la asamblea sea válida y pueda tomar decisiones.</p>
                            
                            <p><strong>Estados:</strong></p>
                            <ul>
                                <li><strong>Programada:</strong> Asamblea creada pero aún no iniciada</li>
                                <li><strong>Activa:</strong> Asamblea en curso</li>
                                <li><strong>Suspendida:</strong> Asamblea pausada temporalmente</li>
                            </ul>
                        </small>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-lightbulb me-2"></i>Consejos</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <ul>
                                <li>Planifique la asamblea con al menos 15 días de anticipación</li>
                                <li>Verifique que el coordinador asignado esté disponible</li>
                                <li>El quórum típico es del 50% para asambleas ordinarias</li>
                                <li>Para asambleas extraordinarias considere un quórum mayor</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('assemblyForm');
    const submitBtn = document.getElementById('submitBtn');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    // Establecer fecha mínima como hoy
    const now = new Date();
    const minDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    fechaInicio.min = minDate;
    fechaFin.min = minDate;
    
    // Función para mostrar errores
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
    }
    
    // Función para limpiar errores
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
    }
    
    // Validar fechas
    function validateDates() {
        clearFieldError(fechaFin);
        
        if (fechaInicio.value && fechaFin.value) {
            const startDate = new Date(fechaInicio.value);
            const endDate = new Date(fechaFin.value);
            
            if (endDate <= startDate) {
                showFieldError(fechaFin, 'La fecha de finalización debe ser posterior a la fecha de inicio');
                return false;
            }
        }
        return true;
    }
    
    // Event listeners para fechas
    fechaInicio.addEventListener('change', function() {
        fechaFin.min = this.value;
        validateDates();
    });
    
    fechaFin.addEventListener('change', validateDates);
    
    // Auto-completar quórum según tipo
    document.getElementById('tipo_asamblea').addEventListener('change', function() {
        const quorumField = document.getElementById('quorum_minimo');
        if (this.value === 'ordinaria') {
            quorumField.value = '50';
        } else if (this.value === 'extraordinaria') {
            quorumField.value = '60';
        }
    });
    
    // Función global para manejar el envío del formulario
    window.submitForm = function() {
        console.log('submitForm() llamada');
        
        let isValid = true;
        let firstErrorField = null;
        
        // ========================================
        // VALIDAR TÍTULO
        // ========================================
        const titulo = document.getElementById('titulo');
        if (!titulo.value.trim()) {
            showFieldError(titulo, 'El título de la asamblea es obligatorio');
            isValid = false;
            if (!firstErrorField) firstErrorField = titulo;
        } else if (titulo.value.trim().length < 10) {
            showFieldError(titulo, 'El título debe tener al menos 10 caracteres');
            isValid = false;
            if (!firstErrorField) firstErrorField = titulo;
        } else if (titulo.value.trim().length > 200) {
            showFieldError(titulo, 'El título no puede exceder 200 caracteres');
            isValid = false;
            if (!firstErrorField) firstErrorField = titulo;
        } else {
            clearFieldError(titulo);
        }
        
        // ========================================
        // VALIDAR CONJUNTO
        // ========================================
        const conjunto = document.getElementById('conjunto_id');
        if (!conjunto.value || conjunto.value === '') {
            showFieldError(conjunto, 'Debe seleccionar un conjunto residencial');
            isValid = false;
            if (!firstErrorField) firstErrorField = conjunto;
        } else {
            clearFieldError(conjunto);
        }
        
        // ========================================
        // VALIDAR TIPO DE ASAMBLEA
        // ========================================
        const tipoAsamblea = document.getElementById('tipo_asamblea');
        if (!tipoAsamblea.value || tipoAsamblea.value === '') {
            showFieldError(tipoAsamblea, 'Debe seleccionar el tipo de asamblea');
            isValid = false;
            if (!firstErrorField) firstErrorField = tipoAsamblea;
        } else {
            clearFieldError(tipoAsamblea);
        }
        
        // ========================================
        // VALIDAR COORDINADOR
        // ========================================
        const coordinador = document.getElementById('coordinador_id');
        if (!coordinador.value || coordinador.value === '') {
            showFieldError(coordinador, 'Debe asignar un coordinador a la asamblea');
            isValid = false;
            if (!firstErrorField) firstErrorField = coordinador;
        } else {
            clearFieldError(coordinador);
        }
        
        // ========================================
        // VALIDAR FECHA DE INICIO
        // ========================================
        const fechaInicio = document.getElementById('fecha_inicio');
        if (!fechaInicio.value) {
            showFieldError(fechaInicio, 'La fecha y hora de inicio son obligatorias');
            isValid = false;
            if (!firstErrorField) firstErrorField = fechaInicio;
        } else {
            const fechaInicioDate = new Date(fechaInicio.value);
            const ahora = new Date();
            // Permitir 5 minutos de tolerancia
            ahora.setMinutes(ahora.getMinutes() - 5);
            
            if (fechaInicioDate < ahora) {
                showFieldError(fechaInicio, 'La fecha de inicio no puede ser en el pasado');
                isValid = false;
                if (!firstErrorField) firstErrorField = fechaInicio;
            } else {
                clearFieldError(fechaInicio);
            }
        }
        
        // ========================================
        // VALIDAR FECHA DE FIN
        // ========================================
        const fechaFin = document.getElementById('fecha_fin');
        if (!fechaFin.value) {
            showFieldError(fechaFin, 'La fecha y hora de finalización son obligatorias');
            isValid = false;
            if (!firstErrorField) firstErrorField = fechaFin;
        } else {
            clearFieldError(fechaFin);
        }
        
        // ========================================
        // VALIDAR RELACIÓN ENTRE FECHAS
        // ========================================
        if (fechaInicio.value && fechaFin.value) {
            const fechaInicioDate = new Date(fechaInicio.value);
            const fechaFinDate = new Date(fechaFin.value);
            
            if (fechaFinDate <= fechaInicioDate) {
                showFieldError(fechaFin, 'La fecha de finalización debe ser posterior a la fecha de inicio');
                isValid = false;
                if (!firstErrorField) firstErrorField = fechaFin;
            } else {
                // Verificar que no sea el mismo día (mínimo 30 minutos)
                const diferenciaMilisegundos = fechaFinDate.getTime() - fechaInicioDate.getTime();
                const diferenciaMinutos = diferenciaMilisegundos / (1000 * 60);
                
                if (diferenciaMinutos < 30) {
                    showFieldError(fechaFin, 'La asamblea debe durar al menos 30 minutos');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = fechaFin;
                } else if (diferenciaMinutos > (8 * 60)) { // Más de 8 horas
                    showFieldError(fechaFin, 'La asamblea no puede durar más de 8 horas');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = fechaFin;
                }
            }
        }
        
        // ========================================
        // VALIDAR QUÓRUM
        // ========================================
        const quorum = document.getElementById('quorum_minimo');
        const quorumValue = parseFloat(quorum.value);
        
        if (isNaN(quorumValue)) {
            showFieldError(quorum, 'El quórum debe ser un número válido');
            isValid = false;
            if (!firstErrorField) firstErrorField = quorum;
        } else if (quorumValue < 1) {
            showFieldError(quorum, 'El quórum mínimo debe ser al menos 1%');
            isValid = false;
            if (!firstErrorField) firstErrorField = quorum;
        } else if (quorumValue > 100) {
            showFieldError(quorum, 'El quórum no puede ser mayor al 100%');
            isValid = false;
            if (!firstErrorField) firstErrorField = quorum;
        } else {
            // Validación adicional según tipo de asamblea
            const tipo = tipoAsamblea.value;
            if (tipo === 'ordinaria' && quorumValue < 30) {
                showFieldError(quorum, 'Para asambleas ordinarias se recomienda un quórum mínimo del 30%');
                isValid = false;
                if (!firstErrorField) firstErrorField = quorum;
            } else if (tipo === 'extraordinaria' && quorumValue < 50) {
                showFieldError(quorum, 'Para asambleas extraordinarias se recomienda un quórum mínimo del 50%');
                isValid = false;
                if (!firstErrorField) firstErrorField = quorum;
            } else {
                clearFieldError(quorum);
            }
        }
        
        // ========================================
        // VALIDAR LINK DE REUNIÓN (OPCIONAL)
        // ========================================
        const linkReunion = document.getElementById('link_reunion');
        if (linkReunion.value.trim()) {
            const urlPattern = /^https?:\/\/.+/i;
            if (!urlPattern.test(linkReunion.value.trim())) {
                showFieldError(linkReunion, 'El link debe ser una URL válida (http:// o https://)');
                isValid = false;
                if (!firstErrorField) firstErrorField = linkReunion;
            } else {
                clearFieldError(linkReunion);
            }
        } else {
            clearFieldError(linkReunion);
        }
        
        // ========================================
        // VALIDAR DESCRIPCIÓN (OPCIONAL)
        // ========================================
        const descripcion = document.getElementById('descripcion');
        if (descripcion.value.trim() && descripcion.value.trim().length > 1000) {
            showFieldError(descripcion, 'La descripción no puede exceder 1000 caracteres');
            isValid = false;
            if (!firstErrorField) firstErrorField = descripcion;
        } else {
            clearFieldError(descripcion);
        }
        
        // ========================================
        // PROCESAR RESULTADO DE VALIDACIÓN
        // ========================================
        if (isValid) {
            console.log('✅ Formulario válido, enviando...');
            
            // Mostrar estado de carga
            const btnText = submitBtn.querySelector('.btn-text');
            const btnSpinner = submitBtn.querySelector('.btn-spinner');
            
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            submitBtn.disabled = true;
            
            // Enviar formulario
            try {
                form.submit();
            } catch (error) {
                console.error('❌ Error al enviar formulario:', error);
                // Restaurar botón en caso de error
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
                submitBtn.disabled = false;
                alert('Error al enviar el formulario. Por favor, intente nuevamente.');
            }
        } else {
            console.log('❌ Formulario inválido - ' + document.querySelectorAll('.is-invalid').length + ' errores encontrados');
            
            // Scroll al primer error y enfocarlo
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Enfocar después del scroll
                setTimeout(() => {
                    firstErrorField.focus();
                }, 500);
            }
            
            // Mostrar alerta con resumen de errores
            const errores = document.querySelectorAll('.is-invalid').length;
            alert(`⚠️ Por favor corrija los ${errores} error(es) en el formulario antes de continuar.`);
        }
    };
    
    // Limpiar form_data de sesión si existe
    <?php if (isset($_SESSION['form_data'])): ?>
        console.log('Datos del formulario cargados desde sesión');
    <?php endif; ?>
});
</script>

<?php 
// Limpiar datos del formulario después de mostrarlos
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
include '../views/layouts/footer.php'; 
?>