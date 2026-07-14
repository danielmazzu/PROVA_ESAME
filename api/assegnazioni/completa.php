<?php
// ============================================
// API: Completa Assegnazione
// Endpoint: PUT /api/assegnazioni/completa.php?id={id}
// Scopo: Segna un'assegnazione come "Completata" e registra la data di completamento.
//        Il dipendente puo' completare solo le proprie assegnazioni.
//        Il referente puo' completare qualsiasi assegnazione.
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

// Accetta solo richieste PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Recupera l'ID dell'assegnazione dalla query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID assegnazione non valido.']);
    exit;
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

try {
    $pdo = getConnection();
    
    // Recupera i dati dell'assegnazione per verificare lo stato e l'appartenenza
    $stmt = $pdo->prepare('SELECT utente_id, data_assegnazione, stato FROM assegnazioni WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $assegnazione = $stmt->fetch();

    // Verifica che l'assegnazione esista
    if (!$assegnazione) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assegnazione non trovata.']);
        exit;
    }

    // ZERO TRUST: un dipendente non puo' completare il corso di un altro dipendente
    if ($role === 'dipendente' && (int)$assegnazione['utente_id'] !== (int)$userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non puoi completare un corso assegnato ad un altro dipendente.']);
        exit;
    }

    // Solo le assegnazioni in stato "Assegnato" possono essere completate
    // Non si puo' completare un corso gia' completato, scaduto o annullato
    if ($assegnazione['stato'] !== 'Assegnato') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'L\'assegnazione non è in stato "Assegnato".']);
        exit;
    }

    // Aggiorna lo stato a "Completato" e registra la data di completamento
    $updateStmt = $pdo->prepare("UPDATE assegnazioni SET stato = 'Completato', data_completamento = CURRENT_DATE WHERE id = :id");
    $updateStmt->execute(['id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Corso completato!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
