<?php
// ============================================
// API: Modifica Corso
// Endpoint: PUT /api/corsi/update.php?id={id}
// Body JSON atteso: { "titolo", "descrizione", "categoria", "durata_ore", "obbligatorio", "attivo" }
// Scopo: Aggiorna i dati di un corso esistente. Accessibile solo ai referenti.
// ============================================

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Controllo autorizzazione: solo i referenti possono modificare i corsi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'referente') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato. Solo i referenti possono modificare i corsi.']);
    exit;
}

// Accetta solo richieste PUT (metodo HTTP per le operazioni di aggiornamento)
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Recupera l'ID del corso dalla query string (?id=...)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID corso non valido.']);
    exit;
}

// Decodifica il body JSON con i nuovi dati del corso
$data = json_decode(file_get_contents('php://input'), true);
$titolo       = trim($data['titolo'] ?? '');
$descrizione  = trim($data['descrizione'] ?? '');
$categoria    = trim($data['categoria'] ?? '');
$durata_ore   = (int)($data['durata_ore'] ?? 0);
$obbligatorio = isset($data['obbligatorio']) ? (int)$data['obbligatorio'] : 0;
$attivo       = isset($data['attivo']) ? (int)$data['attivo'] : 1;

// Validazione lato server dei campi obbligatori
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
    
    // Esegue l'UPDATE del corso con i nuovi dati
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

    // rowCount() restituisce il numero di righe modificate
    // Se 0, potrebbe significare che il corso non esiste o che i dati non sono cambiati
    if ($stmt->rowCount() === 0) {
        // Verifica se il corso esiste nel database
        $check = $pdo->prepare('SELECT id FROM corsi WHERE id = :id');
        $check->execute(['id' => $id]);
        if (!$check->fetch()) {
            http_response_code(404); // 404 = Not Found
            echo json_encode(['success' => false, 'message' => 'Corso non trovato.']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Corso aggiornato con successo.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore del server.']);
}
