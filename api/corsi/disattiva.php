<?php
// ============================================
// API: Disattiva/Attiva Corso
// Endpoint: PUT /api/corsi/disattiva.php?id={id}
// Body JSON atteso: { "attivo": 0 } per disattivare, { "attivo": 1 } per riattivare
// Scopo: Cambia lo stato di visibilita' di un corso senza eliminarlo dal database.
//        Un corso disattivato non puo' essere assegnato ai dipendenti.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono disattivare/attivare i corsi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono disattivare i corsi.']);
    exit;
}

// Accetta solo richieste PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Recupera l'ID del corso dalla query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID corso non valido.']);
    exit;
}

// Legge il nuovo stato dal body JSON (0 = disattivato, 1 = attivo)
$data = json_decode(file_get_contents('php://input'), true);
$attivo = isset($data['attivo']) ? (int)$data['attivo'] : 0;

try {
    $pdo = getConnection();
    
    // Aggiorna il campo "attivo" del corso
    $stmt = $pdo->prepare('UPDATE corsi SET attivo = :attivo WHERE id = :id');
    $stmt->execute(['attivo' => $attivo, 'id' => $id]);

    // Se nessuna riga e' stata modificata, verifica se il corso esiste
    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare('SELECT id FROM corsi WHERE id = :id');
        $check->execute(['id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
            exit;
        }
    }

    // Compone il messaggio di risposta in base allo stato impostato
    $stato = $attivo ? 'attivato' : 'disattivato';
    echo json_encode(['success' => true, 'message' => "Corso $stato con successo."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
