<?php
class Database {
    private $conn = null;
    private $table = '';
    private static $instance = null;
    
    // 构造函数
    private function __construct($config) {
        try {
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset={$config['db_charset']}";
            $this->conn = new PDO($dsn, $config['db_user'], $config['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch(PDOException $e) {
            throw new Exception("数据库连接失败: " . $e->getMessage());
        }
    }
    
    // 单例模式获取实例
    public static function getInstance($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    // 设置表名
    public function table($table) {
        $this->table = $table;
        return $this;
    }
    
    // 查找单条记录
    public function find($where = []) {
        $sql = "SELECT * FROM {$this->table}";
        $conditions = [];
        $params = [];
        
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->conn->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    // 查找多条记录
    public function select($where = [], $order = '', $limit = '') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // 插入记录
    public function insert($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->conn->lastInsertId();
    }
    
    // 更新记录
    public function update($where, $data) {
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE " . implode(' AND ', $conditions);
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    // 删除记录
    public function delete($where) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $conditions);
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    // 开始事务
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    // 提交事务
    public function commit() {
        return $this->conn->commit();
    }
    
    // 回滚事务
    public function rollback() {
        return $this->conn->rollBack();
    }
} 