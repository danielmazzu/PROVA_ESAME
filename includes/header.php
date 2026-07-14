<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isLoggedIn = isset($_SESSION['user_id']);
$isReferente = isset($_SESSION['role']) && $_SESSION['role'] === 'referente';
$isDipendente = isset($_SESSION['role']) && $_SESSION['role'] === 'dipendente';
$nome = $_SESSION['nome'] ?? '';
$cognome = $_SESSION['cognome'] ?? '';
$fullName = trim($nome . ' ' . $cognome);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academy Aziendale - Percorsi Formativi">
    <title>Academy Aziendale<?php echo isset($pageTitle) ? ' - ' . $pageTitle : ''; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php if ($isLoggedIn): ?>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">
                <span class="brand-icon"><i class="ph ph-graduation-cap"></i></span>
                Academy Aziendale
            </a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="ph ph-chart-pie-slice"></i></span> Dashboard
                </a>
                
                <?php if ($isDipendente): ?>
                <a href="miei_corsi.php" class="nav-link <?php echo $currentPage === 'miei_corsi' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="ph ph-books"></i></span> I Miei Corsi
                </a>
                <?php endif; ?>

                <?php if ($isReferente): ?>
                <a href="admin_corsi.php" class="nav-link <?php echo $currentPage === 'admin_corsi' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="ph ph-book-open"></i></span> Catalogo Corsi
                </a>
                <a href="admin_assegnazioni.php" class="nav-link <?php echo $currentPage === 'admin_assegnazioni' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="ph ph-users"></i></span> Assegnazioni
                </a>
                <a href="admin_statistiche.php" class="nav-link <?php echo $currentPage === 'admin_statistiche' ? 'active' : ''; ?>">
                    <span class="nav-icon"><i class="ph ph-trend-up"></i></span> Statistiche
                </a>
                <?php endif; ?>
            </div>
            <div class="navbar-user">
                <div class="user-info">
                    <span class="user-avatar"><?php echo strtoupper(substr($nome, 0, 1) . substr($cognome, 0, 1)); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
                    <?php if ($isReferente): ?>
                    <span class="user-badge">Referente</span>
                    <?php endif; ?>
                </div>
                <button id="btn-logout" class="btn btn-ghost btn-sm">Logout</button>
            </div>
            <button class="navbar-toggle" id="navbar-toggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
    <?php endif; ?>
    <main class="main-content <?php echo !$isLoggedIn ? 'main-auth' : ''; ?>">
