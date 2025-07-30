<?php
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception('.env file not found');
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_PORT', $_ENV['DB_PORT']);
define('DB_USERNAME', $_ENV['DB_USERNAME']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);

define('AUTH_USERNAME', $_ENV['AUTH_USERNAME'] ?? 'admin');
define('AUTH_PASSWORD', $_ENV['AUTH_PASSWORD'] ?? 'changeme');

function getDatabaseConnection($database = null) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT;
        if ($database) {
            $dsn .= ";dbname=" . $database;
        }
        $dsn .= ";charset=utf8mb4";
        
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

function getDatabases() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SHOW DATABASES");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getTables($database) {
    $pdo = getDatabaseConnection($database);
    $stmt = $pdo->query("SHOW TABLES");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function validateIdentifier($identifier) {
    return preg_match('/^[a-zA-Z0-9_-]+$/', $identifier);
}

function getTableData($database, $table, $limit = 100, $offset = 0) {
    if (!validateIdentifier($database) || !validateIdentifier($table)) {
        throw new Exception("Invalid database or table name");
    }
    
    $pdo = getDatabaseConnection($database);
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `" . $table . "`");
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM `" . $table . "` LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    return [
        'data' => $data,
        'total' => $totalRows
    ];
}

function getTableStructure($database, $table) {
    if (!validateIdentifier($database) || !validateIdentifier($table)) {
        throw new Exception("Invalid database or table name");
    }
    
    $pdo = getDatabaseConnection($database);
    $stmt = $pdo->prepare("DESCRIBE `" . $table . "`");
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
