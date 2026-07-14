<?php
$pageTitle = 'Statistiche';
$pageScripts = ['../assets/js/api.js', '../assets/js/admin_statistiche.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
?>

<div class="page-header flex-between">
    <div>
        <h1>📈 Statistiche Academy</h1>
        <p>Visualizza l'andamento delle formazioni.</p>
    </div>
</div>

<div id="alerts"></div>

<div class="card mb-6">
    <div class="card-body">
        <form id="form-filtri" class="form-inline">
            <div class="form-group">
                <label class="form-label" for="filtro-mese">Mese/Periodo (YYYY-MM)</label>
                <input type="month" id="filtro-mese" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label" for="filtro-categoria">Categoria</label>
                <input type="text" id="filtro-categoria" class="form-input" placeholder="Es. Sicurezza">
            </div>
            <button type="submit" class="btn btn-primary">Filtra</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Mese</th>
                    <th>Categoria</th>
                    <th>Corsi Assegnati</th>
                    <th>Corsi Completati</th>
                    <th>Completamento (%)</th>
                </tr>
            </thead>
            <tbody id="statistiche-table-body">
                <tr><td colspan="5" class="text-center">Caricamento in corso...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
