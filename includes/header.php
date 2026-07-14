<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Template Esame - Applicazione Web PHP">
    <title>Template Esame<?php echo isset($pageTitle) ? ' - ' . $pageTitle : ''; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php if ($isLoggedIn): ?>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">
                <span class="brand-icon">TE</span>
                Template Esame
            </a>
            <div class="navbar-menu">
                <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="todos.php" class="nav-link <?php echo $currentPage === 'todos' ? 'active' : ''; ?>">
                    <span class="nav-icon">✅</span> To-Do
                </a>
                <?php if ($isAdmin): ?>
                <a href="admin.php" class="nav-link <?php echo $currentPage === 'admin' ? 'active' : ''; ?>">
                    <span class="nav-icon">⚙️</span> Admin
                </a>
                <?php endif; ?>
            </div>
            <div class="navbar-user">
                <div class="user-info">
                    <span class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <?php if ($isAdmin): ?>
                    <span class="user-badge">Admin</span>
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
