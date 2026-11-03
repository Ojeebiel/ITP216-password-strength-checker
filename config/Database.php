<?php

require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

class Database {
    private static ?PDO $instance = null;

    // private static string $host     = "localhost";
    // private static string $db_user  = "root";
    // private static string $db_pass  = "";
    // private static string $db_name  = "itp216_pt";

    /**
     * Prevents direct instantiation from outside
     */
    private function __construct() {}

    /**
     * Retrieves or initializes the single shared PDO instance
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $host   = $_ENV['DB_HOST'] ?? 'localhost';
                $db_name = $_ENV['DB_NAME'] ?? '';
                $db_user   = $_ENV['DB_USER'] ?? 'root';
                $db_pass   = $_ENV['DB_PASS'] ?? '';

                // 1. Establish the PDO DSN connection string with UTF-8 support
                $dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";

                // 2. Configure PDO safety options
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Returns arrays indexed by column name
                    PDO::ATTR_EMULATE_PREPARES   => false,                  // Uses native server-side prepared statements
                ];

                // 3. Initialize the connection object
                self::$instance = new PDO($dsn, $db_user, $db_pass, $options);

            } catch (PDOException $e) {
                // Log internal error safely
                error_log($e->getMessage());

                // Return a safe error response without exposing database credentials
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    "success" => false, 
                    "message" => "Database connection failed. System is experiencing technical difficulties."
                ]);
                exit;
            }
        }

        return self::$instance;
    }

    
}
