/**
 * ============================================
 * Auth - Logica Login e Registrazione
 * ============================================
 */

document.addEventListener('DOMContentLoaded', () => {

    // ---- LOGIN ----
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = loginForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Accesso in corso...';

            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            try {
                const data = await api.post('../api/auth/login.php', { email, password });

                showAlert('auth-alerts', data.message, 'success');

                // Redirect basato sul ruolo
                setTimeout(() => {
                    if (data.user && data.user.role === 'admin') {
                        window.location.href = 'dashboard.php';
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                }, 500);

            } catch (error) {
                showAlert('auth-alerts', error.message, 'danger');
                btn.disabled = false;
                btn.textContent = 'Accedi';
            }
        });
    }

    // ---- REGISTRAZIONE ----
    const registerForm = document.getElementById('form-register');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = registerForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Registrazione in corso...';

            const nome     = document.getElementById('register-nome').value.trim();
            const cognome  = document.getElementById('register-cognome').value.trim();
            const email    = document.getElementById('register-email').value.trim();
            const password = document.getElementById('register-password').value;

            try {
                const data = await api.post('../api/auth/register.php', { nome, cognome, email, password });

                showAlert('auth-alerts', data.message + ' Redirect al login...', 'success');

                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);

            } catch (error) {
                showAlert('auth-alerts', error.message, 'danger');
                btn.disabled = false;
                btn.textContent = 'Registrati';
            }
        });
    }
});
