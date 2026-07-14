<?php
// ============================================
// API: Modifica Assegnazione
// Endpoint: PUT /api/assegnazioni/update.php?id={id}
// Body JSON atteso: { "data_scadenza": "2026-12-31" }
// Scopo: Permette al referente di modificare la data di scadenza di un'assegnazione.
//        La nuova data non puo' essere precedente alla data di assegnazione originale.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono modificare le assegnazioni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono modificare le assegnazioni.']);
    exit;
}

// Accetta solo richieste PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Recupera l'ID dell'assegnazione dalla query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID assegnazione non valido.']);
    exit;
}

// Decodifica il body JSON
$data = json_decode(file_get_contents('php://input'), true);
$data_scadenza = trim($data['data_scadenza'] ?? '');

// Validazione: la data di scadenza e' obbligatoria
if (empty($data_scadenza)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La data di scadenza è obbligatoria.']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Recupera la data di assegnazione originale per validare la nuova scadenza
    $stmt = $pdo->prepare('SELECT data_assegnazione FROM assegnazioni WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $assegnazione = $stmt->fetch();

    if (!$assegnazione) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assegnazione non trovata.']);
        exit;
    }

    // REGOLA DI BUSINESS: la scadenza non puo' essere precedente alla data di assegnazione
    // (non avrebbe senso che un corso scada prima di essere stato assegnato)
    if ($data_scadenza < $assegnazione['data_assegnazione']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La data di scadenza non può essere precedente alla data di assegnazione.']);
        exit;
    }

    // Aggiorna la data di scadenza
    $updateStmt = $pdo->prepare('UPDATE assegnazioni SET data_scadenza = :data_scadenza WHERE id = :id');
    $updateStmt->execute([
        'data_scadenza' => $data_scadenza,
        'id'            => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Assegnazione aggiornata.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
