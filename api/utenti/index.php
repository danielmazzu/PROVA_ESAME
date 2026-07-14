<?php
// ============================================
// API: Elenco Utenti (Dipendenti)
// Endpoint: GET /api/utenti/index.php
// Scopo: Restituisce la lista di tutti i dipendenti registrati (esclude i referenti).
//        Usata dalla pagina Elenco Dipendenti e dal form di assegnazione corsi.
//        Accessibile solo ai referenti.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono vedere la lista utenti
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono vedere gli utenti.']);
    exit;
}

// Accetta solo richieste GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Seleziona solo i dipendenti (non i referenti) ordinati per cognome e nome
    // Non restituisce la password per motivi di sicurezza
    $stmt = $pdo->query("SELECT id, nome, cognome, email FROM users WHERE role = 'dipendente' ORDER BY cognome ASC, nome ASC");
    $utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $utenti]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
