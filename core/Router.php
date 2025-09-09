<?php
// core/Router.php - Versión corregida para tu estructura
class Router {
    private $routes = [];
    
    public function get($path, $controller, $method) {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }
    
    public function post($path, $controller, $method) {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remover el prefijo base del proyecto
        $basePath = '/Asambleas/public';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Si el path está vacío, asignar ruta raíz
        if (empty($path) || $path === '/') {
            $path = '/';
        }
        
        // Debug para desarrollo (comentar en producción)
        if (isset($_GET['debug'])) {
            echo "<pre>";
            echo "URI completa: " . $uri . "\n";
            echo "Path extraído: " . $path . "\n";
            echo "Método: " . $method . "\n";
            echo "Rutas disponibles: " . print_r(array_keys($this->routes[$method] ?? []), true);
            echo "</pre>";
        }
        
        // Buscar ruta exacta
        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $this->callController($route['controller'], $route['method'], []);
            return;
        }
        
        // Buscar rutas con parámetros dinámicos
        foreach ($this->routes[$method] ?? [] as $pattern => $route) {
            if ($this->matchRoute($pattern, $path, $params)) {
                $this->callController($route['controller'], $route['method'], $params);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function matchRoute($pattern, $path, &$params) {
        $params = [];
        
        // Convertir patrón como /admin/asambleas/{id} a regex
        $regexPattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regexPattern = '#^' . $regexPattern . '$#';
        
        if (preg_match($regexPattern, $path, $matches)) {
            array_shift($matches); // Remover la coincidencia completa
            $params = $matches;
            return true;
        }
        
        return false;
    }
    
    private function callController($controllerClass, $method, $params) {
        // Incluir archivos de controladores si existen
        $controllerFile = "../controllers/{$controllerClass}.php";
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        }
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], $params);
            } else {
                $this->notFound("Método $method no encontrado en $controllerClass");
            }
        } else {
            $this->notFound("Controlador $controllerClass no encontrado");
        }
    }
    
    private function notFound($message = "Página no encontrada") {
        http_response_code(404);
        echo "<!DOCTYPE html>";
        echo "<html><head><title>404 - Página no encontrada</title></head><body>";
        echo "<h1>404 - $message</h1>";
        echo "<p>La página que buscas no existe.</p>";
        echo "<a href='/Asambleas/public/'>Volver al inicio</a>";
        echo "</body></html>";
    }
}
?>