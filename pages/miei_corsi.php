<?php
$pageTitle = 'I Miei Corsi';
$pageScripts = ['../assets/js/api.js', '../assets/js/miei_corsi.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Solo i dipendenti (o admin per test) possono vedere questa pagina
if ($_SESSION['role'] !== 'dipendente') {
    // Redirect to admin dashboard if they are admin
    header('Location: dashboard.php');
    exit;
}
?>

<div class="page-header flex-between">
    <div>
        <h1><i class="ph ph-books"></i> I Miei Corsi</h1>
        <p>Consulta e completa i percorsi formativi a te assegnati.</p>
    </div>
</div>

<div id="alerts"></div>

<div class="card mb-6">
    <div class="card-body">
        <form id="form-filtri" class="form-inline">
            <div class="form-group">
                <label class="form-label" for="filtro-stato">Stato</label>
                <select id="filtro-stato" class="form-input">
                    <option value="">Tutti</option>
                    <option value="Assegnato">Da Completare</option>
                    <option value="Completato">Completati</option>
                    <option value="Scaduto">Scaduti</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="filtro-categoria">Categoria</label>
                <input type="text" id="filtro-categoria" class="form-input" placeholder="Es. Sicurezza">
            </div>
            <button type="submit" class="btn btn-primary">Filtra</button>
        </form>
    </div>
</div>

<div id="corsi-list" class="grid-cards">
    <div class="loading-overlay"><div class="spinner"></div></div>
</div>

<div id="empty-state" class="empty-state hidden">
    <div class="empty-icon"><i class="ph ph-folder-open"></i></div>
    <h3>Nessun corso trovato</h3>
    <p>Non ci sono corsi che corrispondono ai criteri di ricerca.</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
