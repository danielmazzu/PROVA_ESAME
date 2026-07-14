<?php
// ============================================
// API: Statistiche
// GET /api/statistiche/academy.php
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono visualizzare le statistiche.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

try {
    $pdo = getConnection();
    
    $query = "
        SELECT 
            TO_CHAR(a.data_assegnazione, 'YYYY-MM') as mese,
            c.categoria as categoria,
            COUNT(a.id) as numeroAssegnazioni,
            SUM(CASE WHEN a.stato = 'Completato' THEN 1 ELSE 0 END) as numeroCompletamenti
        FROM assegnazioni a
        JOIN corsi c ON a.corso_id = c.id
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($_GET['mese'])) {
        $query .= " AND TO_CHAR(a.data_assegnazione, 'YYYY-MM') = :mese";
        $params['mese'] = $_GET['mese'];
    }

    if (!empty($_GET['categoria'])) {
        $query .= " AND c.categoria = :categoria";
        $params['categoria'] = $_GET['categoria'];
    }

    if (!empty($_GET['utente_id'])) {
        $query .= " AND a.utente_id = :utente_id";
        $params['utente_id'] = (int)$_GET['utente_id'];
    }

    $query .= " GROUP BY mese, categoria ORDER BY mese DESC, categoria ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted = array_map(function($row) {
        $assegnazioni = (int)$row['numeroAssegnazioni'];
        $completamenti = (int)$row['numeroCompletamenti'];
        $percentuale = $assegnazioni > 0 ? round(($completamenti / $assegnazioni) * 100, 2) : 0;
        
        return [
            'mese' => $row['mese'],
            'categoria' => $row['categoria'],
            'numeroAssegnazioni' => $assegnazioni,
            'numeroCompletamenti' => $completamenti,
            'percentualeCompletamento' => $percentuale
        ];
    }, $results);

    echo json_encode(['success' => true, 'data' => $formatted]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
