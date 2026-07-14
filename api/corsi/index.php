<?php
// ============================================
// API: Lista Corsi / Crea Corso
// Endpoint:
//   GET  /api/corsi/index.php               - Restituisce la lista di tutti i corsi (con filtri opzionali)
//   POST /api/corsi/index.php               - Crea un nuovo corso (solo referente)
// Filtri GET opzionali: ?categoria=Informatica&attivo=1
// Body POST atteso: { "titolo", "descrizione", "categoria", "durata_ore", "obbligatorio", "attivo" }
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autenticazione: l'utente deve aver effettuato il login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 = Unauthorized
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

// Determina il metodo HTTP della richiesta (GET o POST)
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

// -----------------------------------------------
// GET: Restituisce la lista dei corsi con filtri
// -----------------------------------------------
if ($method === 'GET') {
    try {
        // Query base: seleziona tutti i corsi. "WHERE 1=1" permette di aggiungere
        // condizioni AND in modo dinamico senza preoccuparsi del primo WHERE
        $query = 'SELECT * FROM corsi WHERE 1=1';
        $params = [];

        // Filtro opzionale per categoria (es. "Informatica", "Sicurezza")
        if (isset($_GET['categoria']) && $_GET['categoria'] !== '') {
            $query .= ' AND categoria = :categoria';
            $params['categoria'] = $_GET['categoria'];
        }

        // Filtro opzionale per stato attivo/disattivo (1 = attivo, 0 = disattivato)
        if (isset($_GET['attivo']) && $_GET['attivo'] !== '') {
            $query .= ' AND attivo = :attivo';
            $params['attivo'] = (int)$_GET['attivo'];
        }

        // Ordina per data di creazione decrescente (i piu' recenti prima)
        $query .= ' ORDER BY created_at DESC';

        // Esegue la query con prepared statement (protezione da SQL Injection)
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $corsi = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $corsi]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore del server.']);
    }
    exit;
}

// -----------------------------------------------
// POST: Crea un nuovo corso (solo per referenti)
// -----------------------------------------------
if ($method === 'POST') {
    // Controllo autorizzazione: solo il referente (admin) puo' creare corsi
    if ($_SESSION['role'] !== 'referente') {
        http_response_code(403); // 403 = Forbidden
        echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono creare corsi.']);
        exit;
    }

    // Decodifica il body JSON della richiesta
    $data = json_decode(file_get_contents('php://input'), true);

    // Estrae e pulisce i campi dal body
    $titolo       = trim($data['titolo'] ?? '');
    $descrizione  = trim($data['descrizione'] ?? '');
    $categoria    = trim($data['categoria'] ?? '');
    $durata_ore   = (int)($data['durata_ore'] ?? 0);
    $attivo       = isset($data['attivo']) ? (int)$data['attivo'] : 1;

    // Validazione lato server: verifica i campi obbligatori
    $errors = [];
    if (empty($titolo)) $errors[] = 'Il titolo è obbligatorio.';
    if (empty($categoria)) $errors[] = 'La categoria è obbligatoria.';
    if ($durata_ore <= 0) $errors[] = 'La durata prevista deve essere maggiore di zero.';

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
        exit;
    }

    try {
        // Inserisce il nuovo corso nel database
        $stmt = $pdo->prepare('INSERT INTO corsi (titolo, descrizione, categoria, durata_ore, attivo) VALUES (:titolo, :descrizione, :categoria, :durata_ore, :attivo)');
        $stmt->execute([
            'titolo'       => $titolo,
            'descrizione'  => $descrizione,
            'categoria'    => $categoria,
            'durata_ore'   => $durata_ore,
            'attivo'       => $attivo
        ]);

        // Recupera l'ID auto-generato del corso appena creato
        $id = $pdo->lastInsertId();
        
        // Recupera i dati completi del corso creato per restituirli al client
        $stmt = $pdo->prepare('SELECT * FROM corsi WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $corso = $stmt->fetch();

        http_response_code(201); // 201 = Created
        echo json_encode(['success' => true, 'message' => 'Corso creato.', 'data' => $corso]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore del server.']);
    }
    exit;
}

// Se il metodo HTTP non e' ne' GET ne' POST, restituisce errore 405
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
