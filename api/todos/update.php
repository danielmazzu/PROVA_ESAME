<?php
// ============================================
// API: Todos - Aggiornamento
// PUT /api/todos/update.php?id=X
// Body: { title, description, completed }
// ============================================

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autenticato.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getConnection();

    // Verifica ownership
    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $todoId, 'user_id' => $userId]);
    $todo = $stmt->fetch();

    if (!$todo) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Todo non trovato.']);
        exit;
    }

    // Aggiorna solo i campi forniti
    $title       = trim($data['title'] ?? $todo['title']);
    $description = trim($data['description'] ?? $todo['description']);
    $completed   = isset($data['completed']) ? intval($data['completed']) : $todo['completed'];

    $stmt = $pdo->prepare('UPDATE todos SET title = :title, description = :description, completed = :completed WHERE id = :id AND user_id = :user_id');
    $stmt->execute([
        'title'       => $title,
        'description' => $description,
        'completed'   => $completed,
        'id'          => $todoId,
        'user_id'     => $userId
    ]);

    // Restituisci il todo aggiornato
    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
    $stmt->execute(['id' => $todoId]);
    $updatedTodo = $stmt->fetch();

    echo json_encode(['success' => true, 'data' => $updatedTodo, 'message' => 'Todo aggiornato con successo.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
