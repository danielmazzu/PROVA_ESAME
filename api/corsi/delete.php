<?php
// ============================================
// API: Elimina Corso
// Endpoint: DELETE /api/corsi/delete.php?id={id}
// Scopo: Elimina fisicamente un corso dal database, ma SOLO se non ha assegnazioni collegate.
//        Se il corso ha assegnazioni, suggerisce la disattivazione come alternativa.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono eliminare i corsi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono eliminare i corsi.']);
    exit;
}

// Accetta solo richieste DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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

try {
    $pdo = getConnection();
    
    // CONTROLLO INTEGRITA' REFERENZIALE: prima di eliminare, verifica se ci sono
    // assegnazioni collegate a questo corso. Se ci sono dipendenti che lo stanno
    // seguendo o lo hanno completato, non si puo' eliminare (si perderebbe lo storico)
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM assegnazioni WHERE corso_id = :id');
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        http_response_code(409); // 409 = Conflict (conflitto con lo stato attuale della risorsa)
        echo json_encode(['success' => false, 'message' => 'Impossibile eliminare il corso: ci sono assegnazioni collegate. Puoi invece disattivarlo.']);
        exit;
    }

    // Nessuna assegnazione collegata: procede con l'eliminazione fisica
    $stmt = $pdo->prepare('DELETE FROM corsi WHERE id = :id');
    $stmt->execute(['id' => $id]);

    // Verifica che la riga sia stata effettivamente eliminata
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Corso eliminato con successo.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
