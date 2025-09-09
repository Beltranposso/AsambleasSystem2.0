<?php
require_once '../core/Database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Método principal para mostrar login
    public function login() {
        $this->showLogin();
    }
    
    // Mostrar formulario de login
    public function showLogin() {
        // Si ya está logueado, redirigir según el rol
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['user_role']);
            return;
        }
        
        require_once '../views/auth/login.php';
    }
    
    // Procesar login (método POST)
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Asambleas/public/login');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validación básica
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email y contraseña son requeridos';
            header('Location: /Asambleas/public/login');
            exit;
        }
        
        // Para desarrollo - login automático sin validación de BD
        if ($this->isDevelopmentMode()) {
            $this->developmentLogin($email);
            return;
        }
        
        // Autenticación real con base de datos
        $this->authenticate($email, $password);
    }
    
    // Autenticación con base de datos
    public function authenticate($email, $password) {
        try {
            // Buscar usuario en la base de datos
            $stmt = $this->db->prepare("SELECT id, email, password, rol, nombre, activo FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    // Login exitoso
                    $this->setUserSession($user);
                    $this->redirectToDashboard($user['rol']);
                } else {
                    $_SESSION['error'] = 'Credenciales incorrectas';
                    header('Location: /Asambleas/public/login');
                    exit;
                }
            } else {
                $_SESSION['error'] = 'Usuario no encontrado o inactivo';
                header('Location: /Asambleas/public/login');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error en el sistema. Intente más tarde.';
            error_log("Error de autenticación: " . $e->getMessage());
            header('Location: /Asambleas/public/login');
            exit;
        }
    }
    
    // Login de desarrollo (sin verificación de BD)
    private function developmentLogin($email) {
        // Usuarios de prueba para desarrollo
        $testUsers = [
            'admin@test.com' => [
                'id' => 1,
                'email' => 'admin@test.com',
                'rol' => 'administrador',
                'nombre' => 'Administrador Test'
            ],
            'coordinador@test.com' => [
                'id' => 2,
                'email' => 'coordinador@test.com',
                'rol' => 'coordinador',
                'nombre' => 'Coordinador Test'
            ],
            'operador@test.com' => [
                'id' => 3,
                'email' => 'operador@test.com',
                'rol' => 'operador',
                'nombre' => 'Operador Test'
            ],
            'votante@test.com' => [
                'id' => 4,
                'email' => 'votante@test.com',
                'rol' => 'votante',
                'nombre' => 'Votante Test'
            ]
        ];
        
        // Si el email está en los usuarios de prueba
        if (isset($testUsers[$email])) {
            $user = $testUsers[$email];
            $this->setUserSession($user);
            $this->redirectToDashboard($user['rol']);
        } else {
            // Crear usuario temporal basado en el email
            $role = $this->guessRoleFromEmail($email);
            $user = [
                'id' => rand(1000, 9999),
                'email' => $email,
                'rol' => $role,
                'nombre' => 'Usuario ' . ucfirst($role)
            ];
            
            $this->setUserSession($user);
            $this->redirectToDashboard($user['rol']);
        }
    }
    
    // Establecer sesión de usuario
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['logged_in'] = true;
        
        // Registrar último login (solo en modo producción)
        if (!$this->isDevelopmentMode()) {
            $this->updateLastLogin($user['id']);
        }
    }
    
    // Actualizar último login
    private function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error actualizando último login: " . $e->getMessage());
        }
    }
    
    // Redirigir según el rol del usuario
    public function redirectToDashboard($role = null) {
        if (!$role && isset($_SESSION['user_role'])) {
            $role = $_SESSION['user_role'];
        }
        
        $dashboards = [
            'administrador' => '/Asambleas/public/dashboard/admin',
            'coordinador' => '/Asambleas/public/dashboard/coordinador',
            'operador' => '/Asambleas/public/dashboard/operador',
            'votante' => '/Asambleas/public/dashboard/votante'

        ];
        
        $redirectUrl = $dashboards[$role] ?? '/Asambleas/public/dashboard/admin';
        header("Location: $redirectUrl");
        exit;
    }
    
    // Adivinar rol basado en el email (para desarrollo)
    private function guessRoleFromEmail($email) {
        if (strpos($email, 'admin') !== false) return 'administrador';
        if (strpos($email, 'coordinador') !== false) return 'coordinador';
        if (strpos($email, 'operador') !== false) return 'operador';
        return 'votante'; // Por defecto
    }
    
    // Verificar si está en modo desarrollo
    private function isDevelopmentMode() {
        // Cambiar esto a false para modo producción
        return true;
        
        // Alternativa: verificar por ambiente
        // return $_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1';
    }
    
    // Cerrar sesión
    public function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Redirigir al login
        header('Location: /Asambleas/public/login');
        exit;
    }
    
    // Mostrar perfil del usuario
    public function perfil() {
        $this->requireAuth();
        require_once '../views/auth/perfil.php';
    }
    
    // Mostrar configuración
    public function configuracion() {
        $this->requireAuth();
        require_once '../views/auth/configuracion.php';
    }
    
    // Verificar si el usuario está autenticado
    public function requireAuth() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            header('Location: /Asambleas/public/login');
            exit;
        }
    }
    
    // Verificar rol específico
    public function requireRole($requiredRole) {
        $this->requireAuth();
        
        if ($_SESSION['user_role'] !== $requiredRole) {
            // Redirigir a su dashboard correspondiente
            $this->redirectToDashboard();
        }
    }
    
    // Verificar múltiples roles
    public function requireAnyRole($roles) {
        $this->requireAuth();
        
        if (!in_array($_SESSION['user_role'], $roles)) {
            $this->redirectToDashboard();
        }
    }
    
    // Obtener información del usuario actual
    public function getCurrentUser() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'name' => $_SESSION['user_name']
        ];
    }
    
    // Método para debuggear (solo en desarrollo)
    public function debug() {
        if (!$this->isDevelopmentMode()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        
        echo "<h2>Debug de Autenticación</h2>";
        echo "<h3>Sesión actual:</h3>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        echo "<h3>Usuarios de prueba disponibles:</h3>";
        echo "<ul>";
        echo "<li>admin@test.com - Administrador</li>";
        echo "<li>coordinador@test.com - Coordinador</li>";
        echo "<li>operador@test.com - Operador</li>";
        echo "<li>votante@test.com - Votante</li>";
        echo "</ul>";
        
        echo "<h3>Enlaces útiles:</h3>";
        echo "<ul>";
        echo "<li><a href='/Asambleas/public/login'>Login</a></li>";
        echo "<li><a href='/Asambleas/public/logout'>Logout</a></li>";
        echo "<li><a href='/Asambleas/public/dashboard/admin'>Dashboard Admin</a></li>";
        echo "</ul>";
    }
}
?>