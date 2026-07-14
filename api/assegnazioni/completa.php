<?php
// ============================================
// API: Completa Assegnazione
// PUT /api/assegnazioni/completa.php?id={id}
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
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

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

try {
    $pdo = getConnection();
    
    // Verifica l'assegnazione e l'appartenenza
    $stmt = $pdo->prepare('SELECT utente_id, data_assegnazione, stato FROM assegnazioni WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $assegnazione = $stmt->fetch();

    if (!$assegnazione) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assegnazione non trovata.']);
        exit;
    }

    if ($role === 'dipendente' && (int)$assegnazione['utente_id'] !== (int)$userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non puoi completare un corso assegnato ad un altro dipendente.']);
        exit;
    }

    if ($assegnazione['stato'] !== 'Assegnato') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'L\'assegnazione non è in stato "Assegnato".']);
        exit;
    }

    // Aggiorna stato e data
    $updateStmt = $pdo->prepare("UPDATE assegnazioni SET stato = 'Completato', data_completamento = CURRENT_DATE WHERE id = :id");
    $updateStmt->execute(['id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Corso completato!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
