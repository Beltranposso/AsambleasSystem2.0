<?php
// test_vote.php - Colocar en public/ para probar el envío de votos
session_start();

// Simular sesión de votante
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 4; // Ana Patricia del ejemplo
    $_SESSION['user_type'] = 'votante';
}

// API para obtener opciones de votación - MOVER AL INICIO
if (isset($_GET['get_options']) && isset($_GET['voting_id'])) {
    header('Content-Type: application/json');
    
    try {
        require_once '../core/Database.php';
        $db = new Database();
        $options = $db->fetchAll("SELECT id, opcion, descripcion FROM opciones_votacion WHERE votacion_id = ? ORDER BY orden_display", [$_GET['voting_id']]);
        echo json_encode($options);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

echo "<h1>Test de Envío de Voto</h1>";

// Verificar que podemos conectar a la base de datos
try {
    require_once '../core/Database.php';
    $db = new Database();
    echo "<p style='color: green'>✅ Conexión a BD exitosa</p>";
    
    // Obtener votaciones disponibles
    $votings = $db->fetchAll("SELECT v.*, a.titulo as asamblea_titulo FROM votaciones v JOIN asambleas a ON v.asamblea_id = a.id WHERE v.estado = 'abierta' LIMIT 3");
    
    if (empty($votings)) {
        echo "<p style='color: red'>❌ No hay votaciones abiertas para probar</p>";
        
        // Mostrar todas las votaciones para debug
        $allVotings = $db->fetchAll("SELECT v.*, a.titulo as asamblea_titulo FROM votaciones v JOIN asambleas a ON v.asamblea_id = a.id ORDER BY v.id DESC LIMIT 5");
        if (!empty($allVotings)) {
            echo "<h3>Votaciones existentes (para debug):</h3>";
            echo "<ul>";
            foreach ($allVotings as $v) {
                echo "<li>ID: {$v['id']} - {$v['titulo']} - Estado: {$v['estado']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: green'>✅ Encontradas " . count($votings) . " votaciones abiertas</p>";
        
        // Mostrar detalles de las votaciones
        echo "<h3>Votaciones disponibles:</h3>";
        foreach ($votings as $v) {
            echo "<p>• ID: {$v['id']} - {$v['titulo']} ({$v['asamblea_titulo']})</p>";
            
            // Mostrar opciones para esta votación
            $options = $db->fetchAll("SELECT id, opcion FROM opciones_votacion WHERE votacion_id = ? ORDER BY orden_display", [$v['id']]);
            if (!empty($options)) {
                echo "<ul>";
                foreach ($options as $opt) {
                    echo "<li>Opción ID: {$opt['id']} - {$opt['opcion']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange'>⚠️ No hay opciones para esta votación</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error de BD: " . $e->getMessage() . "</p>";
}

// Test del controlador
echo "<h2>Test del VoterController:</h2>";
try {
    require_once '../controllers/VoterController.php';
    $controller = new VoterController();
    echo "<p style='color: green'>✅ VoterController cargado correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error cargando VoterController: " . $e->getMessage() . "</p>";
}

// Verificar sesión
echo "<h3>Estado de la Sesión:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Formulario de test simple
echo "<h2>Test Manual de Voto:</h2>";
?>

<div style="background: #f9f9f9; padding: 20px; border-radius: 5px; max-width: 600px;">
    <h3>Enviar Voto de Prueba</h3>
    
    <?php if (!empty($votings)): ?>
        <form id="testVoteForm" method="post" action="">
            <div style="margin-bottom: 15px;">
                <label>Seleccionar Votación:</label>
                <select name="voting_id" id="votingSelect" required style="width: 100%; padding: 5px;">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($votings as $voting): ?>
                        <option value="<?php echo $voting['id']; ?>">
                            ID: <?php echo $voting['id']; ?> - <?php echo htmlspecialchars($voting['titulo']); ?> (<?php echo $voting['asamblea_titulo']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Seleccionar Opción:</label>
                <select name="option_id" id="optionSelect" required style="width: 100%; padding: 5px;">
                    <option value="">-- Primero selecciona una votación --</option>
                </select>
                <div id="loadingOptions" style="display: none; color: #666; font-style: italic;">Cargando opciones...</div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <input type="hidden" name="test_direct" value="1">
                <button type="submit" style="background: #3182ce; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                    Enviar Voto de Prueba (Directo)
                </button>
            </div>
        </form>
        
        <hr style="margin: 20px 0;">
        
        <h4>Test AJAX Independiente:</h4>
        <div>
            <label>Votación ID:</label>
            <input type="number" id="ajaxVotingId" placeholder="ej: 1" style="padding: 5px; margin-right: 10px;">
            
            <label>Opción ID:</label>
            <input type="number" id="ajaxOptionId" placeholder="ej: 1" style="padding: 5px; margin-right: 10px;">
            
            <button onclick="testAjaxVote()" style="background: #38a169; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Test AJAX Vote
            </button>
        </div>
        
        <div id="ajaxResult" style="margin-top: 15px; padding: 10px; background: #f0f0f0; border-radius: 5px; display: none;"></div>
        
    <?php else: ?>
        <p style="color: #d69e2e;">No hay votaciones disponibles para probar. Necesitas crear una votación abierta primero.</p>
        
        <h4>Crear votación de prueba rápida:</h4>
        <form method="post" action="">
            <input type="hidden" name="create_test_voting" value="1">
            <button type="submit" style="background: #d69e2e; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Crear Votación de Prueba
            </button>
        </form>
    <?php endif; ?>
</div>

<?php
// Crear votación de prueba si se solicita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test_voting'])) {
    try {
        // Insertar votación de prueba
        $votingId = $db->execute("INSERT INTO votaciones (titulo, descripcion, asamblea_id, estado, tipo_votacion) VALUES ('Prueba de Sistema', 'Votación para probar el sistema', 1, 'abierta', 'ordinaria')");
        
        if ($votingId) {
            // Agregar opciones
            $db->execute("INSERT INTO opciones_votacion (votacion_id, opcion, descripcion, orden_display) VALUES (?, 'Sí', 'Opción afirmativa', 1)", [$votingId]);
            $db->execute("INSERT INTO opciones_votacion (votacion_id, opcion, descripcion, orden_display) VALUES (?, 'No', 'Opción negativa', 2)", [$votingId]);
            $db->execute("INSERT INTO opciones_votacion (votacion_id, opcion, descripcion, orden_display) VALUES (?, 'Abstención', 'Me abstengo', 3)", [$votingId]);
            
            echo "<p style='color: green'>✅ Votación de prueba creada con ID: $votingId</p>";
            echo "<script>setTimeout(() => location.reload(), 1000);</script>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>❌ Error creando votación: " . $e->getMessage() . "</p>";
    }
}

// Procesar envío directo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_direct'])) {
    echo "<h3>Resultado del Test Directo:</h3>";
    
    try {
        echo "<p><strong>Datos enviados:</strong></p>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        
        echo "<p><strong>Datos de sesión:</strong></p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        // Simular el envío al controlador
        $controller = new VoterController();
        
        echo "<p><strong>Ejecutando procesarVoto()...</strong></p>";
        
        // Capturar la salida del controlador
        ob_start();
        $controller->procesarVoto();
        $output = ob_get_clean();
        
        echo "<p><strong>Salida del controlador:</strong></p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        
        if (isset($_SESSION['success'])) {
            echo "<p style='color: green'>✅ SUCCESS: " . $_SESSION['success'] . "</p>";
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            echo "<p style='color: red'>❌ ERROR: " . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red'>❌ Excepción en test: " . $e->getMessage() . "</p>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>

<script>
// Cargar opciones cuando se selecciona una votación - CORREGIDO
document.getElementById('votingSelect').addEventListener('change', async function() {
    const votingId = this.value;
    const optionSelect = document.getElementById('optionSelect');
    const loadingDiv = document.getElementById('loadingOptions');
    
    if (!votingId) {
        optionSelect.innerHTML = '<option value="">-- Primero selecciona una votación --</option>';
        return;
    }
    
    try {
        loadingDiv.style.display = 'block';
        optionSelect.innerHTML = '<option value="">Cargando...</option>';
        
        // URL corregida para obtener opciones
        const url = window.location.pathname + '?get_options=1&voting_id=' + votingId;
        console.log('Cargando opciones desde:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        const text = await response.text();
        console.log('Response text:', text);
        
        const data = JSON.parse(text);
        console.log('Opciones obtenidas:', data);
        
        optionSelect.innerHTML = '<option value="">-- Seleccionar opción --</option>';
        
        if (data.error) {
            optionSelect.innerHTML += '<option value="">Error: ' + data.error + '</option>';
        } else if (data.length === 0) {
            optionSelect.innerHTML += '<option value="">No hay opciones disponibles</option>';
        } else {
            data.forEach(option => {
                optionSelect.innerHTML += `<option value="${option.id}">ID: ${option.id} - ${option.opcion}</option>`;
            });
        }
        
        loadingDiv.style.display = 'none';
        
    } catch (error) {
        console.error('Error cargando opciones:', error);
        optionSelect.innerHTML = '<option value="">Error cargando opciones</option>';
        loadingDiv.style.display = 'none';
    }
});

async function testAjaxVote() {
    const resultDiv = document.getElementById('ajaxResult');
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p>🔄 Enviando voto AJAX...</p>';
    
    const votingId = document.getElementById('ajaxVotingId').value;
    const optionId = document.getElementById('ajaxOptionId').value;
    
    if (!votingId || !optionId) {
        resultDiv.innerHTML = '<p style="color: red;">❌ Ingresa tanto el ID de votación como el ID de opción</p>';
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('voting_id', votingId);
        formData.append('option_id', optionId);
        
        console.log('Enviando a:', '/Asambleas/public/votante/procesar-voto');
        console.log('Datos:', {voting_id: votingId, option_id: optionId});
        
        const response = await fetch('/Asambleas/public/votante/procesar-voto', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        
        const text = await response.text();
        console.log('Response text:', text);
        
        resultDiv.innerHTML = `
            <h4>Resultado AJAX:</h4>
            <p><strong>Status:</strong> ${response.status}</p>
            <p><strong>Content-Type:</strong> ${response.headers.get('content-type') || 'No definido'}</p>
            <h4>Respuesta completa:</h4>
            <pre style="background: white; padding: 10px; border-radius: 3px; max-height: 300px; overflow: auto;">${text}</pre>
        `;
        
        // Intentar parsear como JSON
        try {
            const jsonData = JSON.parse(text);
            resultDiv.innerHTML += `
                <h4>JSON parseado:</h4>
                <pre style="background: #e6fffa; padding: 10px; border-radius: 3px; color: #234e52;">${JSON.stringify(jsonData, null, 2)}</pre>
            `;
        } catch (e) {
            resultDiv.innerHTML += '<p><em>La respuesta no es JSON válido</em></p>';
        }
        
    } catch (error) {
        console.error('Error en AJAX:', error);
        resultDiv.innerHTML = `<p style="color: red;">❌ Error de red: ${error.message}</p>`;
    }
}
</script>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    overflow-x: auto;
    font-size: 0.875rem;
}

select, input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

h1, h2, h3 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
}
</style>