<?php
$pageTitle = 'Elenco Dipendenti';
$pageScripts = ['../assets/js/api.js', '../assets/js/admin_utenti.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
?>

<div class="page-header flex-between">
    <div>
        <h1><i class="ph ph-user-list"></i> Elenco Dipendenti</h1>
        <p>Visualizza la lista di tutti i dipendenti registrati nell'Academy.</p>
    </div>
</div>

<div id="alerts"></div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Email</th>
                    <th>Ruolo</th>
                </tr>
            </thead>
            <tbody id="utenti-table-body">
                <tr><td colspan="5" class="text-center">Caricamento in corso...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
