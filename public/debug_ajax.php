<?php
// debug_ajax.php - Crear en la carpeta public para probar las rutas AJAX
session_start();

// Simular una sesión de votante para testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 4; // ID de Ana Patricia del ejemplo
    $_SESSION['user_type'] = 'votante';
}

echo "<h1>Debug AJAX - Votante</h1>";

// 1. Verificar que las rutas AJAX estén funcionando
echo "<h2>1. Test de Rutas AJAX:</h2>";

$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>";
echo "<h3>URLs a probar:</h3>";
echo "<p><strong>Detalles de votación:</strong> <code>POST {$base_url}/votante/ajax/voting-details</code></p>";
echo "<p><strong>Estado de voto:</strong> <code>POST {$base_url}/votante/ajax/check-vote-status</code></p>";
echo "</div>";

// 2. Probar la conexión directa al controlador
echo "<h2>2. Test Directo del Controlador:</h2>";

try {
    require_once '../controllers/VoterController.php';
    require_once '../core/Database.php';
    
    $controller = new VoterController();
    echo "<p style='color: green'>✅ VoterController cargado correctamente</p>";
    
    // Probar método de votaciones disponibles
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getAvailableVotings');
    $method->setAccessible(true);
    $votings = $method->invoke($controller);
    
    echo "<h3>Votaciones disponibles encontradas:</h3>";
    echo "<pre>" . print_r($votings, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error: " . $e->getMessage() . "</p>";
}

// 3. Test manual de AJAX
echo "<h2>3. Test Manual de AJAX:</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Datos POST recibidos:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if (isset($_POST['test_ajax'])) {
        try {
            require_once '../controllers/VoterController.php';
            $controller = new VoterController();
            
            // Simular llamada AJAX
            $_POST['voting_id'] = $_POST['voting_id'] ?? 1;
            
            // Capturar la salida
            ob_start();
            $controller->getVotingDetailsAjax();
            $output = ob_get_clean();
            
            echo "<h3>Respuesta del controlador:</h3>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
            
            // Verificar si es JSON válido
            $decoded = json_decode($output, true);
            if ($decoded !== null) {
                echo "<p style='color: green'>✅ JSON válido</p>";
                echo "<pre>" . print_r($decoded, true) . "</pre>";
            } else {
                echo "<p style='color: red'>❌ JSON inválido</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red'>❌ Error en test AJAX: " . $e->getMessage() . "</p>";
        }
    }
}

// 4. Verificar datos de sesión
echo "<h2>4. Estado de la Sesión:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// 5. Verificar base de datos
echo "<h2>5. Test de Base de Datos:</h2>";
try {
    require_once '../core/Database.php';
    $db = new Database();
    
    // Verificar votaciones
    $votings = $db->fetchAll("SELECT * FROM votaciones LIMIT 3");
    echo "<h3>Votaciones en BD:</h3>";
    echo "<pre>" . print_r($votings, true) . "</pre>";
    
    // Verificar opciones de votación
    if (!empty($votings)) {
        $options = $db->fetchAll("SELECT * FROM opciones_votacion WHERE votacion_id = ? LIMIT 5", [$votings[0]['id']]);
        echo "<h3>Opciones para votación ID " . $votings[0]['id'] . ":</h3>";
        echo "<pre>" . print_r($options, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error de BD: " . $e->getMessage() . "</p>";
}

?>

<!-- Formulario de test -->
<h2>6. Test Manual:</h2>
<form method="post" style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
    <h3>Probar AJAX manualmente:</h3>
    <label>
        ID de Votación:
        <input type="number" name="voting_id" value="1" style="margin-left: 10px; padding: 5px;">
    </label>
    <br><br>
    <button type="submit" name="test_ajax" value="1" style="background: #3182ce; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Probar getVotingDetailsAjax
    </button>
</form>

<!-- Test JavaScript -->
<h2>7. Test JavaScript (AJAX Real):</h2>
<div style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
    <button onclick="testAjax()" style="background: #38a169; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Test AJAX con JavaScript
    </button>
    <div id="ajaxResult" style="margin-top: 15px;"></div>
</div>

<script>
async function testAjax() {
    const resultDiv = document.getElementById('ajaxResult');
    resultDiv.innerHTML = '<p>🔄 Enviando petición AJAX...</p>';
    
    try {
        const response = await fetch('<?php echo $base_url; ?>/votante/ajax/voting-details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'voting_id=1'
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const text = await response.text();
        console.log('Response text:', text);
        
        resultDiv.innerHTML = `
            <h4>Respuesta del servidor:</h4>
            <p><strong>Status:</strong> ${response.status}</p>
            <p><strong>Content-Type:</strong> ${response.headers.get('Content-Type') || 'No definido'}</p>
            <h4>Contenido:</h4>
            <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow: auto;">${text}</pre>
        `;
        
        // Intentar parsear como JSON
        try {
            const data = JSON.parse(text);
            resultDiv.innerHTML += `
                <h4>JSON parseado:</h4>
                <pre style="background: #e6fffa; padding: 10px; border-radius: 3px; color: #234e52;">${JSON.stringify(data, null, 2)}</pre>
            `;
        } catch (jsonError) {
            resultDiv.innerHTML += `<p style="color: red;"><strong>❌ Error al parsear JSON:</strong> ${jsonError.message}</p>`;
        }
        
    } catch (error) {
        resultDiv.innerHTML = `<p style="color: red;"><strong>❌ Error de red:</strong> ${error.message}</p>`;
        console.error('Error:', error);
    }
}
</script>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    overflow-x: auto;
    font-size: 0.875rem;
}

h1, h2, h3 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
}

code {
    background: #f8f9fa;
    padding: 2px 5px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
}
</style>