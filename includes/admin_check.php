<?php
// ============================================
// Middleware: Verifica Ruolo Referente (Admin)
// ============================================
// Questo file va incluso DOPO auth_check.php nelle pagine
// riservate esclusivamente ai referenti (es. gestione corsi, statistiche).
// Se l'utente non e' un referente, viene reindirizzato alla dashboard.
// ============================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'referente') {
    header('Location: dashboard.php'); // Reindirizza i dipendenti alla dashboard
    exit;
}
