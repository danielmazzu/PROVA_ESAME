<?php
// ============================================
// API: Annulla Assegnazione
// Endpoint: PUT /api/assegnazioni/annulla.php?id={id}
// Scopo: Annulla un'assegnazione cambiandone lo stato in "Annullato".
//        Non si puo' annullare un'assegnazione gia' completata.
//        Solo i referenti possono annullare le assegnazioni.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono annullare le assegnazioni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono annullare le assegnazioni.']);
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

try {
    $pdo = getConnection();
    
    // Verifica che l'assegnazione esista e recupera il suo stato attuale
    $stmt = $pdo->prepare('SELECT stato FROM assegnazioni WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $assegnazione = $stmt->fetch();

    if (!$assegnazione) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Assegnazione non trovata.']);
        exit;
    }

    // REGOLA DI BUSINESS: un corso gia' completato non puo' essere annullato
    // (il dipendente ha gia' terminato la formazione, lo storico va preservato)
    if ($assegnazione['stato'] === 'Completato') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Impossibile annullare un\'assegnazione già completata.']);
        exit;
    }

    // Aggiorna lo stato a "Annullato"
    $updateStmt = $pdo->prepare("UPDATE assegnazioni SET stato = 'Annullato' WHERE id = :id");
    $updateStmt->execute(['id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Assegnazione annullata.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server: ' . $e->getMessage()]);
}
