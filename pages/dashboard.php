<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Statistiche Dipendente
$totCorsiAssegnati = 0;
$totCorsiCompletati = 0;
$totCorsiScaduti = 0;

if ($role === 'dipendente') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(stato = 'Completato', 1, 0)) as completati, SUM(IF(stato = 'Scaduto', 1, 0)) as scaduti FROM assegnazioni WHERE utente_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $stats = $stmt->fetch();
    $totCorsiAssegnati = (int)($stats['total'] ?? 0);
    $totCorsiCompletati = (int)($stats['completati'] ?? 0);
    $totCorsiScaduti = (int)($stats['scaduti'] ?? 0);
}

// Statistiche Referente
$totCorsiCatalogo = 0;
$totAssegnazioni = 0;
$totDipendenti = 0;

if ($role === 'referente') {
    $totCorsiCatalogo = (int)$pdo->query("SELECT COUNT(*) as total FROM corsi")->fetch()['total'];
    $totAssegnazioni = (int)$pdo->query("SELECT COUNT(*) as total FROM assegnazioni")->fetch()['total'];
    $totDipendenti = (int)$pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'dipendente'")->fetch()['total'];
}

$hour = (int)date('H');
if ($hour < 12) $greeting = 'Buongiorno';
elseif ($hour < 18) $greeting = 'Buon pomeriggio';
else $greeting = 'Buonasera';
?>

<div class="welcome-section">
    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($fullName); ?>! <i class="ph ph-hand-waving"></i></h1>
    <p>Benvenuto nell'Academy Aziendale.</p>
    <?php if ($role === 'referente'): ?>
    <span class="role-indicator admin"><i class="ph ph-shield-check"></i> Accesso Referente Academy</span>
    <?php else: ?>
    <span class="role-indicator user"><i class="ph ph-user"></i> Accesso Dipendente</span>
    <?php endif; ?>
</div>

<div class="stats-grid">
    <?php if ($role === 'dipendente'): ?>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="ph ph-books"></i></div>
            <div class="stat-value"><?php echo $totCorsiAssegnati; ?></div>
            <div class="stat-label">Corsi Assegnati Totali</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="ph ph-check-circle"></i></div>
            <div class="stat-value"><?php echo $totCorsiCompletati; ?></div>
            <div class="stat-label">Corsi Completati</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="ph ph-hourglass-high"></i></div>
            <div class="stat-value"><?php echo $totCorsiAssegnati - $totCorsiCompletati; ?></div>
            <div class="stat-label">Da Completare</div>
        </div>
    <?php elseif ($role === 'referente'): ?>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="ph ph-book-open"></i></div>
            <div class="stat-value"><?php echo $totCorsiCatalogo; ?></div>
            <div class="stat-label">Corsi a Catalogo</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="ph ph-users"></i></div>
            <div class="stat-value"><?php echo $totAssegnazioni; ?></div>
            <div class="stat-label">Assegnazioni Effettuate</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="ph ph-user-list"></i></div>
            <div class="stat-value"><?php echo $totDipendenti; ?></div>
            <div class="stat-label">Dipendenti Iscritti</div>
        </div>
    <?php endif; ?>
</div>

<div class="page-header mt-8">
    <h1>Azioni Rapide</h1>
</div>

<div class="quick-actions">
    <?php if ($role === 'dipendente'): ?>
        <a href="miei_corsi.php" class="action-card">
            <div class="action-icon primary"><i class="ph ph-books"></i></div>
            <div class="action-text">
                <h3>I Miei Corsi</h3>
                <p>Visualizza e completa i corsi che ti sono stati assegnati.</p>
            </div>
        </a>
    <?php elseif ($role === 'referente'): ?>
        <a href="admin_corsi.php" class="action-card">
            <div class="action-icon primary"><i class="ph ph-book-open"></i></div>
            <div class="action-text">
                <h3>Gestione Catalogo</h3>
                <p>Aggiungi, modifica o disattiva i corsi dell'Academy.</p>
            </div>
        </a>
        <a href="admin_assegnazioni.php" class="action-card">
            <div class="action-icon warning"><i class="ph ph-users"></i></div>
            <div class="action-text">
                <h3>Assegna Corsi</h3>
                <p>Gestisci l'assegnazione dei corsi ai dipendenti.</p>
            </div>
        </a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
