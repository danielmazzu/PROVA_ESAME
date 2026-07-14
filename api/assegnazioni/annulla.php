<?php
// ============================================
// API: Annulla Assegnazione
// PUT /api/assegnazioni/annulla.php?id={id}
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono annullare le assegnazioni.']);
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

try {
    $pdo = getConnection();
    
    // Controlla l'esistenza dell'assegnazione
    $stmt = $pdo->prepare('SELECT stato FROM assegnazioni WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $assegnazione = $stmt->fetch();

    if (!$assegnazione) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assegnazione non trovata.']);
        exit;
    }

    if ($assegnazione['stato'] === 'Completato') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Impossibile annullare un\'assegnazione già completata.']);
        exit;
    }

    // Aggiorna stato
    $updateStmt = $pdo->prepare("UPDATE assegnazioni SET stato = 'Annullato' WHERE id = :id");
    $updateStmt->execute(['id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Assegnazione annullata.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
