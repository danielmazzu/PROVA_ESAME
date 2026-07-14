<?php
$pageTitle = 'Registrazione';
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
            <h1>Crea Account</h1>
            <p>Registrati per iniziare ad usare la piattaforma</p>
        </div>

        <div id="auth-alerts"></div>

        <form id="form-register">
            <div class="form-group">
                <label class="form-label" for="register-nome"><i class="ph ph-user"></i> Nome</label>
                <input type="text" class="form-input" id="register-nome" 
                       placeholder="Inserisci il nome" required autocomplete="given-name">
            </div>
            <div class="form-group">
                <label class="form-label" for="register-cognome"><i class="ph ph-user"></i> Cognome</label>
                <input type="text" class="form-input" id="register-cognome" 
                       placeholder="Inserisci il cognome" required autocomplete="family-name">
            </div>
            <div class="form-group">
                <label class="form-label" for="register-email"><i class="ph ph-envelope-simple"></i> Email</label>
                <input type="email" class="form-input" id="register-email" 
                       placeholder="Inserisci la tua email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="register-password"><i class="ph ph-lock-key"></i> Password</label>
                <input type="password" class="form-input" id="register-password" 
                       placeholder="Scegli una password" required minlength="6" autocomplete="new-password">
                <small class="text-muted" style="display:block; margin-top:6px; font-size:12px;">Min. 6 caratteri</small>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Registrati</button>
        </form>

        <div class="auth-footer">
            Hai già un account? <a href="login.php">Accedi</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
