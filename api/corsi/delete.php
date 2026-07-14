<?php
// ============================================
// API: Elimina Corso
// DELETE /api/corsi/delete.php?id={id}
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono eliminare i corsi.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID corso non valido.']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Verifica se ci sono assegnazioni
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM assegnazioni WHERE corso_id = :id');
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'Impossibile eliminare il corso: ci sono assegnazioni collegate. Puoi invece disattivarlo.']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM corsi WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Corso eliminato con successo.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
