<?php
// ============================================
// API: Todos - Eliminazione
// DELETE /api/todos/delete.php?id=X
// ============================================

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autenticato.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$userId = $_SESSION['user_id'];
$todoId = intval($_GET['id'] ?? 0);

if ($todoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID todo non valido.']);
    exit;
}

try {
    $pdo = getConnection();

    // Verifica ownership e elimina
    $stmt = $pdo->prepare('DELETE FROM todos WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $todoId, 'user_id' => $userId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Todo non trovato.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Todo eliminato con successo.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
