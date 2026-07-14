<?php
// ============================================
// API: Corsi
// GET /api/corsi/index.php - Lista corsi (con filtri categoria, attivo)
// POST /api/corsi/index.php - Crea un nuovo corso
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autenticazione
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getConnection();

if ($method === 'GET') {
    try {
        $query = 'SELECT * FROM corsi WHERE 1=1';
        $params = [];

        if (isset($_GET['categoria']) && $_GET['categoria'] !== '') {
            $query .= ' AND categoria = :categoria';
            $params['categoria'] = $_GET['categoria'];
        }

        if (isset($_GET['attivo']) && $_GET['attivo'] !== '') {
            $query .= ' AND attivo = :attivo';
            $params['attivo'] = (int)$_GET['attivo'];
        }

        $query .= ' ORDER BY created_at DESC';

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $corsi = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $corsi]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore del server.']);
    }
    exit;
}

if ($method === 'POST') {
    // Solo i referenti possono creare corsi
    if ($_SESSION['role'] !== 'referente') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono creare corsi.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $titolo       = trim($data['titolo'] ?? '');
    $descrizione  = trim($data['descrizione'] ?? '');
    $categoria    = trim($data['categoria'] ?? '');
    $durata_ore   = (int)($data['durata_ore'] ?? 0);
    $obbligatorio = isset($data['obbligatorio']) ? (int)$data['obbligatorio'] : 0;
    $attivo       = isset($data['attivo']) ? (int)$data['attivo'] : 1;

    // Validazione
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
        $stmt = $pdo->prepare('INSERT INTO corsi (titolo, descrizione, categoria, durata_ore, obbligatorio, attivo) VALUES (:titolo, :descrizione, :categoria, :durata_ore, :obbligatorio, :attivo)');
        $stmt->execute([
            'titolo'       => $titolo,
            'descrizione'  => $descrizione,
            'categoria'    => $categoria,
            'durata_ore'   => $durata_ore,
            'obbligatorio' => $obbligatorio,
            'attivo'       => $attivo
        ]);

        $id = $pdo->lastInsertId();
        
        // Fetch created course
        $stmt = $pdo->prepare('SELECT * FROM corsi WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $corso = $stmt->fetch();

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Corso creato.', 'data' => $corso]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore del server.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
