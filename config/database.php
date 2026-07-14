<?php
// ============================================
// Configurazione Database
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'temeplate');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Restituisce una connessione PDO al database.
 * 
 * @return PDO
 * @throws PDOException
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "";
        $user = DB_USER;
        $pass = DB_PASS;

        // Se siamo su Railway/Supabase, usiamo l'URL di connessione PostgreSQL
        $dbUrl = getenv('DATABASE_URL');
        
        if ($dbUrl) {
            // Parsing url: postgres://user:password@host:port/dbname
            $parsedUrl = parse_url($dbUrl);
            $host = $parsedUrl['host'];
            $port = $parsedUrl['port'] ?? 5432;
            $user = $parsedUrl['user'];
            $pass = $parsedUrl['pass'];
            $dbName = ltrim($parsedUrl['path'], '/');
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
        } else {
            // Fallback: MySQL locale
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore di connessione al database.', 'details' => $e->getMessage()]);
            exit;
        }
    }

    return $pdo;
}
