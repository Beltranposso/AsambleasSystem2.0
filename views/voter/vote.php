<?php
// views/voter/vote.php
$pageTitle = 'Votar - ' . htmlspecialchars($voting['titulo']);
include '../views/layouts/header.php';
?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-vote-yea"></i> Ejercer mi Voto</h1>
            <p>Tu participación es importante para la toma de decisiones</p>
        </div>
        <div class="header-actions">
            <a href="/votante/mis-votos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="voting-container">
        <!-- Información de la Votación -->
        <div class="voting-info-card">
            <div class="voting-header">
                <h2><?php echo htmlspecialchars($voting['titulo']); ?></h2>
                <div class="voting-meta">
                    <span class="meta-item">
                        <i class="fas fa-building"></i>
                        <?php echo htmlspecialchars($voting['asamblea_titulo']); ?>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('d/m/Y H:i', strtotime($voting['fecha_inicio'])); ?>
                    </span>
                    <?php if (!empty($voting['fecha_fin'])): ?>
                        <span class="meta-item">
                            <i class="fas fa-clock"></i>
                            Cierra: <?php echo date('d/m/Y H:i', strtotime($voting['fecha_fin'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($voting['descripcion'])): ?>
                <div class="voting-description">
                    <h3>Descripción:</h3>
                    <p><?php echo nl2br(htmlspecialchars($voting['descripcion'])); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Información del Votante -->
            <div class="voter-info">
                <h3>Tu Información de Voto:</h3>
                <div class="voter-details">
                    <div class="detail-item">
                        <span class="label">Votante:</span>
                        <span class="value"><?php echo htmlspecialchars($voterInfo['nombres'] . ' ' . $voterInfo['apellidos']); ?></span>
                    </div>
                </div>
                
                <!-- Botones de Acción -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large" id="submitVote" disabled>
                        <i class="fas fa-vote-yea"></i>
                        Enviar Mi Voto
                    </button>
                    <button type="button" class="btn btn-secondary btn-large" onclick="cancelVote()">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final -->
<div id="finalConfirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmación Final de Voto</h2>
        </div>
        <div class="modal-body">
            <div class="final-confirmation">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>¿Estás seguro de tu decisión?</h3>
                <div class="vote-summary">
                    <p><strong>Votación:</strong> <?php echo htmlspecialchars($voting['titulo']); ?></p>
                    <p><strong>Tu opción seleccionada:</strong> <span id="selectedOption"></span></p>
                    <p><strong>Tu coeficiente de voto:</strong> <?php echo number_format($voterInfo['coeficiente_participacion'] ?? 0, 2); ?>%</p>
                </div>
                <div class="warning-text">
                    <p><i class="fas fa-info-circle"></i> <strong>Importante:</strong> Una vez enviado tu voto, no podrás cambiarlo. Esta acción es irreversible.</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" id="confirmFinalVote">
                        <i class="fas fa-check"></i> Sí, Confirmar mi Voto
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelFinalVote">
                        <i class="fas fa-arrow-left"></i> Revisar mi Selección
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    padding: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.header-content h1 {
    margin: 0 0 0.5rem 0;
    color: #1a202c;
    display: flex;
    align-items: center;
}

.header-content i {
    margin-right: 0.75rem;
    color: #3182ce;
}

.header-content p {
    margin: 0;
    color: #718096;
}

.voting-container {
    display: grid;
    gap: 2rem;
}

.voting-info-card, .voting-form-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.voting-header h2 {
    margin: 0 0 1rem 0;
    color: #1a202c;
    font-size: 1.5rem;
}

.voting-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.meta-item {
    display: flex;
    align-items: center;
    color: #718096;
    font-size: 0.875rem;
}

.meta-item i {
    margin-right: 0.5rem;
    color: #cbd5e0;
}

.voting-description {
    background: #f7fafc;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.voting-description h3 {
    margin: 0 0 1rem 0;
    color: #1a202c;
    font-size: 1.1rem;
}

.voting-description p {
    margin: 0;
    color: #4a5568;
    line-height: 1.6;
}

.voter-info {
    background: #e6fffa;
    border: 1px solid #81e6d9;
    border-radius: 8px;
    padding: 1.5rem;
}

.voter-info h3 {
    margin: 0 0 1rem 0;
    color: #234e52;
    font-size: 1.1rem;
}

.voter-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-item .label {
    font-weight: 500;
    color: #234e52;
}

.detail-item .value {
    font-weight: 600;
    color: #1a202c;
}

.form-header {
    margin-bottom: 2rem;
    text-align: center;
}

.form-header h3 {
    margin: 0 0 0.5rem 0;
    color: #1a202c;
    font-size: 1.3rem;
}

.form-header p {
    margin: 0;
    color: #718096;
}

.voting-options {
    margin-bottom: 2rem;
}

.voting-option {
    margin-bottom: 1rem;
}

.voting-option input[type="radio"] {
    display: none;
}

.option-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.option-label:hover {
    border-color: #cbd5e0;
    background: #f7fafc;
}

.voting-option input[type="radio"]:checked + .option-label {
    border-color: #3182ce;
    background: #ebf8ff;
}

.option-content {
    display: flex;
    align-items: center;
    flex: 1;
}

.option-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #4a5568;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 1rem;
    flex-shrink: 0;
}

.voting-option input[type="radio"]:checked + .option-label .option-number {
    background: #3182ce;
    color: white;
}

.option-text h4 {
    margin: 0 0 0.25rem 0;
    color: #1a202c;
    font-size: 1.1rem;
}

.option-text p {
    margin: 0;
    color: #718096;
    font-size: 0.875rem;
}

.option-check {
    width: 24px;
    height: 24px;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    transition: all 0.2s;
}

.voting-option input[type="radio"]:checked + .option-label .option-check {
    border-color: #3182ce;
    background: #3182ce;
    color: white;
}

.voting-confirmation {
    background: #fffbf0;
    border: 1px solid #f6d55c;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.confirmation-box h4 {
    margin: 0 0 1rem 0;
    color: #1a202c;
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.checkbox-group:last-child {
    margin-bottom: 0;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 0.75rem;
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.checkbox-group label {
    color: #4a5568;
    line-height: 1.5;
    cursor: pointer;
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.btn i {
    margin-right: 0.5rem;
}

.btn-primary {
    background: #3182ce;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2c5aa0;
}

.btn-primary:disabled {
    background: #cbd5e0;
    color: #a0aec0;
    cursor: not-allowed;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

.no-options {
    text-align: center;
    padding: 3rem;
    color: #718096;
}

.no-options i {
    font-size: 3rem;
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
}

.modal-header {
    padding: 2rem 2rem 1rem 2rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
    margin: 0;
    color: #1a202c;
    text-align: center;
}

.modal-body {
    padding: 2rem;
}

.final-confirmation {
    text-align: center;
}

.warning-icon {
    font-size: 3rem;
    color: #d69e2e;
    margin-bottom: 1rem;
}

.final-confirmation h3 {
    margin: 0 0 1.5rem 0;
    color: #1a202c;
}

.vote-summary {
    background: #f7fafc;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
}

.vote-summary p {
    margin: 0.5rem 0;
    color: #4a5568;
}

.warning-text {
    background: #fffbf0;
    border: 1px solid #f6d55c;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 2rem;
}

.warning-text p {
    margin: 0;
    color: #744210;
    font-size: 0.875rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .voting-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .voter-details {
        grid-template-columns: 1fr;
    }
    
    .option-content {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .option-number {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('votingForm');
    const submitBtn = document.getElementById('submitVote');
    const confirmVoteCheckbox = document.getElementById('confirm_vote');
    const confirmIdentityCheckbox = document.getElementById('confirm_identity');
    const radioButtons = document.querySelectorAll('input[name="option_id"]');
    const modal = document.getElementById('finalConfirmModal');
    const confirmFinalBtn = document.getElementById('confirmFinalVote');
    const cancelFinalBtn = document.getElementById('cancelFinalVote');
    
    // Función para validar el formulario
    function validateForm() {
        const optionSelected = document.querySelector('input[name="option_id"]:checked');
        const confirmVoteChecked = confirmVoteCheckbox.checked;
        const confirmIdentityChecked = confirmIdentityCheckbox.checked;
        
        if (optionSelected && confirmVoteChecked && confirmIdentityChecked) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }
    
    // Agregar event listeners
    radioButtons.forEach(radio => {
        radio.addEventListener('change', validateForm);
    });
    
    confirmVoteCheckbox.addEventListener('change', validateForm);
    confirmIdentityCheckbox.addEventListener('change', validateForm);
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedOption = document.querySelector('input[name="option_id"]:checked');
        if (selectedOption) {
            const optionText = selectedOption.parentNode.querySelector('.option-text h4').textContent;
            document.getElementById('selectedOption').textContent = optionText;
            modal.style.display = 'block';
        }
    });
    
    // Confirmación final
    confirmFinalBtn.addEventListener('click', function() {
        form.submit();
    });
    
    cancelFinalBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Validación inicial
    validateForm();
});

function cancelVote() {
    if (confirm('¿Estás seguro de que quieres cancelar? Perderás tu selección actual.')) {
        window.location.href = '/votante/mis-votos';
    }
}
</script>

<?php include '../views/layouts/footer.php'; ?>
        