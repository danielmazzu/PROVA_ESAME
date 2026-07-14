<?php
// ============================================
// API: Assegnazioni
// GET /api/assegnazioni/index.php
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

$pdo = getConnection();
$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

try {
    $query = '
        SELECT a.*, c.titolo as corso_titolo, c.categoria, c.durata_ore, 
               u.nome as utente_nome, u.cognome as utente_cognome
        FROM assegnazioni a
        JOIN corsi c ON a.corso_id = c.id
        JOIN users u ON a.utente_id = u.id
        WHERE 1=1
    ';
    $params = [];

    // Se è un dipendente, vede solo le proprie
    if ($role === 'dipendente') {
        $query .= ' AND a.utente_id = :user_id';
        $params['user_id'] = $userId;
    } else {
        // Filtro per dipendente (solo referente)
        if (!empty($_GET['utente_id'])) {
            $query .= ' AND a.utente_id = :utente_id';
            $params['utente_id'] = (int)$_GET['utente_id'];
        }
    }

    // Filtri comuni
    if (!empty($_GET['stato'])) {
        $query .= ' AND a.stato = :stato';
        $params['stato'] = $_GET['stato'];
    }
    
    if (!empty($_GET['categoria'])) {
        $query .= ' AND c.categoria = :categoria';
        $params['categoria'] = $_GET['categoria'];
    }

    if (!empty($_GET['corso_id'])) {
        $query .= ' AND a.corso_id = :corso_id';
        $params['corso_id'] = (int)$_GET['corso_id'];
    }

    $query .= ' ORDER BY a.data_assegnazione DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $assegnazioni = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $assegnazioni]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
