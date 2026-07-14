<?php
$pageTitle = 'Catalogo Corsi';
$pageScripts = ['../assets/js/api.js', '../assets/js/admin_corsi.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php'; // Adattato per controllare 'referente'
?>

<div class="page-header flex-between">
    <div>
        <h1>📖 Catalogo Corsi</h1>
        <p>Gestisci i corsi formativi dell'Academy.</p>
    </div>
    <button class="btn btn-primary" id="btn-nuovo-corso">+ Nuovo Corso</button>
</div>

<div id="alerts"></div>

<div class="card mb-6">
    <div class="card-body">
        <form id="form-filtri" class="form-inline">
            <div class="form-group">
                <label class="form-label" for="filtro-categoria">Categoria</label>
                <input type="text" id="filtro-categoria" class="form-input" placeholder="Es. Sicurezza">
            </div>
            <div class="form-group">
                <label class="form-label" for="filtro-attivo">Stato</label>
                <select id="filtro-attivo" class="form-input">
                    <option value="">Tutti</option>
                    <option value="1">Attivi</option>
                    <option value="0">Disattivati</option>
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
                    <th>Titolo</th>
                    <th>Categoria</th>
                    <th>Durata (h)</th>
                    <th>Stato</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody id="corsi-table-body">
                <tr><td colspan="6" class="text-center">Caricamento in corso...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
