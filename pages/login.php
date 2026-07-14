<?php
$pageTitle = 'Login';
$pageScripts = ['../assets/js/auth.js'];
require_once __DIR__ . '/../includes/header.php';

// Se già loggato, redirect a dashboard
if ($isLoggedIn) {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="auth-card card">
    <div class="card-body">
        <div class="auth-logo">
            <span class="brand-icon">TE</span>
            <h1>Bentornato</h1>
            <p>Inserisci le tue credenziali per accedere</p>
        </div>

        <div id="auth-alerts"></div>

        <form id="form-login">
            <div class="form-group">
                <label class="form-label" for="login-email"><i class="ph ph-envelope-simple"></i> Email</label>
                <input type="email" class="form-input" id="login-email" 
                       placeholder="Inserisci la tua email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="login-password"><i class="ph ph-lock-key"></i> Password</label>
                <input type="password" class="form-input" id="login-password" 
                       placeholder="Inserisci la password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Accedi</button>
        </form>

        <div class="auth-footer">
            Non hai un account? <a href="register.php">Registrati ora</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
