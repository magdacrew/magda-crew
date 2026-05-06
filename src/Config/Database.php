<?php
class Database {
    private static $pdo = null;

    
    private function __construct() {}

    public static function getConnection() {

        if (self::$pdo === null) {
            
            
            $envPath = __DIR__ . '/../../.env';
            
            if (!file_exists($envPath)) {
                die("Erro crítico: Arquivo .env não encontrado na raiz do projeto.");
            }

            
            $env = parse_ini_file($envPath);
            
            $host = $env['DB_HOST'];
            $port = $env['DB_PORT'];
            $dbname = $env['DB_NAME'];
            $user = $env['DB_USER'];
            $pass = $env['DB_PASS'] ?? ''; 

            try {
                
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                
                self::$pdo = new PDO($dsn, $user, $pass);
                
                
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                
                die("Falha na conexão com o banco de dados: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}