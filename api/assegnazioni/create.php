<?php
// ============================================
// API: Crea Assegnazione
// Endpoint: POST /api/assegnazioni/create.php
// Body JSON atteso: { "corso_id": 1, "utente_id": 2, "data_scadenza": "2026-12-31" }
// Scopo: Assegna un corso a un dipendente con una data di scadenza.
//        Solo i referenti possono creare assegnazioni.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono assegnare corsi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono assegnare corsi.']);
    exit;
}

// Accetta solo richieste POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Decodifica il body JSON della richiesta
$data = json_decode(file_get_contents('php://input'), true);

// Estrae e converte i campi dal body
$corso_id      = (int)($data['corso_id'] ?? 0);
$utente_id     = (int)($data['utente_id'] ?? 0);
$data_scadenza = trim($data['data_scadenza'] ?? '');

// Validazione lato server
$errors = [];
if ($corso_id <= 0) $errors[] = 'Selezionare un corso.';
if ($utente_id <= 0) $errors[] = 'Selezionare un dipendente.';
if (empty($data_scadenza)) {
    $errors[] = 'La data di scadenza è obbligatoria.';
} else {
    // La data di scadenza non puo' essere nel passato
    $today = date('Y-m-d');
    if ($data_scadenza < $today) {
        $errors[] = 'La data di scadenza non può essere nel passato.';
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getConnection();

    // Verifica che il corso esista e sia attivo prima di assegnarlo
    $stmt = $pdo->prepare('SELECT attivo FROM corsi WHERE id = :id');
    $stmt->execute(['id' => $corso_id]);
    $corso = $stmt->fetch();

    if (!$corso) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
        exit;
    }
    // Non si puo' assegnare un corso disattivato
    if ((int)$corso['attivo'] === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Impossibile assegnare un corso non attivo.']);
        exit;
    }

    // Inserisce la nuova assegnazione con stato iniziale "Assegnato"
    // CURRENT_DATE e' una funzione SQL che restituisce la data odierna
    $stmt = $pdo->prepare("INSERT INTO assegnazioni (corso_id, utente_id, data_assegnazione, data_scadenza, stato) VALUES (:corso_id, :utente_id, CURRENT_DATE, :data_scadenza, 'Assegnato')");
    $stmt->execute([
        'corso_id'      => $corso_id,
        'utente_id'     => $utente_id,
        'data_scadenza' => $data_scadenza
    ]);

    echo json_encode(['success' => true, 'message' => 'Assegnazione creata con successo.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
