<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Recupera statistiche
$pdo = getConnection();
$userId = $_SESSION['user_id'];

// Conteggio todos dell'utente
$stmt = $pdo->prepare('SELECT COUNT(*) as total, SUM(completed) as completed FROM todos WHERE user_id = :user_id');
$stmt->execute(['user_id' => $userId]);
$todoStats = $stmt->fetch();

$totalTodos     = (int)($todoStats['total'] ?? 0);
$completedTodos = (int)($todoStats['completed'] ?? 0);
$pendingTodos   = $totalTodos - $completedTodos;

// Statistiche aggiuntive per admin
if ($isAdmin) {
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM users');
    $totalUsers = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query('SELECT COUNT(*) as total FROM todos');
    $totalAllTodos = (int)$stmt->fetch()['total'];
}

// Saluto basato sull'ora
$hour = (int)date('H');
if ($hour < 12) $greeting = 'Buongiorno';
elseif ($hour < 18) $greeting = 'Buon pomeriggio';
else $greeting = 'Buonasera';
?>

<!-- Welcome Section -->
<div class="welcome-section">
    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($username); ?>! 👋</h1>
    <p>Ecco un riepilogo della tua attività</p>
    <?php if ($isAdmin): ?>
    <span class="role-indicator admin">🛡️ Accesso Amministratore</span>
    <?php else: ?>
    <span class="role-indicator user">👤 Account Utente</span>
    <?php endif; ?>
</div>

<!-- Statistiche -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">📋</div>
        <div class="stat-value"><?php echo $totalTodos; ?></div>
        <div class="stat-label">Todos Totali</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">✅</div>
        <div class="stat-value"><?php echo $completedTodos; ?></div>
        <div class="stat-label">Completati</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">⏳</div>
        <div class="stat-value"><?php echo $pendingTodos; ?></div>
        <div class="stat-label">In Attesa</div>
    </div>
    <?php if ($isAdmin): ?>
    <div class="stat-card">
        <div class="stat-icon info">👥</div>
        <div class="stat-value"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Utenti Registrati</div>
    </div>
    <?php endif; ?>
</div>

<!-- Azioni Rapide -->
<div class="page-header">
    <h1>Azioni Rapide</h1>
    <p>Accedi velocemente alle funzionalità principali</p>
</div>

<div class="quick-actions">
    <a href="todos.php" class="action-card">
        <div class="action-icon primary">✅</div>
        <div class="action-text">
            <h3>Gestisci To-Do</h3>
            <p>Crea, modifica ed elimina le tue attività</p>
        </div>
    </a>
    <?php if ($isAdmin): ?>
    <a href="admin.php" class="action-card">
        <div class="action-icon admin-icon">⚙️</div>
        <div class="action-text">
            <h3>Pannello Admin</h3>
            <p>Gestisci utenti e ruoli del sistema</p>
        </div>
    </a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
