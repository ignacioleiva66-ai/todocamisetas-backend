cat > config/Database.php << 'EOF'
<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host   = getenv('DB_HOST')     ?: 'db';
            $port   = getenv('DB_PORT')     ?: '3306';
            $dbname = getenv('DB_DATABASE') ?: 'todocamisetas';
            $user   = getenv('DB_USERNAME') ?: 'laravel';
            $pass   = getenv('DB_PASSWORD') ?: 'secret';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}
EOF
