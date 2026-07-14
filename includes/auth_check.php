<?php
// ============================================
// Middleware: Verifica Autenticazione
// ============================================
// Includere all'inizio di ogni pagina protetta.
// Redirect a login se l'utente non è autenticato.
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
