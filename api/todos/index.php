<?php
// ============================================
// API: Todos - Lista e Creazione
// GET  /api/todos/index.php      → Lista todos utente
// POST /api/todos/index.php      → Crea nuovo todo
// Body POST: { title, description }
// ============================================

header('Content-Type: application/json');
session_start();

// Verifica autenticazione
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autenticato.']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getConnection();

    // ---- GET: Lista todos dell'utente ----
    if ($method === 'GET') {
        $stmt = $pdo->prepare('SELECT * FROM todos WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        $todos = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $todos]);
        exit;
    }

    // ---- POST: Crea nuovo todo ----
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $title       = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');

        if (empty($title)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Il titolo è obbligatorio.']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO todos (user_id, title, description) VALUES (:user_id, :title, :description)');
        $stmt->execute([
            'user_id'     => $userId,
            'title'       => $title,
            'description' => $description
        ]);

        $newId = $pdo->lastInsertId();

        // Restituisci il todo appena creato
        $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $newId]);
        $todo = $stmt->fetch();

        http_response_code(201);
        echo json_encode(['success' => true, 'data' => $todo, 'message' => 'Todo creato con successo.']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
