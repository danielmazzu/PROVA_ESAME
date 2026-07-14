<?php
// ============================================
// API: Crea Assegnazione
// POST /api/assegnazioni/create.php
// Body: { corso_id, utente_id, data_scadenza }
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono assegnare corsi.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$corso_id      = (int)($data['corso_id'] ?? 0);
$utente_id     = (int)($data['utente_id'] ?? 0);
$data_scadenza = trim($data['data_scadenza'] ?? '');

$errors = [];
if ($corso_id <= 0) $errors[] = 'Selezionare un corso.';
if ($utente_id <= 0) $errors[] = 'Selezionare un dipendente.';
if (empty($data_scadenza)) {
    $errors[] = 'La data di scadenza è obbligatoria.';
} else {
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

    // Controlla se il corso è attivo
    $stmt = $pdo->prepare('SELECT attivo FROM corsi WHERE id = :id');
    $stmt->execute(['id' => $corso_id]);
    $corso = $stmt->fetch();

    if (!$corso) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
        exit;
    }
    if ((int)$corso['attivo'] === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Impossibile assegnare un corso non attivo.']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO assegnazioni (corso_id, utente_id, data_assegnazione, data_scadenza, stato) VALUES (:corso_id, :utente_id, CURRENT_DATE(), :data_scadenza, "Assegnato")');
    $stmt->execute([
        'corso_id'      => $corso_id,
        'utente_id'     => $utente_id,
        'data_scadenza' => $data_scadenza
    ]);

    echo json_encode(['success' => true, 'message' => 'Assegnazione creata con successo.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
