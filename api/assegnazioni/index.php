<?php
// ============================================
// API: Lista Assegnazioni
// Endpoint: GET /api/assegnazioni/index.php
// Filtri opzionali: ?utente_id=2&stato=Assegnato&categoria=Informatica&corso_id=1
// Scopo: Restituisce l'elenco delle assegnazioni con i dettagli del corso e del dipendente.
//        Il referente vede tutte le assegnazioni; il dipendente vede solo le proprie.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autenticazione
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

// Accetta solo richieste GET
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

try {
    // Query con JOIN per unire i dati delle 3 tabelle: assegnazioni, corsi e utenti
    // Questo permette di mostrare il titolo del corso e il nome del dipendente in un'unica risposta
    $query = '
        SELECT a.*, c.titolo as corso_titolo, c.categoria, c.durata_ore, 
               u.nome as utente_nome, u.cognome as utente_cognome
        FROM assegnazioni a
        JOIN corsi c ON a.corso_id = c.id
        JOIN users u ON a.utente_id = u.id
        WHERE 1=1
    ';
    $params = [];

    // CONTROLLO RUOLO (Zero Trust):
    // Il dipendente puo' vedere SOLO le proprie assegnazioni
    // Il referente puo' vedere quelle di tutti (con filtro opzionale per utente)
    if ($role === 'dipendente') {
        $query .= ' AND a.utente_id = :user_id';
        $params['user_id'] = $userId;
    } else {
        // Il referente puo' filtrare per uno specifico dipendente
        if (!empty($_GET['utente_id'])) {
            $query .= ' AND a.utente_id = :utente_id';
            $params['utente_id'] = (int)$_GET['utente_id'];
        }
    }

    // Filtro per stato dell'assegnazione (Assegnato, Completato, Scaduto, Annullato)
    // Il CAST e' necessario perche' PostgreSQL usa un tipo ENUM personalizzato
    if (!empty($_GET['stato'])) {
        $query .= ' AND a.stato = CAST(:stato AS stato_assegnazione)';
        $params['stato'] = $_GET['stato'];
    }
    
    // Filtro per categoria del corso
    if (!empty($_GET['categoria'])) {
        $query .= ' AND c.categoria = :categoria';
        $params['categoria'] = $_GET['categoria'];
    }

    // Filtro per uno specifico corso
    if (!empty($_GET['corso_id'])) {
        $query .= ' AND a.corso_id = :corso_id';
        $params['corso_id'] = (int)$_GET['corso_id'];
    }

    // Ordina per data di assegnazione decrescente (le piu' recenti prima)
    $query .= ' ORDER BY a.data_assegnazione DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $assegnazioni = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $assegnazioni]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
