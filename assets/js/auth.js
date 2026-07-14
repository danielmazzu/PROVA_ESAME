/**
 * ============================================
 * Auth - Logica Login e Registrazione (Client-Side)
 * ============================================
 * Questo file gestisce l'interazione dell'utente con i form di login e registrazione.
 * Intercetta il submit dei form, invia i dati al backend tramite API e mostra
 * messaggi di feedback senza ricaricare la pagina (approccio CSR - Client-Side Rendering).
 * ============================================
 */

// Attende che il DOM sia completamente caricato prima di aggiungere gli event listener
document.addEventListener('DOMContentLoaded', () => {

    // ---- GESTIONE LOGIN ----
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        // Intercetta il submit del form di login
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Impedisce il comportamento default del form (ricaricamento pagina)

            // Disabilita il pulsante per evitare doppi click durante la richiesta
            const btn = loginForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Accesso in corso...';

            // Recupera i valori inseriti dall'utente nei campi del form
            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            try {
                // Invia le credenziali al backend tramite POST
                const data = await api.post('../api/auth/login.php', { email, password });

                // Mostra un messaggio di successo verde
                showAlert('auth-alerts', data.message, 'success');

                // Dopo 500ms reindirizza alla dashboard (da' il tempo di leggere il messaggio)
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 500);

            } catch (error) {
                // In caso di errore (credenziali errate, server non disponibile)
                // mostra un messaggio rosso e riabilita il pulsante
                showAlert('auth-alerts', error.message, 'danger');
                btn.disabled = false;
                btn.textContent = 'Accedi';
            }
        });
    }

    // ---- GESTIONE REGISTRAZIONE ----
    const registerForm = document.getElementById('form-register');
    if (registerForm) {
        // Intercetta il submit del form di registrazione
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = registerForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Registrazione in corso...';

            // Recupera tutti i campi del form di registrazione
            const nome     = document.getElementById('register-nome').value.trim();
            const cognome  = document.getElementById('register-cognome').value.trim();
            const email    = document.getElementById('register-email').value.trim();
            const password = document.getElementById('register-password').value;

            try {
                // Invia i dati di registrazione al backend
                const data = await api.post('../api/auth/register.php', { nome, cognome, email, password });

                // Mostra conferma e reindirizza al login dopo 1.5 secondi
                showAlert('auth-alerts', data.message + ' Redirect al login...', 'success');

                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);

            } catch (error) {
                // Mostra l'errore (es. "Email gia' registrata", "Password troppo corta")
                showAlert('auth-alerts', error.message, 'danger');
                btn.disabled = false;
                btn.textContent = 'Registrati';
            }
        });
    }
});
