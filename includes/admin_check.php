<?php
// ============================================
// Middleware: Verifica Ruolo Admin
// ============================================
// Includere DOPO auth_check.php nelle pagine
// riservate solo agli amministratori.
// ============================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'referente') {
    header('Location: dashboard.php');
    exit;
}
