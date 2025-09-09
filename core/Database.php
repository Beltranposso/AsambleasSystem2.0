<?php
class Database {
    private $connection;
    private $host = 'localhost';
    private $database = 'asambleas_db';
    private $username = 'root';
    private $password = '';
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            
            // Establecer charset
            $this->connection->set_charset("utf8");
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos");
        }
    }
    
    /**
     * Ejecutar una consulta que devuelve un solo registro
     */
    public function fetch($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . $this->connection->error);
            }
            
            // Bind parameters si existen
            if (!empty($params)) {
                $types = $this->getParamTypes($params);
                $stmt->bind_param($types, ...$params);
            }
            
            // Ejecutar
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: " . $stmt->error);
            }
            
            // Obtener resultado
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stmt->close();
                return $row;
            }
            
            $stmt->close();
            return null;
            
        } catch (Exception $e) {
            error_log("Database fetch error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ejecutar una consulta que devuelve múltiples registros
     */
    public function fetchAll($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . $this->connection->error);
            }
            
            // Bind parameters si existen
            if (!empty($params)) {
                $types = $this->getParamTypes($params);
                $stmt->bind_param($types, ...$params);
            }
            
            // Ejecutar
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            $rows = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            
            $stmt->close();
            return $rows;
            
        } catch (Exception $e) {
            error_log("Database fetchAll error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ejecutar una consulta de modificación (INSERT, UPDATE, DELETE)
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . $this->connection->error);
            }
            
            // Bind parameters si existen
            if (!empty($params)) {
                $types = $this->getParamTypes($params);
                $stmt->bind_param($types, ...$params);
            }
            
            // Ejecutar
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: " . $stmt->error);
            }
            
            // Retornar información útil según el tipo de consulta
            $info = [
                'affected_rows' => $stmt->affected_rows,
                'insert_id' => $stmt->insert_id,
                'success' => true
            ];
            
            $stmt->close();
            return $info;
            
        } catch (Exception $e) {
            error_log("Database execute error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Determinar los tipos de parámetros para bind_param
     */
    private function getParamTypes($params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
    
    /**
     * Comenzar una transacción
     */
    public function beginTransaction() {
        return $this->connection->autocommit(false);
    }
    
    /**
     * Confirmar una transacción
     */
    public function commit() {
        $result = $this->connection->commit();
        $this->connection->autocommit(true);
        return $result;
    }
    
    /**
     * Revertir una transacción
     */
    public function rollback() {
        $result = $this->connection->rollback();
        $this->connection->autocommit(true);
        return $result;
    }
    
    /**
     * Obtener el último ID insertado
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Obtener el número de filas afectadas
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    /**
     * Escapar una cadena para consultas SQL
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    /**
     * Verificar si una tabla existe
     */
    public function tableExists($tableName) {
        try {
            $query = "SHOW TABLES LIKE ?";
            $result = $this->fetch($query, [$tableName]);
            return $result !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener información de la base de datos
     */
    public function getDatabaseInfo() {
        return [
            'host' => $this->host,
            'database' => $this->database,
            'charset' => $this->connection->character_set_name(),
            'server_version' => $this->connection->server_info
        ];
    }
    
    /**
     * Cerrar la conexión
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Destructor para cerrar automáticamente la conexión
     */
    public function __destruct() {
        $this->close();
    }
    
    /**
     * Método alternativo para consultas simples (para compatibilidad)
     */
    public function query($sql) {
        try {
            $result = $this->connection->query($sql);
            
            if (!$result) {
                throw new Exception("Error en consulta: " . $this->connection->error);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener una conexión directa (para casos especiales)
     */
    public function getConnection() {
        return $this->connection;
    }
}
?>