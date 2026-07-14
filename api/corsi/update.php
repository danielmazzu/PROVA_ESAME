<?php
// ============================================
// API: Modifica Corso
// PUT /api/corsi/update.php?id={id}
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono modificare i corsi.']);
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
$titolo       = trim($data['titolo'] ?? '');
$descrizione  = trim($data['descrizione'] ?? '');
$categoria    = trim($data['categoria'] ?? '');
$durata_ore   = (int)($data['durata_ore'] ?? 0);
$obbligatorio = isset($data['obbligatorio']) ? (int)$data['obbligatorio'] : 0;
$attivo       = isset($data['attivo']) ? (int)$data['attivo'] : 1;

$errors = [];
if (empty($titolo)) $errors[] = 'Il titolo è obbligatorio.';
if (empty($categoria)) $errors[] = 'La categoria è obbligatoria.';
if ($durata_ore <= 0) $errors[] = 'La durata prevista deve essere maggiore di zero.';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare('UPDATE corsi SET titolo = :titolo, descrizione = :descrizione, categoria = :categoria, durata_ore = :durata_ore, obbligatorio = :obbligatorio, attivo = :attivo WHERE id = :id');
    $stmt->execute([
        'titolo'       => $titolo,
        'descrizione'  => $descrizione,
        'categoria'    => $categoria,
        'durata_ore'   => $durata_ore,
        'obbligatorio' => $obbligatorio,
        'attivo'       => $attivo,
        'id'           => $id
    ]);

    if ($stmt->rowCount() === 0) {
        // Potrebbe non esistere o nessun dato è cambiato
        $check = $pdo->prepare('SELECT id FROM corsi WHERE id = :id');
        $check->execute(['id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Corso aggiornato con successo.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
