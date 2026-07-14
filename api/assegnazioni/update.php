<?php
// ============================================
// API: Modifica Assegnazione
// PUT /api/assegnazioni/update.php?id={id}
// Body: { data_scadenza }
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono modificare le assegnazioni.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID assegnazione non valido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$data_scadenza = trim($data['data_scadenza'] ?? '');

if (empty($data_scadenza)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La data di scadenza è obbligatoria.']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Controlla l'esistenza dell'assegnazione
    $stmt = $pdo->prepare('SELECT data_assegnazione FROM assegnazioni WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $assegnazione = $stmt->fetch();

    if (!$assegnazione) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assegnazione non trovata.']);
        exit;
    }

    if ($data_scadenza < $assegnazione['data_assegnazione']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La data di scadenza non può essere precedente alla data di assegnazione.']);
        exit;
    }

    // Aggiorna data di scadenza
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
