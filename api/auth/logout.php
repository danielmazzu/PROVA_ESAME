<?php
// ============================================
// API: Logout Utente
// Endpoint: POST /api/auth/logout.php
// Scopo: Distrugge la sessione PHP e il cookie associato per disconnettere l'utente.
// ============================================

header('Content-Type: application/json');

// Accetta solo richieste POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit;
}

// Avvia la sessione (necessario per poterla distruggere)
session_start();

// Svuota tutte le variabili di sessione
$_SESSION = [];

// Elimina il cookie di sessione dal browser dell'utente
// Questo impedisce che il browser invii il vecchio ID di sessione nelle richieste successive
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Imposta una data di scadenza nel passato
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Distrugge completamente la sessione lato server
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logout effettuato con successo.']);
