<?php
// ============================================
// Middleware: Verifica Autenticazione
// ============================================
// Questo file va incluso all'inizio di ogni pagina protetta.
// Controlla se l'utente ha una sessione attiva (cioe' ha fatto il login).
// Se non e' autenticato, lo reindirizza alla pagina di login.
// ============================================

// Avvia la sessione solo se non e' gia' attiva (evita warning PHP)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se non esiste l'ID utente in sessione, l'utente non e' loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Reindirizza alla pagina di login
    exit;
}
