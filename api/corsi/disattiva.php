<?php
// ============================================
// API: Disattiva/Attiva Corso
// PUT /api/corsi/disattiva.php?id={id}
// Body: { attivo: 0|1 }
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono disattivare i corsi.']);
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
    echo json_encode(['success' => false, 'message' => 'ID corso non valido.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$attivo = isset($data['attivo']) ? (int)$data['attivo'] : 0; // Default a 0 se non passato

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare('UPDATE corsi SET attivo = :attivo WHERE id = :id');
    $stmt->execute(['attivo' => $attivo, 'id' => $id]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare('SELECT id FROM corsi WHERE id = :id');
        $check->execute(['id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
            exit;
        }
    }

    $stato = $attivo ? 'attivato' : 'disattivato';
    echo json_encode(['success' => true, 'message' => "Corso $stato con successo."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
