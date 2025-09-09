<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'asambleas_db'; // ← CAMBIAR ESTO a tu nombre de BD
    private $username = 'root';
    private $password = '';
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", 
                $this->username, 
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function fetch($sql, $params = []) {
        try {
            error_log("Database fetch - SQL: $sql");
            error_log("Database fetch - Params: " . print_r($params, true));
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            error_log("Database fetch - Result: " . print_r($result, true));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database fetch ERROR: " . $e->getMessage());
            throw new Exception("Error en consulta: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        try {
            error_log("Database fetchAll - SQL: $sql");
            error_log("Database fetchAll - Params: " . print_r($params, true));
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();
            
            error_log("Database fetchAll - Count: " . count($result));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database fetchAll ERROR: " . $e->getMessage());
            throw new Exception("Error en consulta: " . $e->getMessage());
        }
    }
    
    public function execute($sql, $params = []) {
        try {
            error_log("Database execute - SQL: $sql");
            error_log("Database execute - Params: " . print_r($params, true));
            
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            
            $affectedRows = $stmt->rowCount();
            $insertId = $this->connection->lastInsertId();
            
            error_log("Database execute - Affected rows: $affectedRows");
            error_log("Database execute - Insert ID: $insertId");
            
            return [
                'success' => $result,
                'affected_rows' => $affectedRows,
                'insert_id' => $insertId
            ];
            
        } catch (PDOException $e) {
            error_log("Database execute ERROR: " . $e->getMessage());
            throw new Exception("Error ejecutando query: " . $e->getMessage());
        }
    }
    
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            error_log("Begin transaction ERROR: " . $e->getMessage());
            throw new Exception("Error iniciando transacción");
        }
    }
    
    public function commit() {
        try {
            return $this->connection->commit();
        } catch (PDOException $e) {
            error_log("Commit ERROR: " . $e->getMessage());
            throw new Exception("Error confirmando transacción");
        }
    }
    
    public function rollback() {
        try {
            return $this->connection->rollback();
        } catch (PDOException $e) {
            error_log("Rollback ERROR: " . $e->getMessage());
            throw new Exception("Error cancelando transacción");
        }
    }
    
    public function tableExists($tableName) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $result = $this->fetch($sql, [$tableName]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Table exists check ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTableStructure($tableName) {
        try {
            $sql = "SHOW COLUMNS FROM `$tableName`";
            return $this->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Get table structure ERROR: " . $e->getMessage());
            return [];
        }
    }
    
    public function query($sql) {
        try {
            error_log("Database raw query: $sql");
            return $this->connection->query($sql);
        } catch (PDOException $e) {
            error_log("Raw query ERROR: " . $e->getMessage());
            throw new Exception("Error en query: " . $e->getMessage());
        }
    }
}
?>