<?php
$pageTitle = 'Gestione Assegnazioni';
$pageScripts = ['../assets/js/api.js', '../assets/js/admin_assegnazioni.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
?>

<div class="page-header flex-between">
    <div>
        <h1><i class="ph ph-users"></i> Gestione Assegnazioni</h1>
        <p>Assegna corsi ai dipendenti e monitora il loro stato.</p>
    </div>
    <button class="btn btn-primary" id="btn-nuova-assegnazione">+ Nuova Assegnazione</button>
</div>

<div id="alerts"></div>

<div class="card mb-6">
    <div class="card-body">
        <form id="form-filtri" class="form-inline">
            <div class="form-group">
                <label class="form-label" for="filtro-stato">Stato</label>
                <select id="filtro-stato" class="form-input">
                    <option value="">Tutti</option>
                    <option value="Assegnato">Assegnato</option>
                    <option value="Completato">Completato</option>
                    <option value="Scaduto">Scaduto</option>
                    <option value="Annullato">Annullato</option>
                </select>
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
                    <th>ID</th>
                    <th>Dipendente</th>
                    <th>Corso</th>
                    <th>Scadenza</th>
                    <th>Stato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody id="assegnazioni-table-body">
                <tr><td colspan="6" class="text-center">Caricamento in corso...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
