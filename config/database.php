<?php
// ============================================
// Configurazione Database
// Gestisce la connessione al database tramite PDO.
// Supporta sia MySQL (sviluppo locale) sia PostgreSQL (produzione su Railway/Supabase).
// ============================================

// Costanti per la connessione al database MySQL locale (usate solo in ambiente di sviluppo)
define('DB_HOST', 'localhost');
define('DB_NAME', 'temeplate');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Restituisce una connessione PDO al database.
 * Utilizza il pattern Singleton (variabile static) per evitare di creare
 * connessioni multiple durante la stessa richiesta HTTP.
 * 
 * @return PDO Oggetto PDO connesso al database
 * @throws PDOException In caso di errore di connessione
 */
function getConnection(): PDO
{
    // Variabile statica: mantiene il valore tra chiamate successive alla funzione
    // Alla prima chiamata vale null, alle successive contiene gia' la connessione
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "";
        $user = DB_USER;
        $pass = DB_PASS;

        // Controlla se esiste la variabile d'ambiente DATABASE_URL (impostata da Railway in produzione)
        $dbUrl = getenv('DATABASE_URL');
        
        if ($dbUrl) {
            // PRODUZIONE: Parsing dell'URL PostgreSQL fornito da Railway/Supabase
            // Formato tipico: postgres://utente:password@host:porta/nome_database
            $parsedUrl = parse_url($dbUrl);
            $host = $parsedUrl['host'];
            $port = $parsedUrl['port'] ?? 5432;       // Porta di default di PostgreSQL
            $user = urldecode($parsedUrl['user'] ?? '');
            $pass = urldecode($parsedUrl['pass'] ?? '');
            $dbName = ltrim($parsedUrl['path'], '/');  // Rimuove lo slash iniziale dal nome del DB
            
            // DSN (Data Source Name) per PostgreSQL
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
        } else {
            // SVILUPPO LOCALE: Usa MySQL con le costanti definite sopra
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        }

        // Opzioni PDO per una connessione sicura e consistente
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Lancia eccezioni in caso di errore SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Restituisce array associativi (chiave => valore)
            PDO::ATTR_EMULATE_PREPARES   => false,                     // Usa prepared statements nativi (piu' sicuri)
        ];

        try {
            // Crea la connessione PDO con il DSN, le credenziali e le opzioni
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Se la connessione fallisce, restituisce un errore 500 (Internal Server Error)
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore DB: ' . $e->getMessage()]);
            exit;
        }
    }

    return $pdo;
}
