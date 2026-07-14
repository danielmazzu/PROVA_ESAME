<?php
$pageTitle = 'Pannello Admin';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();

// Gestisci azioni POST (cambio ruolo, eliminazione)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $targetId   = intval($_POST['user_id'] ?? 0);

    // Non modificare se stessi
    if ($targetId > 0 && $targetId !== $_SESSION['user_id']) {
        if ($action === 'toggle_role') {
            $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id');
            $stmt->execute(['id' => $targetId]);
            $user = $stmt->fetch();
            $newRole = ($user['role'] === 'admin') ? 'user' : 'admin';
            $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $stmt->execute(['role' => $newRole, 'id' => $targetId]);
        } elseif ($action === 'delete_user') {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $targetId]);
        }
    }

    // Redirect per evitare resubmit
    header('Location: admin.php');
    exit;
}

// Carica tutti gli utenti
$stmt = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>🛡️ Pannello Amministrazione</h1>
    <p>Gestisci gli utenti registrati nel sistema</p>
</div>

<!-- Statistiche rapide -->
<div class="stats-grid mb-6">
    <div class="stat-card">
        <div class="stat-icon info">👥</div>
        <div class="stat-value"><?php echo count($users); ?></div>
        <div class="stat-label">Utenti Totali</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">🛡</div>
        <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></div>
        <div class="stat-label">Amministratori</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">👤</div>
        <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'user')); ?></div>
        <div class="stat-label">Utenti Standard</div>
    </div>
</div>

<!-- Tabella Utenti -->
<div class="card">
    <div class="card-header">
        <h2>Lista Utenti</h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Ruolo</th>
                    <th>Registrato il</th>
                    <th class="text-right">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        <?php if ($user['id'] === $_SESSION['user_id']): ?>
                            <span class="text-muted">(tu)</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="role-badge <?php echo $user['role']; ?>">
                            <?php echo $user['role'] === 'admin' ? '🛡 Admin' : '👤 User'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                    <td class="text-right">
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="toggle_role">
                            <button type="submit" class="btn btn-ghost btn-sm" 
                                    title="Cambia ruolo">
                                <?php echo $user['role'] === 'admin' ? '⬇ Declassa' : '⬆ Promuovi'; ?>
                            </button>
                        </form>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Eliminare l\'utente <?php echo htmlspecialchars($user['username']); ?>?')">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="delete_user">
                            <button type="submit" class="btn btn-danger btn-sm">✕ Elimina</button>
                        </form>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
